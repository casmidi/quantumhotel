<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingReportController extends Controller
{
    public function index(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $month = $this->resolveMonth($request, $dateFrom);
        $resNo = strtoupper(trim((string) $request->query('res_no', '')));
        $search = strtoupper(trim((string) $request->query('search', '')));
        $viewMode = $this->resolveViewMode($request);

        if (in_array($viewMode, ['calendar', 'availability', 'reservation'], true)) {
            [$dateFrom, $dateTo] = $this->dateRangeForMonth($month);
        }

        [$rows, $summaryRows, $calendar, $availabilityChart] = $this->buildReportPayload(
            $viewMode,
            $dateFrom,
            $dateTo,
            $month,
            $resNo,
            $search
        );
        $summary = $this->buildSummary($rows, $dateFrom, $dateTo);

        return $this->respond($request, 'reports.booking.index', [
            'profile' => $this->loadHotelProfile(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'month' => $month,
            'resNo' => $resNo,
            'search' => $search,
            'viewMode' => $viewMode,
            'rows' => $rows,
            'summaryRows' => $summaryRows,
            'calendar' => $calendar,
            'availabilityChart' => $availabilityChart,
            'summary' => $summary,
            'printUrl' => $this->printUrl($dateFrom, $dateTo, $month, $resNo, $search, $viewMode),
        ], [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'month' => $month,
            'view_mode' => $viewMode,
            'summary' => $summary,
            'rows' => $rows,
            'summary_rows' => $summaryRows,
            'calendar' => $calendar,
            'availability_chart' => $availabilityChart,
        ]);
    }

    public function print(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $month = $this->resolveMonth($request, $dateFrom);
        $resNo = strtoupper(trim((string) $request->query('res_no', '')));
        $search = strtoupper(trim((string) $request->query('search', '')));
        $viewMode = $this->resolveViewMode($request);

        if (in_array($viewMode, ['calendar', 'availability', 'reservation'], true)) {
            [$dateFrom, $dateTo] = $this->dateRangeForMonth($month);
        }

        [$rows, $summaryRows, $calendar, $availabilityChart] = $this->buildReportPayload(
            $viewMode,
            $dateFrom,
            $dateTo,
            $month,
            $resNo,
            $search
        );
        $summary = $this->buildSummary($rows, $dateFrom, $dateTo);

        return $this->respond($request, 'reports.booking.print', [
            'profile' => $this->loadHotelProfile(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'month' => $month,
            'resNo' => $resNo,
            'search' => $search,
            'viewMode' => $viewMode,
            'rows' => $rows,
            'summaryRows' => $summaryRows,
            'calendar' => $calendar,
            'availabilityChart' => $availabilityChart,
            'summary' => $summary,
        ], [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'month' => $month,
            'view_mode' => $viewMode,
            'summary' => $summary,
            'rows' => $rows,
            'summary_rows' => $summaryRows,
            'calendar' => $calendar,
            'availability_chart' => $availabilityChart,
        ]);
    }

    private function loadBookingRows(string $dateFrom, string $dateTo, string $resNo = '', string $search = ''): Collection
    {
        return $this->loadBookingRowsForPeriod($dateFrom, $dateTo, $resNo, $search, false);
    }

    private function buildReportPayload(string $viewMode, string $dateFrom, string $dateTo, string $month, string $resNo, string $search): array
    {
        $rows = collect();
        $summaryRows = collect();
        $calendar = $this->emptyMonthlyCalendar($month);
        $availabilityChart = $this->emptyAvailabilityChart($month);

        if ($viewMode === 'summary') {
            $rows = $this->loadBookingRows($dateFrom, $dateTo, $resNo, $search);
            $summaryRows = $this->buildBookingSummaryRows($rows);
        } elseif ($viewMode === 'detail') {
            $rows = $this->loadBookingRows($dateFrom, $dateTo, $resNo, $search);
        } elseif ($viewMode === 'calendar') {
            $rows = $this->loadCalendarBookingRows($month, $resNo, $search);
            $calendar = $this->buildMonthlyCalendar($rows, $month);
        } else {
            $rows = $this->loadCalendarBookingRows($month, $resNo, $search);
            $availabilityChart = $this->buildAvailabilityChart($rows, $month);
        }

        return [$rows, $summaryRows, $calendar, $availabilityChart];
    }

    private function loadCalendarBookingRows(string $month, string $resNo = '', string $search = ''): Collection
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();

        return $this->loadBookingRowsForPeriod(
            $start->format('Y-m-d'),
            $start->copy()->endOfMonth()->format('Y-m-d'),
            $resNo,
            $search,
            true
        );
    }

    private function loadBookingRowsForPeriod(string $dateFrom, string $dateTo, string $resNo = '', string $search = '', bool $overlap = false): Collection
    {
        $profile = $this->loadHotelProfile();
        $tanggal = 'Bogor,  ' . Carbon::now()->format('d F Y');
        $userIn = '(' . strtoupper(trim((string) session('user', 'SYSTEM'))) . ')';

        $query = DB::table('Book')
            ->join('Book2', 'Book.ResNo', '=', 'Book2.ResNo')
            ->selectRaw("
                ? as AlamatPt,
                ? as TelponPt,
                ? as Tanggal,
                RTRIM(Book2.ResNo) as ResNo,
                Book2.TglIn,
                Book2.TglOut as TglKeluar,
                Book2.JamIn,
                RTRIM(Book2.Kode) as Kode,
                RTRIM(Book2.Kelas) as Kelas,
                RTRIM(Book2.Telphone) as Telphone,
                RTRIM(Book2.Payment) as Payment,
                RTRIM(Book2.OriginalGuest) as Guest,
                RTRIM(Book2.Alamat) as Alamat,
                RTRIM(Book2.Usaha) as Usaha,
                RTRIM(Book2.Remark) as Remark,
                Book2.Rate,
                RTRIM(Book2.BookingGuest) as BookingGuest,
                RTRIM(Book2.Resno2) as Resno2,
                ? as UserIn
            ", [
                $profile['address'] ?? '',
                $profile['phone'] ?? '',
                $tanggal,
                $userIn,
            ])
            ->whereRaw('ISNULL(Book2.Batal, 0) = 0');

        if ($overlap) {
            $query
                ->whereDate('Book2.TglIn', '<=', $dateTo)
                ->whereDate('Book2.TglOut', '>=', $dateFrom);
        } else {
            $query
                ->whereDate('Book2.TglIn', '>=', $dateFrom)
                ->whereDate('Book2.TglIn', '<=', $dateTo);
        }

        if ($resNo !== '') {
            $query->whereRaw('RTRIM(Book.ResNo) = ?', [$resNo]);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($scope) use ($like) {
                $scope->whereRaw('UPPER(RTRIM(Book2.OriginalGuest)) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(RTRIM(Book2.BookingGuest)) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(RTRIM(Book2.Kode)) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(RTRIM(Book2.ResNo)) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(RTRIM(Book2.Usaha)) LIKE ?', [$like]);
            });
        }

        return $query
            ->orderBy('Book2.TglIn')
            ->orderByRaw('RTRIM(Book2.ResNo)')
            ->orderByRaw('RTRIM(Book2.Kode)')
            ->get()
            ->map(fn ($row) => $this->hydrateRow($row))
            ->values();
    }

    private function hydrateRow(object $row): object
    {
        foreach (['AlamatPt', 'TelponPt', 'Tanggal', 'ResNo', 'Kode', 'Kelas', 'Telphone', 'Payment', 'Guest', 'Alamat', 'Usaha', 'Remark', 'BookingGuest', 'Resno2', 'UserIn'] as $field) {
            $row->{$field} = trim((string) ($row->{$field} ?? ''));
        }

        $row->UserFO = '(' . ($row->Guest ?: $row->BookingGuest) . ')';
        $row->Rate = (float) ($row->Rate ?? 0);
        $row->TglInKey = $this->dateKey($row->TglIn ?? null);
        $row->TglOutKey = $this->dateKey($row->TglKeluar ?? null);
        $row->TglInDisplay = $this->formatDate($row->TglIn ?? null);
        $row->TglKeluarDisplay = $this->formatDate($row->TglKeluar ?? null);
        $row->JamInDisplay = $this->formatTime($row->JamIn ?? null);

        return $row;
    }

    private function buildMonthlyCalendar(Collection $rows, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->format('Y-m-d');
            $bookings = $rows->filter(fn ($row) => $row->TglInKey <= $dateKey && $row->TglOutKey >= $dateKey);
            $groups = $bookings
                ->groupBy('ResNo')
                ->map(fn (Collection $groupRows, string $resNo) => [
                    'res_no' => $resNo,
                    'guest' => $groupRows->first()->Guest,
                    'rooms' => $groupRows->pluck('Kode')->filter()->unique()->values()->all(),
                ])
                ->values()
                ->all();

            $days[] = [
                'date' => $dateKey,
                'day' => $cursor->format('d'),
                'weekday' => $cursor->format('D'),
                'has_booking' => $bookings->isNotEmpty(),
                'room_count' => $bookings->pluck('Kode')->filter()->unique()->count(),
                'booking_count' => $bookings->pluck('ResNo')->filter()->unique()->count(),
                'groups' => $groups,
            ];

            $cursor->addDay();
        }

        return [
            'month_label' => $start->format('F Y'),
            'days' => $days,
        ];
    }

    private function emptyMonthlyCalendar(string $month): array
    {
        return [
            'month_label' => Carbon::parse($month . '-01')->format('F Y'),
            'days' => [],
        ];
    }

    private function emptyAvailabilityChart(string $month): array
    {
        return [
            'month_label' => Carbon::parse($month . '-01')->format('F Y'),
            'day_count' => 0,
            'total_rooms' => 0,
            'days' => [],
            'groups' => [],
        ];
    }

    private function buildAvailabilityChart(Collection $rows, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $startKey = $start->format('Y-m-d');
        $endKey = $end->format('Y-m-d');
        $days = [];
        $dayIndex = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->format('Y-m-d');
            $dayIndex[$dateKey] = count($days) + 1;
            $days[] = [
                'date' => $dateKey,
                'day' => $cursor->format('d'),
                'weekday' => $cursor->format('D'),
                'month' => $cursor->format('M'),
                'is_today' => $cursor->isSameDay(Carbon::today()),
                'booked_count' => 0,
                'available_count' => 0,
                'availability_percent' => 0,
            ];

            $cursor->addDay();
        }

        $roomMap = [];
        $this->loadAvailabilityRooms()->each(function (array $room) use (&$roomMap) {
            $roomMap[$room['code']] = $room;
        });

        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row->Kode ?? '')));

            if ($code === '' || isset($roomMap[$code])) {
                continue;
            }

            $roomMap[$code] = [
                'code' => $code,
                'class' => trim((string) ($row->Kelas ?? '')) ?: 'Unassigned',
                'rate' => (float) ($row->Rate ?? 0),
            ];
        }

        uasort($roomMap, function (array $left, array $right) {
            $classCompare = strnatcasecmp($left['class'], $right['class']);

            return $classCompare !== 0 ? $classCompare : strnatcasecmp($left['code'], $right['code']);
        });

        $bookingRows = $rows
            ->filter(fn ($row) => trim((string) ($row->Kode ?? '')) !== '' && ($row->TglInKey ?? '') !== '')
            ->sortBy(fn ($row) => ($row->TglInKey ?? '') . '|' . trim((string) ($row->Kode ?? '')))
            ->values();

        $tapesByRoom = [];

        foreach ($bookingRows as $row) {
            $code = strtoupper(trim((string) ($row->Kode ?? '')));
            $checkInKey = (string) ($row->TglInKey ?? '');
            $checkOutKey = (string) (($row->TglOutKey ?? '') ?: $checkInKey);

            if ($code === '' || $checkInKey === '') {
                continue;
            }

            $clampedStart = $checkInKey < $startKey ? $startKey : $checkInKey;
            $clampedEnd = $checkOutKey > $endKey ? $endKey : $checkOutKey;

            if ($clampedStart > $endKey || $clampedEnd < $startKey || $clampedStart > $clampedEnd) {
                continue;
            }

            $startColumn = $dayIndex[$clampedStart] ?? 1;
            $endColumn = $dayIndex[$clampedEnd] ?? count($days);
            $guest = trim((string) (($row->Guest ?? '') ?: ($row->BookingGuest ?? '') ?: ($row->ResNo ?? 'Booked')));
            $colorIndex = abs((int) crc32(($row->ResNo ?? '') . '|' . $code)) % 6;

            $tapesByRoom[$code] ??= [];
            $tapesByRoom[$code][] = [
                'res_no' => trim((string) ($row->ResNo ?? '')),
                'guest' => $guest,
                'label' => strlen($guest) > 26 ? substr($guest, 0, 24) . '..' : $guest,
                'room' => $code,
                'start_column' => $startColumn,
                'end_column' => $endColumn,
                'span' => max(1, $endColumn - $startColumn + 1),
                'lane' => 1,
                'class' => 'tape-' . $colorIndex,
                'date_range' => $this->formatDate($checkInKey) . ' - ' . $this->formatDate($checkOutKey),
            ];
        }

        $laneCounts = [];

        foreach ($tapesByRoom as $code => $tapes) {
            usort($tapes, fn (array $left, array $right) => [$left['start_column'], $left['end_column']] <=> [$right['start_column'], $right['end_column']]);

            $laneEnds = [];

            foreach ($tapes as $index => $tape) {
                $lane = 0;

                while (isset($laneEnds[$lane]) && $laneEnds[$lane] >= $tape['start_column']) {
                    $lane++;
                }

                $laneEnds[$lane] = $tape['end_column'];
                $tapes[$index]['lane'] = $lane + 1;
            }

            $tapesByRoom[$code] = $tapes;
            $laneCounts[$code] = max(1, count($laneEnds));
        }

        $totalRooms = count($roomMap);
        $bookedRoomCodesByDate = [];

        foreach ($bookingRows as $row) {
            $code = strtoupper(trim((string) ($row->Kode ?? '')));
            $checkInKey = (string) ($row->TglInKey ?? '');
            $checkOutKey = (string) (($row->TglOutKey ?? '') ?: $checkInKey);

            if ($code === '' || $checkInKey === '') {
                continue;
            }

            $clampedStart = $checkInKey < $startKey ? $startKey : $checkInKey;
            $clampedEnd = $checkOutKey > $endKey ? $endKey : $checkOutKey;

            if ($clampedStart > $endKey || $clampedEnd < $startKey || $clampedStart > $clampedEnd) {
                continue;
            }

            $cursor = Carbon::parse($clampedStart);
            $bookingEnd = Carbon::parse($clampedEnd);

            while ($cursor->lte($bookingEnd)) {
                $dateKey = $cursor->format('Y-m-d');
                $bookedRoomCodesByDate[$dateKey] ??= [];
                $bookedRoomCodesByDate[$dateKey][$code] = true;
                $cursor->addDay();
            }
        }

        foreach ($days as $index => $day) {
            $dateKey = $day['date'];
            $bookedCount = isset($bookedRoomCodesByDate[$dateKey]) ? count($bookedRoomCodesByDate[$dateKey]) : 0;
            $availableCount = max(0, $totalRooms - $bookedCount);

            $days[$index]['booked_count'] = $bookedCount;
            $days[$index]['available_count'] = $availableCount;
            $days[$index]['availability_percent'] = $totalRooms > 0 ? round(($availableCount / $totalRooms) * 100) : 0;
        }

        $groups = collect($roomMap)
            ->groupBy('class')
            ->map(function (Collection $rooms, string $class) use ($tapesByRoom, $laneCounts) {
                $items = $rooms
                    ->map(function (array $room) use ($tapesByRoom, $laneCounts) {
                        $laneCount = $laneCounts[$room['code']] ?? 1;

                        return [
                            'code' => $room['code'],
                            'class' => $room['class'],
                            'rate' => $room['rate'],
                            'lane_count' => $laneCount,
                            'row_height' => 44 + (($laneCount - 1) * 26),
                            'tapes' => $tapesByRoom[$room['code']] ?? [],
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'class' => $class ?: 'Unassigned',
                    'room_count' => count($items),
                    'rooms' => $items,
                ];
            })
            ->values()
            ->all();

        return [
            'month_label' => $start->format('F Y'),
            'day_count' => count($days),
            'total_rooms' => $totalRooms,
            'days' => $days,
            'groups' => $groups,
        ];
    }

    private function loadAvailabilityRooms(): Collection
    {
        return DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Kelas, Rate1')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->orderByRaw('RTRIM(Nama)')
            ->orderByRaw('RTRIM(Kode)')
            ->get()
            ->map(fn ($room) => [
                'code' => strtoupper(trim((string) ($room->Kode ?? ''))),
                'class' => trim((string) ($room->Kelas ?? '')) ?: 'Unassigned',
                'rate' => (float) ($room->Rate1 ?? 0),
            ])
            ->filter(fn (array $room) => $room['code'] !== '')
            ->values();
    }

    private function buildBookingSummaryRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy('ResNo')
            ->map(function (Collection $groupRows, string $resNo) {
                $first = $groupRows->first();
                $checkInKey = $groupRows->pluck('TglInKey')->filter()->sort()->first() ?: '';
                $checkOutKey = $groupRows->pluck('TglOutKey')->filter()->sort()->last() ?: '';
                $rooms = $groupRows->pluck('Kode')->filter()->unique()->sort()->values();
                $classes = $groupRows->pluck('Kelas')->filter()->unique()->values();
                $remarks = $groupRows->pluck('Remark')->filter()->unique()->values();

                return (object) [
                    'ResNo' => $resNo,
                    'TglInKey' => $checkInKey,
                    'TglInDisplay' => $checkInKey !== '' ? $this->formatDate($checkInKey) : '',
                    'TglKeluarDisplay' => $checkOutKey !== '' ? $this->formatDate($checkOutKey) : '',
                    'Guest' => $first->Guest ?: $first->BookingGuest,
                    'Telphone' => $first->Telphone,
                    'RoomCount' => $rooms->count(),
                    'RoomList' => $rooms->implode(', '),
                    'ClassList' => $classes->implode(', '),
                    'Remark' => $remarks->implode(' | '),
                    'Rate' => round($groupRows->sum('Rate'), 2),
                ];
            })
            ->sortBy([
                ['TglInKey', 'asc'],
                ['ResNo', 'asc'],
            ])
            ->values();
    }

    private function buildSummary(Collection $rows, string $dateFrom, string $dateTo): array
    {
        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'reservations' => $rows->pluck('ResNo')->unique()->count(),
            'rooms' => $rows->pluck('Kode')->unique()->count(),
            'guests' => $rows->count(),
            'rate_total' => round($rows->sum('Rate'), 2),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $this->normalizeDate((string) $request->query('date_from', now()->startOfMonth()->format('Y-m-d')));
        $to = $this->normalizeDate((string) $request->query('date_to', now()->endOfMonth()->format('Y-m-d')));

        if (Carbon::parse($to)->lt(Carbon::parse($from))) {
            $to = $from;
        }

        return [$from, $to];
    }

    private function resolveMonth(Request $request, string $dateFrom): string
    {
        $month = trim((string) $request->query('month', ''));

        try {
            return Carbon::parse($month !== '' ? $month . '-01' : $dateFrom)->format('Y-m');
        } catch (\Throwable $exception) {
            return Carbon::parse($dateFrom)->format('Y-m');
        }
    }

    private function dateRangeForMonth(string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();

        return [
            $start->format('Y-m-d'),
            $start->copy()->endOfMonth()->format('Y-m-d'),
        ];
    }

    private function resolveViewMode(Request $request): string
    {
        $viewMode = strtolower(trim((string) $request->query('view', 'summary')));

        return in_array($viewMode, ['summary', 'calendar', 'detail', 'availability', 'reservation'], true) ? $viewMode : 'summary';
    }

    private function printUrl(string $dateFrom, string $dateTo, string $month, string $resNo, string $search, string $viewMode): string
    {
        return '/booking-report/print?' . http_build_query(array_filter([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'month' => $month,
            'res_no' => $resNo !== '' ? $resNo : null,
            'search' => $search !== '' ? $search : null,
            'view' => $viewMode,
        ], fn ($value) => $value !== null && $value !== ''));
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
        $raw = trim($value);
        $match = [];

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $raw, $match)) {
            $year = (int) $match[1];
            $month = (int) $match[2];
            $day = (int) $match[3];

            if (checkdate($month, $day, $year)) {
                return Carbon::create($year, $month, $day)->format('Y-m-d');
            }
        }

        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $raw, $match)) {
            $day = (int) $match[1];
            $month = (int) $match[2];
            $year = (int) $match[3];

            if (checkdate($month, $day, $year)) {
                return Carbon::create($year, $month, $day)->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return now()->format('Y-m-d');
        }
    }

    private function dateKey($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private function formatDate($value): string
    {
        $key = $this->dateKey($value);
        return $key !== '' ? Carbon::parse($key)->format('d-m-Y') : '';
    }

    private function formatTime($value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable $exception) {
            return $raw;
        }
    }
}
