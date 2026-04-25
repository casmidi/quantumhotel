<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReceptionCustomerRecaptulationController extends Controller
{
    private const REQUIRED_COLUMNS = [
        'ROOM',
        'TOTAL',
        'TGL',
        'TAX',
        'LAUNDRY',
        'OTHER',
        'CAFE',
        'KODE',
        'REGNO',
        'MEETING',
        'GUEST',
        'PERSON',
        'TGLIN',
        'TGLOUT',
        'TIPE',
        'INVOICE2',
        'SERVICE',
        'DPP',
        'INVOICE1',
    ];

    public function index(Request $request)
    {
        [$startDate, $endDate, $periodWarning] = $this->resolvePeriod($request);
        $regno = trim((string) $request->query('regno', ''));
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 50), 10), 300);
        $summarySort = $this->normalizeSort((string) $request->query('summary_sort', 'regno'), $this->summarySortFields(), 'regno');
        $summaryDir = $this->normalizeSortDirection((string) $request->query('summary_dir', 'asc'));
        $detailSort = $this->normalizeSort((string) $request->query('detail_sort', ''), $this->detailSortFields(), '');
        $detailDir = $this->normalizeSortDirection((string) $request->query('detail_dir', 'asc'));
        $schemaReady = $this->schemaReady();
        $rows = $schemaReady ? $this->loadReportRows($startDate, $endDate, $regno, $search) : collect();
        $rows = $detailSort !== '' ? $this->sortReportRows($rows, $detailSort, $detailDir) : $rows;
        $summaryGroups = $this->sortRegnoGroups($this->buildRegnoGroups($rows), $summarySort, $summaryDir);
        $summaryDirectory = $this->paginateCollection($summaryGroups, $perPage, $request, ['pageName' => 'summary_page']);
        $directory = $this->paginateCollection($rows, $perPage, $request);
        $summary = $this->buildSummary($rows, $startDate, $endDate);
        $postingStatus = $this->loadPostingStatus($startDate);
        $auditBatch = $this->findAuditBatch($endDate);

        return $this->respond($request, 'reports.reception-customer-recaptulation.index', [
            'profile' => $this->loadHotelProfile(),
            'schemaReady' => $schemaReady,
            'missingColumns' => $this->missingColumns(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodWarning' => $periodWarning,
            'postingStatus' => $postingStatus,
            'auditBatch' => $auditBatch,
            'rows' => $rows,
            'summaryDirectory' => $summaryDirectory,
            'directory' => $directory,
            'summary' => $summary,
            'regno' => $regno,
            'search' => $search,
            'perPage' => $perPage,
            'summarySort' => $summarySort,
            'summaryDir' => $summaryDir,
            'detailSort' => $detailSort,
            'detailDir' => $detailDir,
            'printUrl' => $this->printUrl($startDate, $endDate, $regno, $search),
        ], [
            'schema_ready' => $schemaReady,
            'missing_columns' => $this->missingColumns(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period_warning' => $periodWarning,
            'posting_status' => $postingStatus,
            'audit_batch' => $auditBatch,
            'summary' => $summary,
            'summary_directory' => $this->paginatorPayload($summaryDirectory),
            'directory' => $this->paginatorPayload($directory),
        ]);
    }

    public function print(Request $request)
    {
        [$startDate, $endDate, $periodWarning] = $this->resolvePeriod($request);
        $regno = trim((string) $request->query('regno', ''));
        $search = trim((string) $request->query('search', ''));
        $schemaReady = $this->schemaReady();
        $rows = $schemaReady ? $this->loadReportRows($startDate, $endDate, $regno, $search) : collect();
        $summary = $this->buildSummary($rows, $startDate, $endDate);
        $postingStatus = $this->loadPostingStatus($startDate);
        $auditBatch = $this->findAuditBatch($endDate);

        return $this->respond($request, 'reports.reception-customer-recaptulation.print', [
            'profile' => $this->loadHotelProfile(),
            'schemaReady' => $schemaReady,
            'missingColumns' => $this->missingColumns(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodWarning' => $periodWarning,
            'postingStatus' => $postingStatus,
            'auditBatch' => $auditBatch,
            'rows' => $rows,
            'summary' => $summary,
            'regno' => $regno,
            'search' => $search,
        ], [
            'schema_ready' => $schemaReady,
            'missing_columns' => $this->missingColumns(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period_warning' => $periodWarning,
            'posting_status' => $postingStatus,
            'audit_batch' => $auditBatch,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    private function loadReportRows(string $startDate, string $endDate, string $regno = '', string $search = ''): Collection
    {
        if ($this->hasOnlineRows($startDate, $endDate, $regno)) {
            return $this->loadOnlinePostingRows($startDate, $endDate, $regno, $search);
        }

        return $this->hasPostedPb1Rows($startDate, $endDate, $regno)
            ? $this->loadPb1Rows($startDate, $endDate, $regno, $search)
            : collect();
    }

    private function loadPb1Rows(string $startDate, string $endDate, string $regno = '', string $search = ''): Collection
    {
        $query = DB::table('PB1')
            ->selectRaw("
                RTRIM(COALESCE(PB1.Invoice2, '')) + RTRIM(COALESCE(PB1.Regno, '')) as Invoice3,
                PB1.Tgl as Tanggal,
                RTRIM(COALESCE(PB1.Kode, '')) as Kode,
                COALESCE(PB1.[ROOM], 0) as Room1,
                COALESCE(PB1.Cafe, 0) + COALESCE(PB1.Meeting, 0) as Cafe1,
                COALESCE(PB1.Laundry, 0) as Vila,
                COALESCE(PB1.[Other], 0) as Other1,
                RTRIM(COALESCE(PB1.Tipe, '')) as Tipe,
                COALESCE(PB1.Meeting, 0) as Meeting,
                COALESCE(PB1.Person, 0) as Person,
                RTRIM(COALESCE(PB1.Guest, '')) as Guest,
                PB1.TglIn as TglIn,
                PB1.TglOut as TglOut,
                RTRIM(COALESCE(PB1.Regno, '')) as Regno,
                COALESCE(PB1.DPP, 0) as Dpp,
                COALESCE(PB1.Tax, 0) as Tax,
                COALESCE(PB1.Service, 0) as Service,
                COALESCE(PB1.Total, 0) as Total,
                RTRIM(COALESCE(PB1.Invoice1, '')) as Invoice1,
                RTRIM(COALESCE(PB1.Invoice2, '')) as Invoice2
            ")
            ->whereRaw('CAST(PB1.Tgl AS date) >= ?', [$startDate])
            ->whereRaw('CAST(PB1.Tgl AS date) <= ?', [$endDate]);

        if ($regno !== '') {
            $query->whereRaw("RTRIM(COALESCE(PB1.Regno, '')) = ?", [$regno]);
        }

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($scope) use ($keyword) {
                $scope
                    ->whereRaw('UPPER(RTRIM(COALESCE(PB1.Invoice2, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(PB1.Regno, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(PB1.Kode, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(PB1.Guest, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(PB1.Tipe, \'\'))) LIKE ?', [$keyword]);
            });
        }

        return $query
            ->orderByRaw("RTRIM(COALESCE(PB1.Regno, ''))")
            ->orderByRaw("RTRIM(COALESCE(PB1.Invoice2, ''))")
            ->orderBy('PB1.Tgl')
            ->orderByRaw("RTRIM(COALESCE(PB1.Kode, ''))")
            ->get()
            ->map(fn (object $row) => $this->hydrateRow($row))
            ->values();
    }

    private function hasPostedPb1Rows(string $startDate, string $endDate, string $regno = ''): bool
    {
        if (!Schema::hasTable('PB1')) {
            return false;
        }

        $query = DB::table('PB1')
            ->whereRaw('CAST(PB1.Tgl AS date) >= ?', [$startDate])
            ->whereRaw('CAST(PB1.Tgl AS date) <= ?', [$endDate]);

        if ($regno !== '') {
            $query->whereRaw("RTRIM(COALESCE(PB1.Regno, '')) = ?", [$regno]);
        }

        return $query->exists();
    }

    private function hasOnlineRows(string $startDate, string $endDate, string $regno = ''): bool
    {
        if (!Schema::hasTable('ONLINE')) {
            return false;
        }

        $query = DB::table('ONLINE')
            ->whereRaw('CAST(ONLINE.Tgl AS date) >= ?', [$startDate])
            ->whereRaw('CAST(ONLINE.Tgl AS date) <= ?', [$endDate]);

        if ($regno !== '') {
            $query->whereRaw("RTRIM(COALESCE(ONLINE.Regno, '')) = ?", [$regno]);
        }

        return $query->exists();
    }

    private function loadOnlinePostingRows(string $startDate, string $endDate, string $regno = '', string $search = ''): Collection
    {
        if (!Schema::hasTable('ONLINE')) {
            return collect();
        }

        $query = DB::table('ONLINE')
            ->leftJoin('DATA2', function ($join) {
                $join->whereRaw("RTRIM(COALESCE(DATA2.Regno, '')) = RTRIM(COALESCE(ONLINE.Regno, ''))")
                    ->whereRaw("RTRIM(COALESCE(DATA2.Kode, '')) = RTRIM(COALESCE(ONLINE.Kode, ''))")
                    ->whereRaw('CAST(DATA2.TglOut AS date) = CAST(ONLINE.Tgl AS date)');
            })
            ->leftJoin('ROOM', 'ROOM.Kode', '=', 'DATA2.Kode')
            ->selectRaw("
                ONLINE.Tgl,
                ONLINE.Tgl2,
                RTRIM(COALESCE(ONLINE.Nofak, '')) as Nofak,
                RTRIM(COALESCE(ONLINE.Nofak2, '')) as Nofak2,
                RTRIM(COALESCE(ONLINE.Nomor, '')) as Nomor,
                RTRIM(COALESCE(ONLINE.Regno, '')) as Regno,
                RTRIM(COALESCE(ONLINE.KodePjk, '')) as KodePjk,
                RTRIM(COALESCE(ONLINE.Kode, '')) as Kode,
                RTRIM(COALESCE(ONLINE.Ket, '')) as Ket,
                COALESCE(ONLINE.Nominal, 0) as Nominal,
                RTRIM(COALESCE(ONLINE.Usaha, '')) as OnlineUsaha,
                RTRIM(COALESCE(DATA2.Package, '')) as StayPackage,
                RTRIM(COALESCE(DATA2.Tipe, '')) as StayTipe,
                COALESCE(DATA2.Person, 1) as StayPerson,
                RTRIM(COALESCE(DATA2.Guest, '')) as StayGuest,
                DATA2.TglIn as StayTglIn,
                DATA2.TglOut as StayTglOut,
                RTRIM(COALESCE(DATA2.Usaha, '')) as StayUsaha,
                RTRIM(COALESCE(DATA2.Segment, '')) as StaySegment,
                COALESCE(ROOM.Meeting, 0) as StayMeeting
            ")
            ->whereRaw('(CAST(ONLINE.Tgl AS date) >= ? AND CAST(ONLINE.Tgl AS date) <= ? OR CAST(ONLINE.Tgl2 AS date) >= ? AND CAST(ONLINE.Tgl2 AS date) <= ?)', [
                $startDate,
                $endDate,
                $startDate,
                $endDate,
            ]);

        if ($regno !== '') {
            $query->whereRaw("RTRIM(COALESCE(ONLINE.Regno, '')) = ?", [$regno]);
        }

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($scope) use ($keyword) {
                $scope
                    ->whereRaw('UPPER(RTRIM(COALESCE(ONLINE.Nomor, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(ONLINE.Regno, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(ONLINE.Kode, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(ONLINE.Ket, \'\'))) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(COALESCE(ONLINE.Usaha, \'\'))) LIKE ?', [$keyword]);
            });
        }

        $packageSplits = $this->loadPackageSplits($startDate, $endDate);

        return $query
            ->orderByRaw("RTRIM(COALESCE(ONLINE.Regno, ''))")
            ->orderByRaw("RTRIM(COALESCE(ONLINE.Nomor, ''))")
            ->orderBy('ONLINE.Tgl')
            ->orderByRaw("RTRIM(COALESCE(ONLINE.Kode, ''))")
            ->get()
            ->map(fn (object $row) => $this->onlineRowToReportRow($row, $packageSplits))
            ->map(fn (object $row) => $this->hydrateRow($row))
            ->values();
    }

    private function onlineRowToReportRow(object $online, array $packageSplits): object
    {
        $regno = trim((string) $online->Regno);
        $kode = trim((string) $online->Kode);
        $kodePjk = trim((string) $online->KodePjk);
        $nominal = (float) $online->Nominal;
        $guest = $this->guestFromKet((string) $online->Ket);
        $stay = trim((string) ($online->StayGuest ?? '')) !== '' ? (object) [
            'Regno' => $regno,
            'Package' => $online->StayPackage ?? '',
            'Tipe' => $online->StayTipe ?? '',
            'Person' => $online->StayPerson ?? 1,
            'Guest' => $online->StayGuest ?? '',
            'TglIn' => $online->StayTglIn ?? null,
            'TglOut' => $online->StayTglOut ?? null,
            'Usaha' => $online->StayUsaha ?? '',
            'Segment' => $online->StaySegment ?? '',
            'Meeting' => $online->StayMeeting ?? 0,
        ] : null;

        if ($stay !== null) {
            $guest = $this->decorateGuest($this->lateCheckout((string) $online->Ket) ? '[L] ' . trim((string) $stay->Guest) : trim((string) $stay->Guest), trim((string) $stay->Usaha));
            $tglIn = $kode === '999' ? null : $stay->TglIn;
            $tglOut = $kode === '999' ? null : ($stay->TglOut ?: $online->Tgl);
            $person = (int) ($stay->Person ?: 1);
            $tipe = '';
            $usaha = trim((string) $stay->Usaha);
            $segment = trim((string) $stay->Segment);
            $meetingRoom = (int) ($stay->Meeting ?: 0) === 1;
        } else {
            $tglIn = $kode === '999' ? null : $this->lookupTransactionDate($regno, $kode, $kodePjk, 'in');
            $tglOut = $kode === '999' ? null : $this->lookupTransactionDate($regno, $kode, $kodePjk, 'out');
            $person = 1;
            $tipe = $kodePjk === 'ATE' ? ' EB ' : '';
            $usaha = trim((string) $online->OnlineUsaha);
            $segment = '';
            $meetingRoom = false;
            $guest = $this->decorateGuest($guest, $usaha);
        }

        $amounts = $this->departmentAmounts($online, $stay, $nominal, $meetingRoom, $packageSplits);

        return (object) [
            'Invoice3' => trim((string) $online->Nomor) . $regno,
            'Tanggal' => $online->Tgl2 ?: $online->Tgl,
            'Kode' => $kode,
            'Room1' => $amounts['room'],
            'Cafe1' => $amounts['cafe'] + $amounts['meeting'],
            'Vila' => $amounts['laundry'],
            'Other1' => $amounts['other'],
            'Tipe' => $tipe,
            'Meeting' => $amounts['meeting'],
            'Person' => $person,
            'Guest' => $guest,
            'TglIn' => $tglIn,
            'TglOut' => $tglOut,
            'Regno' => $regno,
            'Dpp' => $nominal,
            'Tax' => $nominal * 0.11,
            'Service' => $nominal * 0.1,
            'Total' => $nominal * 1.21,
            'Invoice1' => trim((string) $online->Nofak2),
            'Invoice2' => trim((string) $online->Nomor),
            'Usaha' => $usaha,
            'Segment' => $segment,
        ];
    }

    private function departmentAmounts(object $online, ?object $stay, float $nominal, bool $meetingRoom, array $packageSplits): array
    {
        $kodePjk = trim((string) $online->KodePjk);
        $amounts = ['room' => 0.0, 'cafe' => 0.0, 'meeting' => 0.0, 'laundry' => 0.0, 'other' => 0.0];

        if ($kodePjk === 'ATM') {
            $amounts['cafe'] = $nominal;
            return $amounts;
        }

        if ($kodePjk === 'ATQ') {
            $amounts['meeting'] = $nominal;
            return $amounts;
        }

        if ($kodePjk === 'ATZ' || $kodePjk === 'ATE') {
            $amounts['other'] = $nominal;
            return $amounts;
        }

        if ($kodePjk !== 'ATS') {
            $amounts['other'] = $nominal;
            return $amounts;
        }

        $package = trim((string) ($stay->Package ?? ''));
        if ($this->lateCheckout((string) $online->Ket) || $stay === null || $package === '') {
            $amounts[$meetingRoom ? 'laundry' : 'room'] = $nominal;
            return $amounts;
        }

        $days = 1;
        try {
            if (!empty($stay->TglIn) && !empty($stay->TglOut)) {
                $days = max(1, Carbon::parse($stay->TglIn)->diffInDays(Carbon::parse($stay->TglOut)));
            }
        } catch (\Throwable $exception) {
            $days = 1;
        }

        $key = trim((string) $stay->Regno) . '|' . trim((string) $online->Kode) . '|' . $package;
        $split = ['room' => 0.0, 'cafe' => 0.0, 'meeting' => 0.0, 'laundry' => 0.0, 'other' => 0.0];

        foreach ($packageSplits[$key] ?? [] as $kind => $value) {
            $value *= $days;
            match ($kind) {
                'ROOM' => $split[$meetingRoom ? 'laundry' : 'room'] += $value,
                'RESTAURANT' => $split['cafe'] += $value,
                'MEETING' => $split['meeting'] += $value,
                'OTHER' => $split['other'] += $value,
                default => $split['room'] += $value,
            };
        }

        if ((int) array_sum($split) !== (int) $nominal || abs(array_sum($split)) < 0.0001) {
            $split = ['room' => 0.0, 'cafe' => 0.0, 'meeting' => 0.0, 'laundry' => 0.0, 'other' => 0.0];
            $split[$meetingRoom ? 'laundry' : 'room'] = $nominal;
        }

        return $split;
    }

    private function loadPackageSplits(string $startDate, string $endDate): array
    {
        foreach (['DATA2', 'PACKAGE', 'PACKAGED', 'STOCKPACKAGE'] as $table) {
            if (!Schema::hasTable($table)) {
                return [];
            }
        }

        return DB::table('DATA2')
            ->join('PACKAGE', 'DATA2.Package', '=', 'PACKAGE.Nofak')
            ->join('PACKAGED', 'PACKAGE.Nofak', '=', 'PACKAGED.Nofak')
            ->join('STOCKPACKAGE', 'STOCKPACKAGE.KodeBrg', '=', 'PACKAGED.KodeBrg')
            ->whereRaw('CAST(DATA2.TglOut AS date) >= ?', [$startDate])
            ->whereRaw('CAST(DATA2.TglOut AS date) <= ?', [$endDate])
            ->selectRaw("
                RTRIM(COALESCE(DATA2.Regno, '')) as Regno,
                RTRIM(COALESCE(DATA2.Kode, '')) as Kode,
                RTRIM(COALESCE(DATA2.Package, '')) as Package,
                RTRIM(COALESCE(STOCKPACKAGE.Kind, '')) as Kind,
                SUM(PACKAGED.Qty * STOCKPACKAGE.HJ / 1.21) as Jumlah
            ")
            ->groupByRaw("
                RTRIM(COALESCE(DATA2.Regno, '')),
                RTRIM(COALESCE(DATA2.Kode, '')),
                RTRIM(COALESCE(DATA2.Package, '')),
                RTRIM(COALESCE(STOCKPACKAGE.Kind, ''))
            ")
            ->get()
            ->groupBy(fn (object $row) => trim((string) $row->Regno) . '|' . trim((string) $row->Kode) . '|' . trim((string) $row->Package))
            ->map(fn (Collection $rows) => $rows
                ->mapWithKeys(fn (object $row) => [strtoupper(trim((string) $row->Kind)) => (float) $row->Jumlah])
                ->all())
            ->all();
    }

    private function lookupTransactionDate(string $regno, string $kode, string $kodePjk, string $direction)
    {
        $map = [
            'ATE' => ['table' => 'EXTRABED', 'date' => 'EXTRABED.Tgl'],
            'ATZ' => ['table' => 'TAMBAH', 'date' => 'TAMBAH.Tgl'],
            'ATM' => ['table' => 'RES2', 'date' => 'RES2.Tgl'],
        ];

        if (!isset($map[$kodePjk]) || !Schema::hasTable('DATA2') || !Schema::hasTable($map[$kodePjk]['table'])) {
            return null;
        }

        $table = $map[$kodePjk]['table'];
        $dateColumn = $direction === 'in' ? 'DATA2.TglIn' : 'DATA2.TglOut';

        $row = DB::table($table)
            ->join('DATA2', 'DATA2.Regno2', '=', $table . '.Regno2')
            ->selectRaw($dateColumn . ' as StayDate, ' . $map[$kodePjk]['date'] . ' as TransactionDate')
            ->whereRaw("RTRIM(COALESCE(DATA2.Regno, '')) = ?", [$regno])
            ->whereRaw("RTRIM(COALESCE(" . $table . ".Kode, '')) = ?", [$kode])
            ->first();

        return $row?->StayDate ?: $row?->TransactionDate;
    }

    private function guestFromKet(string $ket): string
    {
        $parts = explode('->', $ket, 2);

        return trim($parts[1] ?? $ket);
    }

    private function decorateGuest(string $guest, string $usaha): string
    {
        $guest = trim($guest);
        $usaha = trim($usaha);

        if ($usaha === '' || str_contains($guest, '/')) {
            return $guest;
        }

        return mb_substr($guest . ' // ' . $usaha, 0, 100);
    }

    private function lateCheckout(string $ket): bool
    {
        return str_starts_with(trim($ket), 'Room Revenue C/O Late');
    }

    private function hydrateRow(object $row): object
    {
        foreach (['Invoice3', 'Kode', 'Tipe', 'Guest', 'Regno', 'Invoice1', 'Invoice2'] as $field) {
            $row->{$field} = trim((string) ($row->{$field} ?? ''));
        }

        foreach (['Room1', 'Cafe1', 'Vila', 'Other1', 'Meeting', 'Dpp', 'Tax', 'Service', 'Total'] as $field) {
            $row->{$field} = round((float) ($row->{$field} ?? 0), 4);
        }

        $row->Person = (int) ($row->Person ?? 0);
        $row->TanggalDisplay = $this->formatDate($row->Tanggal ?? null);
        $row->TglInDisplay = $this->formatDate($row->TglIn ?? null);
        $row->TglOutDisplay = $this->formatDate($row->TglOut ?? null);
        $row->ComputedTotal = round($row->Dpp + $row->Tax + $row->Service, 4);
        $row->FiscalVariance = round($row->ComputedTotal - $row->Total, 4);
        $row->HasVariance = abs($row->FiscalVariance) > 0.05;

        return $row;
    }

    private function buildSummary(Collection $rows, string $startDate, string $endDate): array
    {
        $totals = [
            'room' => round($rows->sum('Room1'), 4),
            'cafe' => round($rows->sum('Cafe1'), 4),
            'vila' => round($rows->sum('Vila'), 4),
            'other' => round($rows->sum('Other1'), 4),
            'dpp' => round($rows->sum('Dpp'), 4),
            'tax' => round($rows->sum('Tax'), 4),
            'service' => round($rows->sum('Service'), 4),
            'total' => round($rows->sum('Total'), 4),
        ];

        $computedTotal = round($totals['dpp'] + $totals['tax'] + $totals['service'], 4);
        $varianceTotal = round($computedTotal - $totals['total'], 4);

        return [
            'period' => $startDate . ' - ' . $endDate,
            'source' => 'ONLINE Final Posting',
            'mode' => 'Generated',
            'total_rows' => $rows->count(),
            'invoices' => $rows->pluck('Invoice2')->filter()->unique()->count(),
            'registrations' => $rows->pluck('Regno')->filter()->unique()->count(),
            'rooms' => $rows->pluck('Kode')->filter()->unique()->count(),
            'pax' => $rows->sum('Person'),
            'zero_total' => $rows->filter(fn ($row) => abs((float) $row->Total) <= 0.0001)->count(),
            'variance_count' => $rows->where('HasVariance', true)->count(),
            'computed_total' => $computedTotal,
            'variance_total' => $varianceTotal,
            'totals' => $totals,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function buildRegnoGroups(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn ($row) => trim((string) ($row->Regno ?? '')) ?: '-')
            ->map(fn (Collection $groupRows, string $regno) => (object) [
                'regno' => $regno,
                'rows' => $groupRows->values(),
            ])
            ->values();
    }

    private function sortReportRows(Collection $rows, string $sort, string $direction): Collection
    {
        $fields = $this->detailSortFields();
        if (!isset($fields[$sort])) {
            return $rows->values();
        }

        $field = $fields[$sort];

        return $rows
            ->sortBy(fn ($row) => $this->sortValue($row, $field), SORT_REGULAR, $direction === 'desc')
            ->values();
    }

    private function sortRegnoGroups(Collection $groups, string $sort, string $direction): Collection
    {
        $fields = $this->summarySortFields();
        if (!isset($fields[$sort])) {
            return $groups->values();
        }

        return $groups
            ->sortBy(function ($group) use ($sort, $fields) {
                $rows = $group->rows instanceof Collection ? $group->rows : collect($group->rows);
                $field = $fields[$sort];

                return match ($field) {
                    'regno' => $group->regno,
                    'guest' => trim((string) optional($rows->first())->Guest),
                    'rows' => $rows->count(),
                    'Person' => $rows->sum('Person'),
                    default => $rows->sum($field),
                };
            }, SORT_REGULAR, $direction === 'desc')
            ->values();
    }

    private function sortValue(object $row, string $field): mixed
    {
        if (in_array($field, ['Tanggal', 'TglIn', 'TglOut'], true)) {
            try {
                return empty($row->{$field}) ? '' : Carbon::parse($row->{$field})->format('YmdHis');
            } catch (\Throwable $exception) {
                return trim((string) ($row->{$field} ?? ''));
            }
        }

        return $row->{$field} ?? '';
    }

    private function normalizeSort(string $sort, array $allowed, string $default): string
    {
        return array_key_exists($sort, $allowed) ? $sort : $default;
    }

    private function normalizeSortDirection(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'desc' : 'asc';
    }

    private function summarySortFields(): array
    {
        return [
            'regno' => 'regno',
            'guest' => 'guest',
            'rows' => 'rows',
            'pax' => 'Person',
            'room' => 'Room1',
            'rest' => 'Cafe1',
            'other' => 'Other1',
            'vila' => 'Vila',
            'dpp' => 'Dpp',
            'service' => 'Service',
            'tax' => 'Tax',
            'total' => 'Total',
        ];
    }

    private function detailSortFields(): array
    {
        return [
            'invoice' => 'Invoice2',
            'room_no' => 'Kode',
            'guest' => 'Guest',
            'pax' => 'Person',
            'tgl_in' => 'TglIn',
            'tgl_out' => 'TglOut',
            'tanggal' => 'Tanggal',
            'room' => 'Room1',
            'rest' => 'Cafe1',
            'other' => 'Other1',
            'vila' => 'Vila',
            'dpp' => 'Dpp',
            'service' => 'Service',
            'tax' => 'Tax',
            'total' => 'Total',
        ];
    }

    private function resolvePeriod(Request $request): array
    {
        $defaultEnd = $this->defaultEndDate();
        $defaultStart = Carbon::parse($defaultEnd)->startOfMonth()->format('Y-m-d');

        $startDate = $this->normalizeDate((string) $request->query('start_date', $defaultStart), $defaultStart);
        $endDate = $this->normalizeDate((string) $request->query('end_date', $defaultEnd), $defaultEnd);
        $periodWarning = null;

        if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            $periodWarning = 'End date is earlier than start date. The period has been adjusted to the start date.';
            $endDate = $startDate;
        }

        return [$startDate, $endDate, $periodWarning];
    }

    private function defaultEndDate(): string
    {
        $postingPeriod = $this->postingPeriodFromSetup();
        if ($postingPeriod !== null) {
            return $postingPeriod->endOfMonth()->format('Y-m-d');
        }

        if (Schema::hasTable('PB1')) {
            $latestDate = DB::table('PB1')->max('Tgl');
            if (!empty($latestDate)) {
                return Carbon::parse($latestDate)->format('Y-m-d');
            }
        }

        if (Schema::hasTable('night_audit_batches')) {
            $latestAudit = DB::table('night_audit_batches')
                ->orderByDesc('business_date')
                ->orderByDesc('id')
                ->value('business_date');

            if (!empty($latestAudit)) {
                return Carbon::parse($latestAudit)->format('Y-m-d');
            }
        }

        return now()->format('Y-m-d');
    }

    private function postingPeriodFromSetup(): ?Carbon
    {
        if (!Schema::hasTable('SETUP') || !Schema::hasColumn('SETUP', 'StatusPosting')) {
            return null;
        }

        $statusPosting = DB::table('SETUP')
            ->selectRaw("RTRIM(COALESCE(StatusPosting, '')) as StatusPosting")
            ->whereRaw("RTRIM(COALESCE(StatusPosting, '')) <> ''")
            ->orderByRaw("CASE WHEN RTRIM(COALESCE(Kode, '')) = '999' THEN 0 ELSE 1 END")
            ->value('StatusPosting');

        $statusPosting = preg_replace('/\D+/', '', (string) $statusPosting);
        if (strlen($statusPosting) !== 6) {
            return null;
        }

        $year = (int) substr($statusPosting, 0, 4);
        $month = (int) substr($statusPosting, 4, 2);
        if ($year < 1900 || $month < 1 || $month > 12) {
            return null;
        }

        try {
            return Carbon::create($year, $month, 1)->startOfMonth();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function normalizeDate(string $value, string $fallback): string
    {
        $value = trim($value);
        if ($value === '') {
            return $fallback;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return $fallback;
        }
    }

    private function loadPostingStatus(string $startDate): ?object
    {
        if (!Schema::hasTable('SETUP') || !Schema::hasColumn('SETUP', 'StatusPosting')) {
            return null;
        }

        $periodCode = Carbon::parse($startDate)->format('Ym');
        $row = DB::table('SETUP')
            ->selectRaw('RTRIM(COALESCE(StatusPosting, \'\')) as StatusPosting')
            ->whereRaw("RTRIM(COALESCE(StatusPosting, '')) = ?", [$periodCode])
            ->first();

        return (object) [
            'period_code' => $periodCode,
            'is_posted' => $row !== null,
        ];
    }

    private function findAuditBatch(string $endDate): ?object
    {
        if (!Schema::hasTable('night_audit_batches')) {
            return null;
        }

        return DB::table('night_audit_batches')
            ->whereDate('business_date', '<=', $endDate)
            ->orderByDesc('business_date')
            ->orderByDesc('id')
            ->first();
    }

    private function schemaReady(): bool
    {
        return Schema::hasTable('PB1') && empty($this->missingColumns());
    }

    private function missingColumns(): array
    {
        if (!Schema::hasTable('PB1')) {
            return self::REQUIRED_COLUMNS;
        }

        $columns = collect(Schema::getColumnListing('PB1'))
            ->map(fn ($column) => strtoupper((string) $column))
            ->all();

        return array_values(array_diff(self::REQUIRED_COLUMNS, $columns));
    }

    private function printUrl(string $startDate, string $endDate, string $regno, string $search): string
    {
        return '/reception-customer-recaptulation/print?' . http_build_query(array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'regno' => $regno !== '' ? $regno : null,
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
}
