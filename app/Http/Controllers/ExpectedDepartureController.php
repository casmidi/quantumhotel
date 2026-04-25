<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpectedDepartureController extends Controller
{
    public function index(Request $request)
    {
        $businessDate = $this->resolveBusinessDate($request);
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 50), 10), 150);
        $schemaReady = $this->schemaReady();
        $selectedBatch = $schemaReady ? $this->findAuditBatch($businessDate) : null;
        $rows = $schemaReady ? $this->loadDepartureRows($businessDate, $search) : collect();
        $directory = $this->paginateCollection($rows, $perPage, $request);
        $summary = $this->buildSummary($rows, $businessDate, $selectedBatch);
        $auditBatches = $this->loadAuditBatches();

        return $this->respond($request, 'reports.expected-departure.index', [
            'profile' => $this->loadHotelProfile(),
            'schemaReady' => $schemaReady,
            'businessDate' => $businessDate,
            'selectedBatch' => $selectedBatch,
            'auditBatches' => $auditBatches,
            'rows' => $rows,
            'directory' => $directory,
            'summary' => $summary,
            'search' => $search,
            'perPage' => $perPage,
            'printUrl' => $this->printUrl($businessDate, $search),
        ], [
            'schema_ready' => $schemaReady,
            'business_date' => $businessDate,
            'selected_batch' => $selectedBatch,
            'summary' => $summary,
            'directory' => $this->paginatorPayload($directory),
        ]);
    }

    public function print(Request $request)
    {
        $businessDate = $this->resolveBusinessDate($request);
        $search = trim((string) $request->query('search', ''));
        $schemaReady = $this->schemaReady();
        $selectedBatch = $schemaReady ? $this->findAuditBatch($businessDate) : null;
        $rows = $schemaReady ? $this->loadDepartureRows($businessDate, $search) : collect();
        $summary = $this->buildSummary($rows, $businessDate, $selectedBatch);

        return $this->respond($request, 'reports.expected-departure.print', [
            'profile' => $this->loadHotelProfile(),
            'schemaReady' => $schemaReady,
            'businessDate' => $businessDate,
            'selectedBatch' => $selectedBatch,
            'rows' => $rows,
            'summary' => $summary,
            'search' => $search,
        ], [
            'schema_ready' => $schemaReady,
            'business_date' => $businessDate,
            'selected_batch' => $selectedBatch,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    private function loadDepartureRows(string $businessDate, string $search = ''): Collection
    {
        $query = DB::table('DATA2')
            ->leftJoin('ROOM', 'DATA2.Kode', '=', 'ROOM.Kode')
            ->selectRaw("
                RTRIM(COALESCE(DATA2.Kode, '')) as Kode,
                RTRIM(COALESCE(DATA2.Kota, '')) as Kota,
                RTRIM(COALESCE(DATA2.Guest, '')) as Guest,
                RTRIM(COALESCE(DATA2.Payment, '')) as Payment,
                RTRIM(COALESCE(DATA2.Remark, '')) as Remark,
                DATA2.TglIn as TglIn,
                DATA2.JamIn as JamIn,
                DATA2.TglKeluar as TglKeluar,
                COALESCE(DATA2.Person, 0) as Pax,
                RTRIM(COALESCE(DATA2.RegNo, '')) as RegNo,
                RTRIM(COALESCE(DATA2.RegNo2, '')) as RegNo2,
                RTRIM(COALESCE(DATA2.Guest2, '')) as Guest2,
                RTRIM(COALESCE(DATA2.Usaha, '')) as Usaha,
                RTRIM(COALESCE(DATA2.KodeNegara, '')) as KodeNegara,
                RTRIM(COALESCE(DATA2.Package, '')) as PackageCode,
                RTRIM(COALESCE(DATA2.Tipe, '')) as Segment,
                COALESCE(DATA2.Nominal, 0) as Rate,
                RTRIM(COALESCE(ROOM.Nama, '')) as RoomClass,
                RTRIM(COALESCE(ROOM.Urut, '')) as RoomOrder,
                LEFT(RTRIM(COALESCE(ROOM.Urut, '')), 2) as Lantai,
                DATEDIFF(day, DATA2.TglIn, ?) as StayNights,
                CASE
                    WHEN CAST(DATA2.TglKeluar AS date) < ? THEN DATEDIFF(day, DATA2.TglKeluar, ?)
                    ELSE 0
                END as OverdueDays
            ", [$businessDate, $businessDate, $businessDate])
            ->whereRaw('CAST(DATA2.TglKeluar AS date) <= ?', [$businessDate])
            ->whereRaw("RTRIM(COALESCE(DATA2.Pst, '')) = ''")
            ->whereRaw("RTRIM(COALESCE(DATA2.Kode, '')) <> '999'");

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($scope) use ($keyword) {
                $scope
                    ->whereRaw('UPPER(RTRIM(DATA2.Kode)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.RegNo)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.RegNo2)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Guest)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Guest2)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Kota)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Payment)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Remark)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Usaha)) LIKE ?', [$keyword]);
            });
        }

        return $query
            ->orderByRaw("RTRIM(COALESCE(DATA2.Kode, ''))")
            ->orderByRaw("RTRIM(COALESCE(ROOM.Urut, ''))")
            ->get()
            ->map(fn (object $row) => $this->hydrateRow($row, $businessDate))
            ->values();
    }

    private function hydrateRow(object $row, string $businessDate): object
    {
        foreach (['Kode', 'Kota', 'Guest', 'Payment', 'Remark', 'RegNo', 'RegNo2', 'Guest2', 'Usaha', 'KodeNegara', 'PackageCode', 'Segment', 'RoomClass', 'RoomOrder', 'Lantai'] as $field) {
            $row->{$field} = trim((string) ($row->{$field} ?? ''));
        }

        $row->Pax = (int) ($row->Pax ?? 0);
        $row->Rate = round((float) ($row->Rate ?? 0), 2);
        $row->StayNights = max((int) ($row->StayNights ?? 0), 1);
        $row->OverdueDays = max((int) ($row->OverdueDays ?? 0), 0);
        $row->GuestDisplay = $row->Guest2 !== '' ? $row->Guest . ' / ' . $row->Guest2 : $row->Guest;
        $row->TglInDisplay = $this->formatDate($row->TglIn ?? null);
        $row->JamInDisplay = $this->formatTime($row->JamIn ?? null);
        $row->TglKeluarDisplay = $this->formatDate($row->TglKeluar ?? null);
        $row->departure_status = $this->departureStatus($row, $businessDate);
        $row->control_flag = $this->controlFlag($row);

        return $row;
    }

    private function buildSummary(Collection $rows, string $businessDate, ?object $selectedBatch): array
    {
        $paymentGroups = $rows
            ->groupBy(fn ($row) => strtoupper(trim((string) $row->Payment)) ?: 'UNKNOWN')
            ->map(fn (Collection $paymentRows, string $payment) => (object) [
                'payment' => $payment,
                'rooms' => $paymentRows->pluck('Kode')->unique()->count(),
                'guests' => $paymentRows->count(),
                'pax' => $paymentRows->sum('Pax'),
            ])
            ->sortByDesc('guests')
            ->values();

        return [
            'business_date' => $businessDate,
            'source' => $selectedBatch
                ? 'Night Audit ' . trim((string) $selectedBatch->status)
                : 'Live Business Date',
            'audit_no' => $selectedBatch->audit_no ?? null,
            'total_rows' => $rows->count(),
            'rooms' => $rows->pluck('Kode')->filter()->unique()->count(),
            'due_today' => $rows->where('OverdueDays', 0)->count(),
            'overdue' => $rows->filter(fn ($row) => (int) $row->OverdueDays > 0)->count(),
            'pax' => $rows->sum('Pax'),
            'rate_total' => round($rows->sum('Rate'), 2),
            'payment_review' => $rows->where('control_flag', 'PAYMENT REVIEW')->count(),
            'payment_groups' => $paymentGroups,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function departureStatus(object $row, string $businessDate): string
    {
        if (empty($row->TglKeluar)) {
            return 'No Date';
        }

        try {
            return Carbon::parse($row->TglKeluar)->lt(Carbon::parse($businessDate)->startOfDay())
                ? 'Overdue'
                : 'Due Today';
        } catch (\Throwable $exception) {
            return 'Review';
        }
    }

    private function controlFlag(object $row): ?string
    {
        if ((int) ($row->OverdueDays ?? 0) > 0) {
            return 'EXTENSION REQUIRED';
        }

        if (trim((string) ($row->Payment ?? '')) === '') {
            return 'PAYMENT REVIEW';
        }

        return null;
    }

    private function resolveBusinessDate(Request $request): string
    {
        $requestedDate = trim((string) $request->query('business_date', ''));
        if ($requestedDate !== '') {
            return $this->normalizeDate($requestedDate);
        }

        $batchId = (int) $request->query('batch_id', 0);
        if ($batchId > 0 && Schema::hasTable('night_audit_batches')) {
            $batch = DB::table('night_audit_batches')->where('id', $batchId)->first();
            if ($batch) {
                return $this->normalizeDate((string) $batch->business_date);
            }
        }

        if (Schema::hasTable('night_audit_batches')) {
            $latestBatch = DB::table('night_audit_batches')
                ->orderByDesc('business_date')
                ->orderByDesc('id')
                ->first();

            if ($latestBatch) {
                return $this->normalizeDate((string) $latestBatch->business_date);
            }
        }

        return now()->format('Y-m-d');
    }

    private function findAuditBatch(string $businessDate): ?object
    {
        if (!Schema::hasTable('night_audit_batches')) {
            return null;
        }

        return DB::table('night_audit_batches')
            ->whereDate('business_date', $businessDate)
            ->orderByDesc('id')
            ->first();
    }

    private function loadAuditBatches(): Collection
    {
        if (!Schema::hasTable('night_audit_batches')) {
            return collect();
        }

        return DB::table('night_audit_batches')
            ->orderByDesc('business_date')
            ->orderByDesc('id')
            ->limit(45)
            ->get();
    }

    private function schemaReady(): bool
    {
        return Schema::hasTable('DATA2') && Schema::hasTable('ROOM');
    }

    private function printUrl(string $businessDate, string $search): string
    {
        return '/expected-departure/print?' . http_build_query(array_filter([
            'business_date' => $businessDate,
            'search' => $search !== '' ? $search : null,
        ]));
    }

    private function loadHotelProfile(): array
    {
        $profile = HotelBranding::profile();
        $profile['logo_absolute_path'] = HotelBranding::logoAbsolutePath($profile);
        $profile['logo_url'] = !empty($profile['logo_path'])
            ? url('/settings/hotel-branding/logo?ts=' . rawurlencode((string) ($profile['updated_at'] ?? now()->timestamp)))
            : null;

        return $profile;
    }

    private function normalizeDate(string $value): string
    {
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return now()->format('Y-m-d');
        }
    }

    private function formatDate($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $exception) {
            return trim((string) $value);
        }
    }

    private function formatTime($value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'g:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat($format, strtoupper($raw))->format('H:i');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable $exception) {
            return $raw;
        }
    }
}
