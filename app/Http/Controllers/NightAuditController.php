<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NightAuditController extends Controller
{
    private const REQUIRED_TABLES = [
        'night_audit_batches',
        'night_audit_room_snapshots',
        'night_audit_revenue_lines',
        'night_audit_cashier_summaries',
        'night_audit_housekeeping_exceptions',
        'night_audit_checklists',
        'night_audit_adjustments',
        'night_audit_approvals',
    ];

    public function index(Request $request)
    {
        $auditDate = $this->normalizeDate((string) $request->query('audit_date', now()->format('Y-m-d')));
        $schemaReady = $this->schemaReady();
        $batches = collect();
        $selectedBatch = null;

        if ($schemaReady) {
            $batches = DB::table('night_audit_batches')
                ->orderByDesc('business_date')
                ->orderByDesc('id')
                ->limit(45)
                ->get();

            $selectedBatch = $this->resolveSelectedBatch($request, $auditDate);
        }

        $payload = $selectedBatch
            ? $this->loadBatchPayload($selectedBatch)
            : $this->buildPreviewPayload($auditDate);

        $viewData = array_merge($payload, [
            'schemaReady' => $schemaReady,
            'auditDate' => $auditDate,
            'batches' => $batches,
            'selectedBatch' => $selectedBatch,
            'statusOptions' => ['Pending', 'Ready', 'Done', 'Blocked', 'Waived'],
        ]);

        return $this->respond($request, 'night-audit.index', $viewData, [
            'schema_ready' => $schemaReady,
            'audit_date' => $auditDate,
            'selected_batch' => $selectedBatch,
            'payload' => $payload,
        ]);
    }

    public function start(Request $request)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia. Jalankan migration night audit terlebih dahulu.', 422, [], '/night-audit', false);
        }

        $validated = $request->validate([
            'audit_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $auditDate = $this->normalizeDate($validated['audit_date']);
        $username = $this->currentUserName();
        $notes = trim((string) ($validated['notes'] ?? ''));

        $batch = DB::transaction(function () use ($auditDate, $username, $notes) {
            $existing = DB::table('night_audit_batches')
                ->whereDate('business_date', $auditDate)
                ->first();

            if ($existing && in_array($existing->status, ['Closed', 'Approved'], true)) {
                return $existing;
            }

            if (!$existing) {
                $batchId = DB::table('night_audit_batches')->insertGetId([
                    'audit_no' => $this->generateAuditNo($auditDate),
                    'business_date' => $auditDate,
                    'status' => 'Draft',
                    'hotel_day_start_time' => $this->hotelDayStartTime(),
                    'started_at' => now(),
                    'started_by' => $username,
                    'notes' => $notes !== '' ? $notes : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $batchId = (int) $existing->id;
                DB::table('night_audit_batches')
                    ->where('id', $batchId)
                    ->update([
                        'status' => 'Draft',
                        'started_at' => $existing->started_at ?: now(),
                        'started_by' => $existing->started_by ?: $username,
                        'notes' => $notes !== '' ? $notes : $existing->notes,
                        'updated_at' => now(),
                    ]);
            }

            $this->regenerateBatch($batchId, $auditDate);
            $this->seedApprovalRows($batchId);

            return DB::table('night_audit_batches')->where('id', $batchId)->first();
        });

        if ($batch && in_array($batch->status, ['Closed', 'Approved'], true)) {
            return $this->respondError($request, 'Audit untuk tanggal ini sudah ' . $batch->status . ' dan tidak bisa dibuka ulang dari tombol Start.', 422, [], '/night-audit?batch_id=' . $batch->id, false);
        }

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $batch->id, 'Night audit batch berhasil disiapkan.', $batch, 201);
    }

    public function refresh(Request $request, int $batchId)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia.', 422, [], '/night-audit', false);
        }

        $batch = $this->findBatch($batchId);
        if (!$batch) {
            return $this->respondError($request, 'Night audit batch tidak ditemukan.', 404, [], '/night-audit', false);
        }

        if ($batch->status !== 'Draft') {
            return $this->respondError($request, 'Hanya audit berstatus Draft yang bisa di-refresh.', 422, [], '/night-audit?batch_id=' . $batchId, false);
        }

        DB::transaction(function () use ($batch) {
            $this->regenerateBatch((int) $batch->id, $this->normalizeDate((string) $batch->business_date));
        });

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $batchId, 'Snapshot night audit berhasil diperbarui.', $this->findBatch($batchId));
    }

    public function close(Request $request, int $batchId)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia.', 422, [], '/night-audit', false);
        }

        $batch = $this->findBatch($batchId);
        if (!$batch) {
            return $this->respondError($request, 'Night audit batch tidak ditemukan.', 404, [], '/night-audit', false);
        }

        if ($batch->status !== 'Draft') {
            return $this->respondError($request, 'Hanya audit berstatus Draft yang bisa ditutup.', 422, [], '/night-audit?batch_id=' . $batchId, false);
        }

        DB::transaction(function () use ($batch) {
            $this->regenerateBatch((int) $batch->id, $this->normalizeDate((string) $batch->business_date));

            DB::table('night_audit_batches')
                ->where('id', $batch->id)
                ->update([
                    'status' => 'Closed',
                    'closed_at' => now(),
                    'closed_by' => $this->currentUserName(),
                    'updated_at' => now(),
                ]);

            DB::table('night_audit_approvals')
                ->where('batch_id', $batch->id)
                ->where('approval_level', 1)
                ->update([
                    'status' => 'Approved',
                    'approver_name' => $this->currentUserName(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $batchId, 'Night audit berhasil ditutup dan siap approval.', $this->findBatch($batchId));
    }

    public function approve(Request $request, int $batchId)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia.', 422, [], '/night-audit', false);
        }

        $batch = $this->findBatch($batchId);
        if (!$batch) {
            return $this->respondError($request, 'Night audit batch tidak ditemukan.', 404, [], '/night-audit', false);
        }

        if ($batch->status !== 'Closed') {
            return $this->respondError($request, 'Audit harus berstatus Closed sebelum approval final.', 422, [], '/night-audit?batch_id=' . $batchId, false);
        }

        DB::transaction(function () use ($batch) {
            DB::table('night_audit_batches')
                ->where('id', $batch->id)
                ->update([
                    'status' => 'Approved',
                    'approved_at' => now(),
                    'approved_by' => $this->currentUserName(),
                    'updated_at' => now(),
                ]);

            DB::table('night_audit_approvals')
                ->where('batch_id', $batch->id)
                ->where('approval_level', 2)
                ->update([
                    'status' => 'Approved',
                    'approver_name' => $this->currentUserName(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $batchId, 'Night audit sudah mendapat approval final.', $this->findBatch($batchId));
    }

    public function updateChecklist(Request $request, int $checklistId)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia.', 422, [], '/night-audit', false);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Pending,Ready,Done,Blocked,Waived'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $checklist = DB::table('night_audit_checklists')->where('id', $checklistId)->first();
        if (!$checklist) {
            return $this->respondError($request, 'Checklist night audit tidak ditemukan.', 404, [], '/night-audit', false);
        }

        $batch = $this->findBatch((int) $checklist->batch_id);
        if (!$batch || $batch->status === 'Approved') {
            return $this->respondError($request, 'Checklist pada audit Approved tidak bisa diubah.', 422, [], '/night-audit?batch_id=' . ($batch->id ?? ''), false);
        }

        $status = $validated['status'];
        $completedStatuses = ['Done', 'Waived'];

        DB::table('night_audit_checklists')
            ->where('id', $checklistId)
            ->update([
                'status' => $status,
                'completed_by' => in_array($status, $completedStatuses, true) ? $this->currentUserName() : null,
                'completed_at' => in_array($status, $completedStatuses, true) ? now() : null,
                'remarks' => trim((string) ($validated['remarks'] ?? '')) ?: null,
                'updated_at' => now(),
            ]);

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $checklist->batch_id . '#checklist', 'Checklist berhasil diperbarui.', [
            'id' => $checklistId,
            'status' => $status,
        ]);
    }

    public function storeAdjustment(Request $request, int $batchId)
    {
        if (!$this->schemaReady()) {
            return $this->respondError($request, 'Tabel night audit belum tersedia.', 422, [], '/night-audit', false);
        }

        $batch = $this->findBatch($batchId);
        if (!$batch) {
            return $this->respondError($request, 'Night audit batch tidak ditemukan.', 404, [], '/night-audit', false);
        }

        if ($batch->status !== 'Draft') {
            return $this->respondError($request, 'Adjustment hanya bisa dicatat saat audit masih Draft.', 422, [], '/night-audit?batch_id=' . $batchId, false);
        }

        $validated = $request->validate([
            'regno' => ['nullable', 'string', 'max:30'],
            'regno2' => ['nullable', 'string', 'max:60'],
            'room_code' => ['nullable', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:80'],
            'reason_code' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $adjustment = [
            'batch_id' => $batchId,
            'adjustment_no' => $this->generateAdjustmentNo($batch),
            'regno' => strtoupper(trim((string) ($validated['regno'] ?? ''))) ?: null,
            'regno2' => strtoupper(trim((string) ($validated['regno2'] ?? ''))) ?: null,
            'room_code' => strtoupper(trim((string) ($validated['room_code'] ?? ''))) ?: null,
            'department' => strtoupper(trim((string) $validated['department'])),
            'reason_code' => strtoupper(trim((string) $validated['reason_code'])),
            'description' => trim((string) $validated['description']),
            'amount' => round((float) $validated['amount'], 2),
            'approval_status' => 'Draft',
            'requested_by' => $this->currentUserName(),
            'remarks' => trim((string) ($validated['remarks'] ?? '')) ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('night_audit_adjustments')->insert($adjustment);

        return $this->respondAfterMutation($request, '/night-audit?batch_id=' . $batchId . '#adjustments', 'Adjustment night audit berhasil dicatat.', $adjustment, 201);
    }

    private function resolveSelectedBatch(Request $request, string $auditDate): ?object
    {
        $batchId = (int) $request->query('batch_id', 0);
        if ($batchId > 0) {
            return DB::table('night_audit_batches')->where('id', $batchId)->first();
        }

        return DB::table('night_audit_batches')
            ->whereDate('business_date', $auditDate)
            ->orderByDesc('id')
            ->first();
    }

    private function findBatch(int $batchId): ?object
    {
        return DB::table('night_audit_batches')->where('id', $batchId)->first();
    }

    private function loadBatchPayload(object $batch): array
    {
        $batchId = (int) $batch->id;

        return [
            'summary' => $this->summaryFromBatch($batch),
            'roomSnapshots' => DB::table('night_audit_room_snapshots')->where('batch_id', $batchId)->orderBy('room_code')->limit(350)->get(),
            'revenueLines' => DB::table('night_audit_revenue_lines')->where('batch_id', $batchId)->orderBy('department')->orderBy('room_code')->limit(350)->get(),
            'cashierSummaries' => DB::table('night_audit_cashier_summaries')->where('batch_id', $batchId)->orderBy('payment_type')->get(),
            'housekeepingExceptions' => DB::table('night_audit_housekeeping_exceptions')->where('batch_id', $batchId)->orderByRaw("CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 ELSE 4 END")->orderBy('room_code')->get(),
            'checklists' => DB::table('night_audit_checklists')->where('batch_id', $batchId)->orderBy('sequence_no')->get(),
            'adjustments' => DB::table('night_audit_adjustments')->where('batch_id', $batchId)->orderByDesc('id')->limit(80)->get(),
            'approvals' => DB::table('night_audit_approvals')->where('batch_id', $batchId)->orderBy('approval_level')->get(),
            'isPreview' => false,
        ];
    }

    private function buildPreviewPayload(string $auditDate): array
    {
        $rooms = $this->loadRoomInventory();
        $activeRows = $this->loadActiveRows();
        $roomSnapshots = $this->buildRoomSnapshots($activeRows, $auditDate);
        $revenueLines = $this->buildRevenueLines($activeRows, $auditDate);
        $cashierSummaries = $this->buildCashierSummaries($auditDate);
        $housekeepingExceptions = $this->buildHousekeepingExceptions($rooms, $activeRows);
        $summary = $this->buildSummary($auditDate, $rooms, $activeRows, $revenueLines, $cashierSummaries, $housekeepingExceptions);
        $checklists = collect($this->defaultChecklistRows($housekeepingExceptions))->map(fn (array $row) => (object) $row);

        return [
            'summary' => $summary,
            'roomSnapshots' => $roomSnapshots,
            'revenueLines' => $revenueLines,
            'cashierSummaries' => $cashierSummaries,
            'housekeepingExceptions' => $housekeepingExceptions,
            'checklists' => $checklists,
            'adjustments' => collect(),
            'approvals' => collect($this->defaultApprovalRows(0))->map(fn (array $row) => (object) $row),
            'isPreview' => true,
        ];
    }

    private function regenerateBatch(int $batchId, string $auditDate): void
    {
        $payload = $this->buildPreviewPayload($auditDate);
        $existingChecklist = DB::table('night_audit_checklists')
            ->where('batch_id', $batchId)
            ->get()
            ->keyBy('task_code');

        foreach ([
            'night_audit_room_snapshots',
            'night_audit_revenue_lines',
            'night_audit_cashier_summaries',
            'night_audit_housekeeping_exceptions',
            'night_audit_checklists',
        ] as $table) {
            DB::table($table)->where('batch_id', $batchId)->delete();
        }

        $this->insertRows('night_audit_room_snapshots', $payload['roomSnapshots'], $batchId);
        $this->insertRows('night_audit_revenue_lines', $payload['revenueLines'], $batchId);
        $this->insertRows('night_audit_cashier_summaries', $payload['cashierSummaries'], $batchId);
        $this->insertRows('night_audit_housekeeping_exceptions', $payload['housekeepingExceptions'], $batchId);

        $checklists = $payload['checklists']->map(function ($row) use ($existingChecklist) {
            $row = (array) $row;
            $existing = $existingChecklist->get($row['task_code']);
            if ($existing) {
                $row['status'] = $existing->status;
                $row['completed_by'] = $existing->completed_by;
                $row['completed_at'] = $existing->completed_at;
                $row['evidence_reference'] = $existing->evidence_reference;
                $row['remarks'] = $existing->remarks;
            }

            return (object) $row;
        });
        $this->insertRows('night_audit_checklists', $checklists, $batchId);

        DB::table('night_audit_batches')
            ->where('id', $batchId)
            ->update(array_merge($this->batchSummaryUpdatePayload($payload['summary']), [
                'hotel_day_start_time' => $this->hotelDayStartTime(),
                'updated_at' => now(),
            ]));
    }

    private function insertRows(string $table, Collection $rows, int $batchId): void
    {
        $now = now();
        $rows->chunk(80)->each(function (Collection $chunk) use ($table, $batchId, $now) {
            $payload = $chunk->map(function ($row) use ($batchId, $now) {
                $data = (array) $row;
                unset($data['id']);
                $data['batch_id'] = $batchId;
                $data['created_at'] = $now;
                $data['updated_at'] = $now;

                return $data;
            })->values()->all();

            if (!empty($payload)) {
                DB::table($table)->insert($payload);
            }
        });
    }

    private function loadRoomInventory(): Collection
    {
        if (!Schema::hasTable('ROOM')) {
            return collect();
        }

        return DB::table('ROOM')
            ->selectRaw("RTRIM(Kode) as room_code, RTRIM(Nama) as room_class, RTRIM(Status) as room_status, RTRIM(Status2) as housekeeping_status, Rate1, Meeting, Urut")
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->where(function ($query) {
                $query->whereNull('Meeting')->orWhere('Meeting', 0);
            })
            ->orderBy('Urut')
            ->orderBy('Kode')
            ->get()
            ->map(function ($room) {
                $room->room_code = trim((string) $room->room_code);
                $room->room_class = trim((string) $room->room_class);
                $room->room_status = trim((string) $room->room_status);
                $room->housekeeping_status = trim((string) $room->housekeeping_status);

                return $room;
            });
    }

    private function loadActiveRows(): Collection
    {
        if (!Schema::hasTable('DATA2')) {
            return collect();
        }

        return DB::table('DATA2')
            ->leftJoin('ROOM', 'DATA2.Kode', '=', 'ROOM.Kode')
            ->leftJoin('DATA', 'DATA2.RegNo', '=', 'DATA.RegNo')
            ->selectRaw("
                RTRIM(DATA2.RegNo) as regno,
                RTRIM(DATA2.RegNo2) as regno2,
                RTRIM(DATA2.Kode) as room_code,
                RTRIM(DATA2.Guest) as guest_name,
                RTRIM(DATA2.Tipe) as market_segment,
                RTRIM(DATA2.Payment) as payment_method,
                RTRIM(DATA2.Package) as package_code,
                RTRIM(DATA2.Usaha) as company_name,
                RTRIM(DATA2.Status) as detail_status,
                RTRIM(DATA2.Pst) as post_status,
                DATA2.TglIn as checkin_date,
                DATA2.TglKeluar as expected_checkout_date,
                DATA2.JamIn as checkin_time,
                DATA2.Nominal as room_rate,
                DATA2.Rate2 as basic_rate,
                DATA2.Disc as discount_percent,
                DATA2.Person as pax,
                RTRIM(ROOM.Nama) as room_class,
                RTRIM(ROOM.Status) as room_status,
                RTRIM(ROOM.Status2) as housekeeping_status,
                DATA.Deposit as header_deposit
            ")
            ->where('DATA2.Pst', '=', ' ')
            ->whereRaw("RTRIM(DATA2.Kode) <> '999'")
            ->orderBy('DATA2.Kode')
            ->get()
            ->map(function ($row) {
                foreach (['regno', 'regno2', 'room_code', 'guest_name', 'market_segment', 'payment_method', 'package_code', 'company_name', 'detail_status', 'room_class', 'room_status', 'housekeeping_status'] as $field) {
                    $row->{$field} = trim((string) ($row->{$field} ?? ''));
                }

                return $row;
            });
    }

    private function buildRoomSnapshots(Collection $activeRows, string $auditDate): Collection
    {
        return $activeRows->map(function ($row) use ($auditDate) {
            $roomRate = (float) ($row->room_rate ?? 0);
            $discountPercent = (float) ($row->discount_percent ?? 0);
            $netRate = max($roomRate - ($roomRate * $discountPercent / 100), 0);
            $payment = strtoupper(trim((string) ($row->payment_method ?? '')));
            $roomStatus = strtoupper(trim((string) ($row->room_status ?? '')));
            $checkinAt = $this->combineLegacyDateTime($row->checkin_date ?? null, $row->checkin_time ?? null);
            $stayNights = $checkinAt ? max($checkinAt->copy()->startOfDay()->diffInDays(Carbon::parse($auditDate)->startOfDay()), 0) + 1 : 1;
            $isComplimentary = str_contains($payment, 'COMPLIMENT');
            $isHouseUse = str_contains($payment, 'HOUSE') || str_contains($roomStatus, 'OWNER UNIT');
            $expectedCheckout = $this->safeDate($row->expected_checkout_date ?? null);
            $riskFlag = null;

            if ($expectedCheckout && Carbon::parse($expectedCheckout)->lt(Carbon::parse($auditDate))) {
                $riskFlag = 'OVERSTAY';
            } elseif ($isComplimentary || $isHouseUse) {
                $riskFlag = 'APPROVAL';
            }

            return (object) [
                'regno' => $row->regno,
                'regno2' => $row->regno2,
                'room_code' => $row->room_code,
                'room_class' => $row->room_class,
                'guest_name' => $row->guest_name,
                'company_name' => $row->company_name,
                'market_segment' => $row->market_segment,
                'payment_method' => $row->payment_method,
                'package_code' => $row->package_code,
                'pms_status' => 'Occupied',
                'housekeeping_status' => $row->housekeeping_status ?: $row->room_status,
                'checkin_at' => $checkinAt?->format('Y-m-d H:i:s'),
                'expected_checkout_date' => $expectedCheckout,
                'pax' => (int) ($row->pax ?? 0),
                'stay_nights' => $stayNights,
                'room_rate' => $roomRate,
                'discount_percent' => $discountPercent,
                'net_room_rate' => ($isComplimentary || $isHouseUse) ? 0 : $netRate,
                'estimated_folio_balance' => ($isComplimentary || $isHouseUse) ? 0 : $netRate,
                'is_day_use' => $expectedCheckout && $checkinAt ? Carbon::parse($expectedCheckout)->isSameDay($checkinAt) : false,
                'is_complimentary' => $isComplimentary,
                'is_house_use' => $isHouseUse,
                'risk_flag' => $riskFlag,
                'audit_note' => $riskFlag === 'OVERSTAY' ? 'Expected checkout sudah lewat dari business date audit.' : null,
            ];
        })->values();
    }

    private function buildRevenueLines(Collection $activeRows, string $auditDate): Collection
    {
        return $activeRows->map(function ($row) use ($auditDate) {
            $roomRate = (float) ($row->room_rate ?? 0);
            $discountPercent = (float) ($row->discount_percent ?? 0);
            $netAmount = max($roomRate - ($roomRate * $discountPercent / 100), 0);
            $payment = strtoupper(trim((string) ($row->payment_method ?? '')));
            $roomStatus = strtoupper(trim((string) ($row->room_status ?? '')));
            $riskFlag = null;

            if (str_contains($payment, 'COMPLIMENT') || str_contains($payment, 'HOUSE') || str_contains($roomStatus, 'OWNER UNIT')) {
                $riskFlag = 'ZERO_RATE_APPROVAL';
                $netAmount = 0;
            }

            return (object) [
                'transaction_date' => $auditDate,
                'source_table' => 'DATA2',
                'source_key' => $row->regno2,
                'department' => 'ROOM',
                'revenue_code' => 'ROOM_NIGHT',
                'room_code' => $row->room_code,
                'regno' => $row->regno,
                'regno2' => $row->regno2,
                'guest_name' => $row->guest_name,
                'description' => 'Night Audit Room Charge',
                'debit' => $netAmount,
                'credit' => 0,
                'net_amount' => $netAmount,
                'tax_amount' => 0,
                'service_amount' => 0,
                'currency' => 'IDR',
                'status' => 'Preview',
                'risk_flag' => $riskFlag,
                'audit_note' => $riskFlag ? 'Room complimentary/house use harus ada approval.' : null,
            ];
        })->values();
    }

    private function buildCashierSummaries(string $auditDate): Collection
    {
        $summary = collect();

        foreach ($this->cashierSourceRows($auditDate) as $row) {
            $paymentType = strtoupper(trim((string) ($row->payment_type ?? '')));
            $paymentType = $paymentType !== '' ? $paymentType : 'CASH';
            $current = $summary->get($paymentType, [
                'payment_type' => $paymentType,
                'gross_receipt' => 0.0,
                'refund_amount' => 0.0,
                'transaction_count' => 0,
            ]);

            $current['gross_receipt'] += (float) ($row->gross_receipt ?? 0);
            $current['refund_amount'] += (float) ($row->refund_amount ?? 0);
            $current['transaction_count'] += (int) ($row->transaction_count ?? 0);
            $summary->put($paymentType, $current);
        }

        return $summary->values()->map(function (array $row) {
            $expectedCash = $row['payment_type'] === 'CASH'
                ? max($row['gross_receipt'] - $row['refund_amount'], 0)
                : 0;

            return (object) [
                'cashier_code' => 'ALL',
                'shift_code' => 'NIGHT',
                'payment_type' => $row['payment_type'],
                'gross_receipt' => round($row['gross_receipt'], 2),
                'refund_amount' => round($row['refund_amount'], 2),
                'void_amount' => 0,
                'cash_drop' => $expectedCash,
                'expected_cash' => $expectedCash,
                'variance_amount' => 0,
                'transaction_count' => $row['transaction_count'],
                'settlement_status' => 'Open',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'remarks' => null,
            ];
        });
    }

    private function cashierSourceRows(string $auditDate): Collection
    {
        $rows = collect();

        if (Schema::hasTable('KAS')) {
            $rows = $rows->merge(DB::table('KAS')
                ->selectRaw("
                    RTRIM(TipeBayar) as payment_type,
                    COUNT(*) as transaction_count,
                    SUM(CASE WHEN Nominal >= 0 THEN Nominal ELSE 0 END) as gross_receipt,
                    SUM(CASE WHEN Nominal < 0 THEN ABS(Nominal) ELSE 0 END) as refund_amount
                ")
                ->whereDate('Tgl', $auditDate)
                ->groupBy(DB::raw('RTRIM(TipeBayar)'))
                ->get());
        }

        if (Schema::hasTable('KASMUKA')) {
            $rows = $rows->merge(DB::table('KASMUKA')
                ->selectRaw("
                    RTRIM(TipeBayar) as payment_type,
                    COUNT(*) as transaction_count,
                    SUM(CASE WHEN Nominal >= 0 THEN Nominal ELSE 0 END) as gross_receipt,
                    SUM(CASE WHEN Nominal < 0 THEN ABS(Nominal) ELSE 0 END) as refund_amount
                ")
                ->whereDate('TglC', $auditDate)
                ->groupBy(DB::raw('RTRIM(TipeBayar)'))
                ->get());
        }

        return $rows;
    }

    private function buildHousekeepingExceptions(Collection $rooms, Collection $activeRows): Collection
    {
        $activeByRoom = $activeRows->keyBy(fn ($row) => strtoupper(trim((string) $row->room_code)));
        $exceptions = collect();

        foreach ($rooms as $room) {
            $roomCode = strtoupper(trim((string) $room->room_code));
            $active = $activeByRoom->get($roomCode);
            $roomStatus = strtoupper(trim((string) ($room->room_status ?? '')));
            $hkStatus = strtoupper(trim((string) ($room->housekeeping_status ?? '')));

            if ($active && preg_match('/VACANT|CHECK OUT/i', $roomStatus)) {
                $exceptions->push((object) [
                    'room_code' => $roomCode,
                    'pms_status' => 'Occupied',
                    'housekeeping_status' => trim((string) ($room->housekeeping_status ?: $room->room_status)),
                    'reservation_status' => 'In House',
                    'exception_type' => 'PMS_ROOM_STATUS_MISMATCH',
                    'severity' => 'Critical',
                    'action_status' => 'Open',
                    'owner_department' => 'Front Office',
                    'notes' => 'Room aktif di DATA2 tetapi status ROOM masih vacant/check out.',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ]);
                continue;
            }

            if (!$active && (str_contains($roomStatus, 'OCCUPIED') || str_contains($hkStatus, 'OCCUPIED'))) {
                $exceptions->push((object) [
                    'room_code' => $roomCode,
                    'pms_status' => 'Vacant',
                    'housekeeping_status' => trim((string) ($room->housekeeping_status ?: $room->room_status)),
                    'reservation_status' => 'No Active Guest',
                    'exception_type' => 'PHANTOM_OCCUPIED',
                    'severity' => 'High',
                    'action_status' => 'Open',
                    'owner_department' => 'Housekeeping',
                    'notes' => 'ROOM terlihat occupied tetapi tidak ada DATA2 aktif.',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ]);
                continue;
            }

            if (!$active && str_contains($roomStatus, 'VACANT DIRTY')) {
                $exceptions->push((object) [
                    'room_code' => $roomCode,
                    'pms_status' => 'Vacant',
                    'housekeeping_status' => 'Vacant Dirty',
                    'reservation_status' => 'Vacant',
                    'exception_type' => 'VACANT_DIRTY_PENDING',
                    'severity' => 'Medium',
                    'action_status' => 'Open',
                    'owner_department' => 'Housekeeping',
                    'notes' => 'Kamar vacant dirty perlu follow up sebelum roll business date.',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ]);
            }
        }

        return $exceptions->values();
    }

    private function buildSummary(
        string $auditDate,
        Collection $rooms,
        Collection $activeRows,
        Collection $revenueLines,
        Collection $cashierSummaries,
        Collection $housekeepingExceptions
    ): array {
        $totalRooms = $rooms->count();
        $occupiedRooms = $activeRows->pluck('room_code')->map(fn ($value) => trim((string) $value))->filter()->unique()->count();
        $outOfOrderRooms = $rooms->filter(fn ($room) => preg_match('/OUT OF ORDER|RENOVATED/i', (string) $room->room_status))->count();
        $houseUseRooms = $activeRows->filter(function ($row) {
            $payment = strtoupper(trim((string) $row->payment_method));
            $roomStatus = strtoupper(trim((string) $row->room_status));

            return str_contains($payment, 'HOUSE') || str_contains($roomStatus, 'OWNER UNIT');
        })->count();
        $complimentaryRooms = $activeRows->filter(fn ($row) => str_contains(strtoupper(trim((string) $row->payment_method)), 'COMPLIMENT'))->count();
        $sellableBase = max($totalRooms - $outOfOrderRooms - $houseUseRooms, 1);
        $cashReceipt = $cashierSummaries->where('payment_type', 'CASH')->sum('gross_receipt');
        $nonCashReceipt = $cashierSummaries->reject(fn ($row) => $row->payment_type === 'CASH')->sum('gross_receipt');

        return [
            'business_date' => $auditDate,
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'vacant_rooms' => max($totalRooms - $occupiedRooms - $outOfOrderRooms, 0),
            'out_of_order_rooms' => $outOfOrderRooms,
            'house_use_rooms' => $houseUseRooms,
            'complimentary_rooms' => $complimentaryRooms,
            'arrival_count' => $this->countRowsByDate('DATA2', 'TglIn', $auditDate),
            'departure_count' => $this->countRowsByDate('DATA2', 'TglKeluar', $auditDate),
            'in_house_count' => $activeRows->count(),
            'walk_in_count' => $activeRows->filter(fn ($row) => str_contains(strtoupper(trim((string) $row->market_segment)), 'WALK'))->count(),
            'occupancy_percent' => round(($occupiedRooms / $sellableBase) * 100, 2),
            'room_revenue' => round($revenueLines->where('department', 'ROOM')->sum('net_amount'), 2),
            'package_revenue' => round($revenueLines->where('department', 'PACKAGE')->sum('net_amount'), 2),
            'other_revenue' => round($revenueLines->reject(fn ($row) => in_array($row->department, ['ROOM', 'PACKAGE'], true))->sum('net_amount'), 2),
            'gross_revenue' => round($revenueLines->sum('net_amount'), 2),
            'cash_receipt_total' => round($cashReceipt, 2),
            'non_cash_receipt_total' => round($nonCashReceipt, 2),
            'city_ledger_total' => round($activeRows->filter(fn ($row) => preg_match('/COMPANY|CORPORATE|TRAVEL|OTA/i', (string) $row->payment_method))->sum('room_rate'), 2),
            'deposit_total' => round($cashierSummaries->sum('gross_receipt'), 2),
            'exception_count' => $housekeepingExceptions->count(),
            'critical_exception_count' => $housekeepingExceptions->whereIn('severity', ['Critical', 'High'])->count(),
        ];
    }

    private function summaryFromBatch(object $batch): array
    {
        return [
            'business_date' => $this->normalizeDate((string) $batch->business_date),
            'total_rooms' => (int) $batch->total_rooms,
            'occupied_rooms' => (int) $batch->occupied_rooms,
            'vacant_rooms' => (int) $batch->vacant_rooms,
            'out_of_order_rooms' => (int) $batch->out_of_order_rooms,
            'house_use_rooms' => (int) $batch->house_use_rooms,
            'complimentary_rooms' => (int) $batch->complimentary_rooms,
            'arrival_count' => (int) $batch->arrival_count,
            'departure_count' => (int) $batch->departure_count,
            'in_house_count' => (int) $batch->in_house_count,
            'walk_in_count' => (int) $batch->walk_in_count,
            'occupancy_percent' => (float) $batch->occupancy_percent,
            'room_revenue' => (float) $batch->room_revenue,
            'package_revenue' => (float) $batch->package_revenue,
            'other_revenue' => (float) $batch->other_revenue,
            'gross_revenue' => (float) $batch->gross_revenue,
            'cash_receipt_total' => (float) $batch->cash_receipt_total,
            'non_cash_receipt_total' => (float) $batch->non_cash_receipt_total,
            'city_ledger_total' => (float) $batch->city_ledger_total,
            'deposit_total' => (float) $batch->deposit_total,
            'exception_count' => (int) $batch->exception_count,
            'critical_exception_count' => (int) $batch->critical_exception_count,
        ];
    }

    private function batchSummaryUpdatePayload(array $summary): array
    {
        return collect($summary)
            ->except('business_date')
            ->all();
    }

    private function defaultChecklistRows(Collection $housekeepingExceptions): array
    {
        $hasCriticalExceptions = $housekeepingExceptions->whereIn('severity', ['Critical', 'High'])->isNotEmpty();
        $hasAnyExceptions = $housekeepingExceptions->isNotEmpty();

        return [
            ['sequence_no' => 10, 'section' => 'Front Office', 'task_code' => 'FO_ARRIVAL_NO_SHOW', 'task_name' => 'Verify arrivals, no-show, walk-in, and stay-over room rack', 'control_level' => 'Critical', 'status' => 'Pending', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 20, 'section' => 'Front Office', 'task_code' => 'FO_INHOUSE_RECON', 'task_name' => 'Reconcile in-house list with active registration and room status', 'control_level' => 'Critical', 'status' => $hasCriticalExceptions ? 'Blocked' : 'Ready', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 30, 'section' => 'Housekeeping', 'task_code' => 'HK_DISCREPANCY', 'task_name' => 'Resolve room status discrepancy and vacant dirty follow up', 'control_level' => 'Critical', 'status' => $hasAnyExceptions ? 'Blocked' : 'Ready', 'required_role' => 'Housekeeping Supervisor'],
            ['sequence_no' => 40, 'section' => 'Cashier', 'task_code' => 'CS_SHIFT_CLOSE', 'task_name' => 'Close cashier shift, deposit, refund, and cash drop reconciliation', 'control_level' => 'Critical', 'status' => 'Pending', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 50, 'section' => 'Revenue', 'task_code' => 'RV_ROOM_REVENUE', 'task_name' => 'Validate room revenue, complimentary, house-use, discount, and package rate', 'control_level' => 'Critical', 'status' => 'Pending', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 60, 'section' => 'Revenue', 'task_code' => 'RV_OUTLET_INTERFACE', 'task_name' => 'Confirm outlet/interface postings from restaurant, minibar, laundry, spa, and telephone', 'control_level' => 'Standard', 'status' => 'Pending', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 70, 'section' => 'AR', 'task_code' => 'AR_CITY_LEDGER', 'task_name' => 'Review city ledger, OTA, company, travel agent, and credit limit exposure', 'control_level' => 'Standard', 'status' => 'Pending', 'required_role' => 'Accounting'],
            ['sequence_no' => 80, 'section' => 'Security', 'task_code' => 'SC_DOCUMENT_BACKUP', 'task_name' => 'Archive night audit report, folio exception, and system backup reference', 'control_level' => 'Standard', 'status' => 'Pending', 'required_role' => 'Night Auditor'],
            ['sequence_no' => 90, 'section' => 'Management', 'task_code' => 'MG_DUTY_MANAGER', 'task_name' => 'Duty manager review for critical exceptions and final audit package', 'control_level' => 'Critical', 'status' => 'Pending', 'required_role' => 'Duty Manager'],
            ['sequence_no' => 100, 'section' => 'Finance', 'task_code' => 'FN_CONTROLLER_SIGNOFF', 'task_name' => 'Financial controller approval for closed business date', 'control_level' => 'Critical', 'status' => 'Pending', 'required_role' => 'Financial Controller'],
        ];
    }

    private function seedApprovalRows(int $batchId): void
    {
        foreach ($this->defaultApprovalRows($batchId) as $row) {
            $exists = DB::table('night_audit_approvals')
                ->where('batch_id', $batchId)
                ->where('approval_level', $row['approval_level'])
                ->where('role_name', $row['role_name'])
                ->exists();

            if (!$exists) {
                DB::table('night_audit_approvals')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    private function defaultApprovalRows(int $batchId): array
    {
        return [
            ['batch_id' => $batchId, 'approval_level' => 1, 'role_name' => 'Night Auditor', 'approver_name' => null, 'status' => 'Pending', 'approved_at' => null, 'remarks' => null],
            ['batch_id' => $batchId, 'approval_level' => 2, 'role_name' => 'Duty Manager', 'approver_name' => null, 'status' => 'Pending', 'approved_at' => null, 'remarks' => null],
            ['batch_id' => $batchId, 'approval_level' => 3, 'role_name' => 'Financial Controller', 'approver_name' => null, 'status' => 'Pending', 'approved_at' => null, 'remarks' => null],
        ];
    }

    private function countRowsByDate(string $table, string $column, string $auditDate): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)
            ->whereDate($column, $auditDate)
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->count();
    }

    private function generateAuditNo(string $auditDate): string
    {
        $base = 'NA-' . Carbon::parse($auditDate)->format('Ymd');
        $sequence = 1;

        do {
            $auditNo = $base . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table('night_audit_batches')->where('audit_no', $auditNo)->exists());

        return $auditNo;
    }

    private function generateAdjustmentNo(object $batch): string
    {
        $base = 'NAA-' . Carbon::parse($batch->business_date)->format('Ymd');
        $sequence = (int) DB::table('night_audit_adjustments')
            ->where('batch_id', $batch->id)
            ->count() + 1;

        do {
            $adjustmentNo = $base . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table('night_audit_adjustments')->where('adjustment_no', $adjustmentNo)->exists());

        return $adjustmentNo;
    }

    private function schemaReady(): bool
    {
        foreach (self::REQUIRED_TABLES as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function hotelDayStartTime(): string
    {
        if (!Schema::hasTable('SETUP')) {
            return '00:00:00';
        }

        $value = DB::table('SETUP')
            ->whereRaw("RTRIM(Kode) = '01'")
            ->value('JamMasuk');

        return $this->normalizeTime($value ?: '00:00:00');
    }

    private function normalizeDate(string $value): string
    {
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return now()->format('Y-m-d');
        }
    }

    private function safeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function combineLegacyDateTime($date, $time): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', Carbon::parse($date)->format('Y-m-d') . ' ' . $this->normalizeTime($time));
        } catch (\Throwable $exception) {
            return Carbon::parse($date)->startOfDay();
        }
    }

    private function normalizeTime($value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '00:00:00';
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'g:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat($format, strtoupper($raw))->format('H:i:s');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($raw)->format('H:i:s');
        } catch (\Throwable $exception) {
            return '00:00:00';
        }
    }

    private function currentUserName(): string
    {
        return strtoupper(trim((string) session('user', 'SYSTEM'))) ?: 'SYSTEM';
    }
}
