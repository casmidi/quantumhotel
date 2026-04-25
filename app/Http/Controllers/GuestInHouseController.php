<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GuestInHouseController extends Controller
{
    public function index(Request $request)
    {
        $businessDate = $this->resolveBusinessDate($request);
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 50), 10), 150);
        $schemaReady = $this->schemaReady();
        $selectedBatch = $schemaReady ? $this->findAuditBatch($businessDate) : null;
        $rows = $schemaReady ? $this->loadGuestRows($businessDate, $search) : collect();
        $directory = $this->paginateCollection($rows, $perPage, $request);
        $summary = $this->buildSummary($rows, $businessDate, $selectedBatch);
        $auditBatches = $this->loadAuditBatches();

        return $this->respond($request, 'reports.guest-in-house.index', [
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
        $rows = $schemaReady ? $this->loadGuestRows($businessDate, $search) : collect();
        $summary = $this->buildSummary($rows, $businessDate, $selectedBatch);

        return $this->respond($request, 'reports.guest-in-house.print', [
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

    private function loadGuestRows(string $businessDate, string $search = ''): Collection
    {
        $checkoutDateExpression = Schema::hasColumn('DATA2', 'TglOut')
            ? 'COALESCE(DATA2.TglOut, DATA2.TglKeluar)'
            : 'DATA2.TglKeluar';

        $query = DB::table('DATA2')
            ->join('ROOM', 'DATA2.Kode', '=', 'ROOM.Kode')
            ->selectRaw("
                RTRIM(DATA2.Kode) as Kode,
                CASE
                    WHEN RTRIM(COALESCE(DATA2.Guest2, '')) = '' THEN RTRIM(COALESCE(DATA2.Guest, ''))
                    ELSE RTRIM(COALESCE(DATA2.Guest, '')) + ' / ' + RTRIM(COALESCE(DATA2.Guest2, ''))
                END as Guest,
                RTRIM(COALESCE(DATA2.Kota, '')) as Kota,
                CASE WHEN RTRIM(COALESCE(DATA2.Pst, '')) = '' THEN 'C/I' ELSE 'C/O' END as PST,
                RTRIM(COALESCE(DATA2.KodeNegara, '')) as KodeNegara,
                COALESCE(DATA2.Person, 0) as Person,
                DATA2.TglIn as TglIn,
                DATA2.JamIn as JamIn,
                DATA2.TglKeluar as TglKeluar,
                RTRIM(COALESCE(DATA2.Usaha, '')) as Usaha,
                SUBSTRING(RTRIM(COALESCE(DATA2.RegNo, '')), 7, 4) as Officer,
                CASE
                    WHEN UPPER(RTRIM(COALESCE(DATA2.Payment, ''))) = 'COMPLIMENT' THEN 0
                    WHEN COALESCE(DATA2.Nominal, 0) > 0 THEN DATA2.Nominal
                    ELSE COALESCE(DATA2.Rate1, 0) - ((COALESCE(DATA2.Rate2, 0) * COALESCE(DATA2.Disc, 0)) / 100.0)
                END as Rate1,
                RTRIM(COALESCE(DATA2.Remark, '')) as Remark,
                CASE
                    WHEN DATEDIFF(day, DATA2.TglIn, ?) <= 0 THEN 1
                    ELSE DATEDIFF(day, DATA2.TglIn, ?)
                END as [Day],
                LEFT(RTRIM(COALESCE(ROOM.Urut, '')), 2) as Lantai,
                COALESCE(DATA2.BF, 0) as BF,
                RTRIM(COALESCE(DATA2.RegNo, '')) as RegNo,
                RTRIM(COALESCE(DATA2.RegNo2, '')) as RegNo2,
                RTRIM(COALESCE(DATA2.Payment, '')) as Payment,
                RTRIM(COALESCE(DATA2.Package, '')) as PackageCode,
                RTRIM(COALESCE(DATA2.Tipe, '')) as Segment,
                RTRIM(COALESCE(ROOM.Nama, '')) as RoomClass,
                RTRIM(COALESCE(ROOM.Urut, '')) as RoomOrder,
                $checkoutDateExpression as ActualOutDate
            ", [$businessDate, $businessDate])
            ->whereRaw("RTRIM(DATA2.Kode) <> '999'")
            ->whereRaw('COALESCE(DATA2.BF, 0) = 0')
            ->where(function ($dateScope) use ($businessDate, $checkoutDateExpression) {
                $dateScope
                    ->where(function ($active) use ($businessDate) {
                        $active
                            ->whereRaw('CAST(DATA2.TglIn AS date) <= ?', [$businessDate])
                            ->whereRaw("RTRIM(COALESCE(DATA2.Pst, '')) = ''");
                    })
                    ->orWhere(function ($arrival) use ($businessDate) {
                        $arrival->whereRaw('CAST(DATA2.TglIn AS date) = ?', [$businessDate]);
                    })
                    ->orWhere(function ($departure) use ($businessDate, $checkoutDateExpression) {
                        $departure
                            ->whereRaw('CAST(DATA2.TglIn AS date) <= ?', [$businessDate])
                            ->whereRaw("CAST($checkoutDateExpression AS date) >= ?", [$businessDate]);
                    });
            });

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
                    ->orWhereRaw('UPPER(RTRIM(DATA2.Usaha)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(DATA2.KodeNegara)) LIKE ?', [$keyword]);
            });
        }

        return $query
            ->orderByRaw("RTRIM(COALESCE(ROOM.Urut, ''))")
            ->orderByRaw("RTRIM(COALESCE(DATA2.Kode, ''))")
            ->orderBy('DATA2.TglIn')
            ->get()
            ->map(fn (object $row) => $this->hydrateRow($row, $businessDate))
            ->values();
    }

    private function hydrateRow(object $row, string $businessDate): object
    {
        foreach (['Kode', 'Guest', 'Kota', 'PST', 'KodeNegara', 'Usaha', 'Officer', 'Remark', 'RegNo', 'RegNo2', 'Payment', 'PackageCode', 'Segment', 'RoomClass', 'RoomOrder', 'Lantai'] as $field) {
            $row->{$field} = trim((string) ($row->{$field} ?? ''));
        }

        $row->Person = (int) ($row->Person ?? 0);
        $row->Rate1 = round((float) ($row->Rate1 ?? 0), 2);
        $row->Day = max((int) ($row->Day ?? 1), 1);
        $row->BF = (int) ($row->BF ?? 0);
        $row->TglInDisplay = $this->formatDate($row->TglIn ?? null);
        $row->JamInDisplay = $this->formatTime($row->JamIn ?? null);
        $row->TglKeluarDisplay = $this->formatDate($row->TglKeluar ?? null);
        $row->status_label = $row->PST === 'C/O' ? 'Checked Out' : 'In House';
        $row->control_flag = $this->controlFlag($row, $businessDate);

        return $row;
    }

    private function buildSummary(Collection $rows, string $businessDate, ?object $selectedBatch): array
    {
        $inHouseRows = $rows->where('PST', 'C/I');
        $checkedOutRows = $rows->where('PST', 'C/O');
        $floors = $rows
            ->groupBy(fn ($row) => $row->Lantai !== '' ? $row->Lantai : 'NA')
            ->map(fn (Collection $floorRows, string $floor) => (object) [
                'floor' => $floor,
                'rooms' => $floorRows->pluck('Kode')->unique()->count(),
                'guests' => $floorRows->count(),
                'pax' => $floorRows->sum('Person'),
            ])
            ->sortBy('floor', SORT_NATURAL)
            ->values();

        return [
            'business_date' => $businessDate,
            'source' => $selectedBatch
                ? 'Night Audit ' . trim((string) $selectedBatch->status)
                : 'Live Business Date',
            'audit_no' => $selectedBatch->audit_no ?? null,
            'total_rows' => $rows->count(),
            'rooms' => $rows->pluck('Kode')->filter()->unique()->count(),
            'in_house' => $inHouseRows->count(),
            'checked_out' => $checkedOutRows->count(),
            'pax' => $rows->sum('Person'),
            'rate_total' => round($rows->sum('Rate1'), 2),
            'overstay' => $rows->where('control_flag', 'OVERSTAY')->count(),
            'complimentary' => $rows->filter(fn ($row) => str_contains(strtoupper((string) $row->Payment), 'COMPLIMENT'))->count(),
            'floors' => $floors,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function controlFlag(object $row, string $businessDate): ?string
    {
        $payment = strtoupper(trim((string) ($row->Payment ?? '')));
        if (str_contains($payment, 'COMPLIMENT')) {
            return 'APPROVAL';
        }

        if ($row->PST === 'C/I' && !empty($row->TglKeluar)) {
            try {
                if (Carbon::parse($row->TglKeluar)->lt(Carbon::parse($businessDate)->startOfDay())) {
                    return 'OVERSTAY';
                }
            } catch (\Throwable $exception) {
                return null;
            }
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
        return '/guest-in-house/print?' . http_build_query(array_filter([
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
