<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $calendarStart = Carbon::today();
        $calendarEnd = $calendarStart->copy()->addDays(29);
        $selectedDate = $this->resolveSelectedDate((string) $request->query('date', ''), $calendarStart, $calendarEnd);
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $bookingCollection = $this->bookingQuery($search)->get();
        $bookings = $this->paginateCollection($bookingCollection, $perPage, $request);
        $rooms = $this->loadRoomOptions();
        $classes = $this->loadClassOptions();
        $calendarDays = $this->buildCalendarDays($calendarStart, $calendarEnd);

        $summary = [
            'total' => $bookingCollection->count(),
            'active' => $bookingCollection->where('Status', 'Active')->count(),
            'cancelled' => $bookingCollection->where('Status', 'Cancel')->count(),
            'arrival_today' => $bookingCollection
                ->filter(fn ($row) => $row->TglIn && Carbon::parse($row->TglIn)->isSameDay(Carbon::today()))
                ->count(),
        ];

        $viewData = [
            'bookings' => $bookings,
            'bookingRows' => $bookingCollection,
            'rooms' => $rooms,
            'classes' => $classes,
            'summary' => $summary,
            'calendarDays' => $calendarDays,
            'calendarRangeLabel' => $calendarStart->format('d M') . ' - ' . $calendarEnd->format('d M Y'),
            'selectedDate' => $selectedDate,
            'search' => $search,
            'perPage' => $perPage,
            'nextResNo' => $this->generateNextResNo(),
            'defaultCheckIn' => old('TglIn', $selectedDate),
            'defaultCheckOut' => old('TglOut', Carbon::parse($selectedDate)->addDay()->format('Y-m-d')),
            'paymentOptions' => ['PA', 'CASH', 'CARD', 'OTA', 'COMPANY', 'TRAVEL', 'COMPLIMENT'],
            'typeOptions' => ['Walk In', 'Personal', 'OTA', 'Corporate', 'Group'],
            'data2Occupancies' => $this->loadData2OccupancyRows(),
            'bookingOccupancies' => $this->loadBookingOccupancyRows($calendarStart, $calendarEnd->copy()->addDays(60)),
            'restrictedRoomCodes' => $this->restrictedRoomCodes(),
        ];

        return $this->respond($request, 'booking.index', $viewData, [
            'bookings' => $this->paginatorPayload($bookings),
            'rooms' => $rooms,
            'classes' => $classes,
            'summary' => $summary,
            'calendar_days' => $calendarDays,
            'calendar_range' => $viewData['calendarRangeLabel'],
            'selected_date' => $selectedDate,
            'next_res_no' => $viewData['nextResNo'],
            'booking_occupancies' => $viewData['bookingOccupancies'],
        ]);
    }

    public function store(Request $request)
    {
        if ($request->input('booking_mode') === 'blocking') {
            return $this->saveBlockingBooking($request);
        }

        return $this->saveBooking($request);
    }

    public function update(Request $request, string $resno2)
    {
        return $this->saveBooking($request, trim((string) $resno2));
    }

    public function destroy(Request $request, string $resno2)
    {
        $booking = $this->findBookingByDetailKey($resno2);

        if (!$booking) {
            return $this->respondError($request, 'Booking was not found.', 404, [], '/booking', false);
        }

        DB::transaction(function () use ($booking) {
            DB::table('Book2')
                ->whereRaw('RTRIM(Resno2) = ?', [$booking->Resno2])
                ->update(['Batal' => 1]);

            $activeDetails = DB::table('Book2')
                ->whereRaw('RTRIM(ResNo) = ?', [$booking->ResNo])
                ->whereRaw('ISNULL(Batal, 0) = 0')
                ->exists();

            if (!$activeDetails) {
                DB::table('Book')
                    ->whereRaw('RTRIM(ResNo) = ?', [$booking->ResNo])
                    ->update(['Batal' => 1]);
            }
        });

        return $this->respondAfterMutation($request, '/booking', 'Booking cancelled successfully.', [
            'res_no' => $booking->ResNo,
            'resno2' => $booking->Resno2,
        ]);
    }

    private function saveBooking(Request $request, ?string $currentResno2 = null)
    {
        $validated = $request->validate([
            'ResNo' => ['nullable', 'string', 'max:30'],
            'TglIn' => ['required', 'date'],
            'TglOut' => ['required', 'date', 'after_or_equal:TglIn'],
            'JamIn' => ['nullable', 'date_format:H:i'],
            'JamOut' => ['nullable', 'date_format:H:i'],
            'Kode' => ['required', 'string', 'max:20'],
            'Kelas' => ['nullable', 'string', 'max:60'],
            'OriginalGuest' => ['required', 'string', 'max:80'],
            'BookingGuest' => ['nullable', 'string', 'max:80'],
            'KTP' => ['nullable', 'string', 'max:50'],
            'Alamat' => ['nullable', 'string', 'max:200'],
            'Telphone' => ['nullable', 'string', 'max:50'],
            'Person' => ['nullable', 'integer', 'min:0', 'max:999'],
            'Usaha' => ['nullable', 'string', 'max:60'],
            'Payment' => ['nullable', 'string', 'max:30'],
            'AcceptBy' => ['nullable', 'string', 'max:60'],
            'Remark' => ['nullable', 'string', 'max:120'],
            'Rate' => ['nullable', 'string', 'max:30'],
            'Deposit' => ['nullable', 'string', 'max:30'],
            'Tipe' => ['nullable', 'string', 'max:30'],
        ]);

        $roomCode = strtoupper(trim((string) $validated['Kode']));
        if (!$this->roomExists($roomCode)) {
            return $this->respondError($request, 'Selected room was not found.');
        }

        $existing = $currentResno2 ? $this->findBookingByDetailKey($currentResno2) : null;
        if ($currentResno2 && !$existing) {
            return $this->respondError($request, 'Booking to update was not found.', 404, [], '/booking', false);
        }

        $resNo = $existing
            ? trim((string) $existing->ResNo)
            : strtoupper(trim((string) ($validated['ResNo'] ?? '')));
        $resNo = $resNo !== '' ? $resNo : $this->generateNextResNo();
        $checkIn = Carbon::parse($validated['TglIn'])->format('Y-m-d');
        $checkOut = Carbon::parse($validated['TglOut'])->format('Y-m-d');
        $jamIn = $this->normalizeTimeForSql($validated['JamIn'] ?? '14:00');
        $jamOut = $this->normalizeTimeForSql($validated['JamOut'] ?? '13:00');
        $rate = $this->normalizeMoney($validated['Rate'] ?? 0);
        $deposit = $this->normalizeMoney($validated['Deposit'] ?? 0);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));
        $room = $this->findRoom($roomCode);
        $class = trim((string) ($validated['Kelas'] ?? ''));
        $class = $class !== '' ? $class : trim((string) ($room->Nama ?? ''));
        $resno2 = $existing ? trim((string) $existing->Resno2) : $this->buildResno2($resNo, $roomCode);

        DB::transaction(function () use ($validated, $existing, $resNo, $resno2, $roomCode, $class, $checkIn, $checkOut, $jamIn, $jamOut, $rate, $deposit, $username) {
            $headerPayload = [
                'KodeCust' => '',
                'Deposit' => $deposit,
                'Tipe' => trim((string) ($validated['Tipe'] ?? '')),
                'Discount' => 0,
                'UserName' => $username,
                'Batal' => 0,
                'strTgl' => $this->buildStrTgl($checkIn, $jamIn),
                'strTglOut' => $this->buildStrTgl($checkOut, $jamOut),
                'TglKeluar' => $checkOut,
            ];

            DB::table('Book')->updateOrInsert(
                ['ResNo' => $resNo],
                $headerPayload
            );

            $detailPayload = [
                'ResNo' => $resNo,
                'Pst' => ' ',
                'TglIn' => $checkIn,
                'JamIn' => $jamIn,
                'TglOut' => $checkOut,
                'JamOut' => $jamOut,
                'OriginalGuest' => strtoupper(trim((string) $validated['OriginalGuest'])),
                'BookingGuest' => strtoupper(trim((string) ($validated['BookingGuest'] ?? ''))),
                'KTP' => trim((string) ($validated['KTP'] ?? '')),
                'Alamat' => trim((string) ($validated['Alamat'] ?? '')),
                'Telphone' => trim((string) ($validated['Telphone'] ?? '')),
                'Person' => (int) ($validated['Person'] ?? 0),
                'Usaha' => strtoupper(trim((string) ($validated['Usaha'] ?? ''))),
                'Deposit' => $deposit,
                'Batal' => 0,
                'Payment' => strtoupper(trim((string) ($validated['Payment'] ?? 'PA'))),
                'AcceptBy' => strtoupper(trim((string) ($validated['AcceptBy'] ?? $username))),
                'Remark' => trim((string) ($validated['Remark'] ?? '')),
                'PostPhone' => 0,
                'Kelas' => $class,
                'Kode' => $roomCode,
                'Rate' => $rate,
                'Resno2' => $resno2,
            ];

            if ($existing) {
                DB::table('Book2')
                    ->whereRaw('RTRIM(Resno2) = ?', [trim((string) $existing->Resno2)])
                    ->update($detailPayload);
                return;
            }

            DB::table('Book2')->insert($detailPayload);
        });

        return $this->respondAfterMutation(
            $request,
            '/booking',
            $existing ? 'Booking updated successfully.' : 'Booking created successfully.',
            $this->findBookingByDetailKey($resno2, true),
            $existing ? 200 : 201
        );
    }

    private function saveBlockingBooking(Request $request)
    {
        $validated = $request->validate([
            'ResNo' => ['nullable', 'string', 'max:30'],
            'TglIn' => ['required', 'date'],
            'TglOut' => ['required', 'date', 'after:TglIn'],
            'JamIn' => ['nullable', 'date_format:H:i'],
            'JamOut' => ['nullable', 'date_format:H:i'],
            'BlockRoomQty' => ['nullable', 'integer', 'min:1', 'max:100'],
            'BlockRooms' => ['required', 'array', 'min:1', 'max:100'],
            'BlockRooms.*' => ['required', 'string', 'max:20'],
            'Kelas' => ['nullable', 'string', 'max:60'],
            'OriginalGuest' => ['required', 'string', 'max:80'],
            'BookingGuest' => ['nullable', 'string', 'max:80'],
            'KTP' => ['nullable', 'string', 'max:50'],
            'Alamat' => ['nullable', 'string', 'max:200'],
            'Telphone' => ['nullable', 'string', 'max:50'],
            'Person' => ['nullable', 'integer', 'min:0', 'max:999'],
            'Usaha' => ['nullable', 'string', 'max:60'],
            'Payment' => ['nullable', 'string', 'max:30'],
            'AcceptBy' => ['nullable', 'string', 'max:60'],
            'Remark' => ['nullable', 'string', 'max:120'],
            'Rate' => ['nullable', 'string', 'max:30'],
            'Deposit' => ['nullable', 'string', 'max:30'],
            'Tipe' => ['nullable', 'string', 'max:30'],
        ]);

        $checkIn = Carbon::parse($validated['TglIn'])->format('Y-m-d');
        $checkOut = Carbon::parse($validated['TglOut'])->format('Y-m-d');
        $jamIn = $this->normalizeTimeForSql($validated['JamIn'] ?? '14:00');
        $jamOut = $this->normalizeTimeForSql($validated['JamOut'] ?? '13:00');
        $class = trim((string) ($validated['Kelas'] ?? ''));
        $selectedRoomCodes = collect($validated['BlockRooms'] ?? [])
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->unique()
            ->values();
        $availableRooms = $this->loadAvailableRoomsForBlocking($checkIn, $checkOut, $class);
        $rooms = $availableRooms
            ->filter(fn ($room) => $selectedRoomCodes->contains(strtoupper(trim((string) $room->Kode))))
            ->values();

        if ($rooms->count() !== $selectedRoomCodes->count()) {
            $unavailableRooms = $selectedRoomCodes
                ->diff($rooms->pluck('Kode')->map(fn ($code) => strtoupper(trim((string) $code))))
                ->values()
                ->implode(', ');

            return $this->respondError(
                $request,
                'Some selected rooms are no longer available for this date range: ' . $unavailableRooms . '.'
            );
        }

        $resNo = strtoupper(trim((string) ($validated['ResNo'] ?? '')));
        $resNo = $resNo !== '' ? $resNo : $this->generateNextResNo();
        $rate = $this->normalizeMoney($validated['Rate'] ?? 0);
        $deposit = $this->normalizeMoney($validated['Deposit'] ?? 0);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));
        $createdResno2 = [];

        DB::transaction(function () use ($validated, $rooms, $resNo, $checkIn, $checkOut, $jamIn, $jamOut, $rate, $deposit, $username, &$createdResno2) {
            DB::table('Book')->updateOrInsert(
                ['ResNo' => $resNo],
                [
                    'KodeCust' => '',
                    'Deposit' => $deposit,
                    'Tipe' => trim((string) ($validated['Tipe'] ?? 'Group')),
                    'Discount' => 0,
                    'UserName' => $username,
                    'Batal' => 0,
                    'strTgl' => $this->buildStrTgl($checkIn, $jamIn),
                    'strTglOut' => $this->buildStrTgl($checkOut, $jamOut),
                    'TglKeluar' => $checkOut,
                ]
            );

            foreach ($rooms as $room) {
                $roomCode = trim((string) $room->Kode);
                $resno2 = $this->buildResno2($resNo, $roomCode);
                $createdResno2[] = $resno2;

                DB::table('Book2')->insert([
                    'ResNo' => $resNo,
                    'Pst' => ' ',
                    'TglIn' => $checkIn,
                    'JamIn' => $jamIn,
                    'TglOut' => $checkOut,
                    'JamOut' => $jamOut,
                    'OriginalGuest' => strtoupper(trim((string) $validated['OriginalGuest'])),
                    'BookingGuest' => strtoupper(trim((string) ($validated['BookingGuest'] ?? ''))),
                    'KTP' => trim((string) ($validated['KTP'] ?? '')),
                    'Alamat' => trim((string) ($validated['Alamat'] ?? '')),
                    'Telphone' => trim((string) ($validated['Telphone'] ?? '')),
                    'Person' => (int) ($validated['Person'] ?? 0),
                    'Usaha' => strtoupper(trim((string) ($validated['Usaha'] ?? ''))),
                    'Deposit' => $deposit,
                    'Batal' => 0,
                    'Payment' => strtoupper(trim((string) ($validated['Payment'] ?? 'PA'))),
                    'AcceptBy' => strtoupper(trim((string) ($validated['AcceptBy'] ?? $username))),
                    'Remark' => trim((string) ($validated['Remark'] ?? 'BLOCKING BOOKING')),
                    'PostPhone' => 0,
                    'Kelas' => trim((string) ($room->Nama ?? '')),
                    'Kode' => $roomCode,
                    'Rate' => $rate > 0 ? $rate : (float) ($room->Rate1 ?? 0),
                    'Resno2' => $resno2,
                ]);
            }
        });

        return $this->respondAfterMutation(
            $request,
            '/booking',
            'Blocking booking created for ' . count($createdResno2) . ' rooms.',
            [
                'res_no' => $resNo,
                'rooms' => $rooms->pluck('Kode')->map(fn ($code) => trim((string) $code))->values(),
                'resno2' => $createdResno2,
            ],
            201
        );
    }

    private function bookingQuery(string $search, bool $includePast = false)
    {
        $query = DB::table('Book2')
            ->leftJoin('Book', 'Book.ResNo', '=', 'Book2.ResNo')
            ->selectRaw("
                RTRIM(Book2.ResNo) as ResNo,
                RTRIM(Book2.Resno2) as Resno2,
                Book2.TglIn,
                Book2.TglOut,
                Book2.JamIn,
                Book2.JamOut,
                RTRIM(Book2.Kode) as Kode,
                RTRIM(Book2.Kelas) as Kelas,
                RTRIM(Book2.OriginalGuest) as OriginalGuest,
                RTRIM(Book2.BookingGuest) as BookingGuest,
                RTRIM(Book2.KTP) as KTP,
                RTRIM(Book2.Alamat) as Alamat,
                RTRIM(Book2.Telphone) as Telphone,
                Book2.Person,
                RTRIM(Book2.Usaha) as Usaha,
                RTRIM(Book2.Payment) as Payment,
                RTRIM(Book2.AcceptBy) as AcceptBy,
                RTRIM(Book2.Remark) as Remark,
                Book2.Rate,
                Book2.Deposit,
                RTRIM(Book.Tipe) as Tipe,
                CASE WHEN ISNULL(Book2.Batal, 0) = 0 THEN 'Active' ELSE 'Cancel' END as Status
            ");

        if (!$includePast) {
            $query->whereDate('Book2.TglIn', '>=', Carbon::today()->format('Y-m-d'));
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($nested) use ($like) {
                $nested->whereRaw('RTRIM(Book2.ResNo) LIKE ?', [$like])
                    ->orWhereRaw('RTRIM(Book2.OriginalGuest) LIKE ?', [$like])
                    ->orWhereRaw('RTRIM(Book2.BookingGuest) LIKE ?', [$like])
                    ->orWhereRaw('RTRIM(Book2.Kode) LIKE ?', [$like])
                    ->orWhereRaw('RTRIM(Book2.Kelas) LIKE ?', [$like])
                    ->orWhereRaw('RTRIM(Book2.Telphone) LIKE ?', [$like]);
            });
        }

        return $query->orderByDesc('Book2.TglIn')->orderByDesc('Book2.ResNo');
    }

    private function findBookingByDetailKey(string $resno2, bool $includePast = false)
    {
        return $this->bookingQuery('', $includePast)
            ->whereRaw('RTRIM(Book2.Resno2) = ?', [trim($resno2)])
            ->first();
    }

    private function resolveSelectedDate(string $value, Carbon $calendarStart, Carbon $calendarEnd): string
    {
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value))) {
                $date = Carbon::parse($value);

                if ($date->betweenIncluded($calendarStart, $calendarEnd)) {
                    return $date->format('Y-m-d');
                }
            }
        } catch (\Throwable $exception) {
        }

        return $calendarStart->format('Y-m-d');
    }

    private function buildCalendarDays(Carbon $start, Carbon $end): array
    {
        $bookingRows = $this->bookingQuery('', false)
            ->whereBetween('Book2.TglIn', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->TglIn)->format('Y-m-d'));
        $stayRows = DB::table('Book2')
            ->selectRaw('RTRIM(ResNo) as ResNo, RTRIM(Kode) as Kode, TglIn, TglOut')
            ->whereRaw('ISNULL(Batal, 0) = 0')
            ->whereDate('Book2.TglIn', '<=', $end->format('Y-m-d'))
            ->whereDate('Book2.TglOut', '>=', $start->format('Y-m-d'))
            ->get();
        $stayMap = [];

        foreach ($stayRows as $stay) {
            if (!$stay->TglIn || !$stay->TglOut) {
                continue;
            }

            $stayStart = Carbon::parse($stay->TglIn)->max($start);
            $stayEnd = Carbon::parse($stay->TglOut)->min($end);
            $stayCursor = $stayStart->copy();

            while ($stayCursor->lte($stayEnd)) {
                $dateKey = $stayCursor->format('Y-m-d');
                $stayMap[$dateKey] ??= [];
                $stayMap[$dateKey][] = trim((string) ($stay->Kode ?? ''));
                $stayCursor->addDay();
            }
        }

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->format('Y-m-d');
            $rows = $bookingRows->get($dateKey, collect());
            $stayRooms = collect($stayMap[$dateKey] ?? [])->filter()->unique()->values();
            $hasStay = $stayRooms->isNotEmpty();

            $days[] = [
                'date' => $dateKey,
                'day' => $cursor->format('d'),
                'weekday' => $cursor->format('D'),
                'month' => $cursor->format('M'),
                'is_today' => $cursor->isSameDay(Carbon::today()),
                'is_past' => $cursor->lt(Carbon::today()),
                'total' => $rows->count(),
                'active' => $rows->where('Status', 'Active')->count(),
                'cancelled' => $rows->where('Status', 'Cancel')->count(),
                'rooms' => $rows->pluck('Kode')->filter()->unique()->values()->all(),
                'revenue' => (float) $rows->sum(fn ($row) => (float) ($row->Rate ?? 0)),
                'stay_count' => $stayRooms->count(),
                'stay_rooms' => $stayRooms->all(),
                'stay_start' => $hasStay && $stayRows->contains(fn ($stay) => Carbon::parse($stay->TglIn)->isSameDay($cursor)),
                'stay_end' => $hasStay && $stayRows->contains(fn ($stay) => Carbon::parse($stay->TglOut)->isSameDay($cursor)),
            ];

            $cursor->addDay();
        }

        return $days;
    }

    private function loadRoomOptions(): array
    {
        return DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Fasilitas) as Fasilitas, RTRIM(Status) as Status, RTRIM(StatusB) as StatusB, RTRIM(StatusC) as StatusC, RTRIM(STATUS2) as STATUS2, Rate1')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->orderBy('Kode')
            ->get()
            ->map(fn ($room) => [
                'kode' => trim((string) $room->Kode),
                'kelas' => trim((string) $room->Nama),
                'fasilitas' => trim((string) $room->Fasilitas),
                'status' => trim((string) $room->Status),
                'status_b' => trim((string) $room->StatusB),
                'status_c' => trim((string) $room->StatusC),
                'status_2' => trim((string) $room->STATUS2),
                'rate' => (float) ($room->Rate1 ?? 0),
            ])
            ->values()
            ->all();
    }

    private function loadClassOptions(): array
    {
        return DB::table('KELAS')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1')
            ->orderBy('Kode')
            ->get()
            ->map(fn ($class) => [
                'kode' => trim((string) $class->Kode),
                'nama' => trim((string) $class->Nama),
                'rate' => (float) ($class->Rate1 ?? 0),
            ])
            ->values()
            ->all();
    }

    private function roomExists(string $roomCode): bool
    {
        return DB::table('ROOM')->whereRaw('RTRIM(Kode) = ?', [$roomCode])->exists();
    }

    private function findRoom(string $roomCode)
    {
        return DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->first();
    }

    private function loadAvailableRoomsForBlocking(string $checkIn, string $checkOut, string $class)
    {
        $unavailableRoomCodes = collect($this->unavailableRoomCodes($checkIn, $checkOut))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query = DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1')
            ->whereRaw("RTRIM(Kode) <> '999'");

        if ($class !== '') {
            $query->whereRaw('RTRIM(Nama) = ?', [$class]);
        }

        if ($unavailableRoomCodes) {
            $query->whereNotIn(DB::raw('RTRIM(Kode)'), $unavailableRoomCodes);
        }

        return $query->orderBy('Kode')->get();
    }

    private function unavailableRoomCodes(string $checkIn, string $checkOut): array
    {
        return collect()
            ->merge($this->bookedRoomCodes($checkIn, $checkOut))
            ->merge($this->data2OccupiedRoomCodes($checkIn, $checkOut))
            ->merge($this->restrictedRoomCodes())
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function bookedRoomCodes(string $checkIn, string $checkOut): array
    {
        return DB::table('Book2')
            ->selectRaw('RTRIM(Kode) as Kode')
            ->whereRaw('ISNULL(Batal, 0) = 0')
            ->whereDate('TglIn', '<', $checkOut)
            ->whereDate('TglOut', '>', $checkIn)
            ->pluck('Kode')
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function data2OccupiedRoomCodes(string $checkIn, string $checkOut): array
    {
        $start = Carbon::parse($checkIn)->startOfDay();
        $end = Carbon::parse($checkOut)->startOfDay();

        return collect($this->loadData2OccupancyRows())
            ->filter(function (array $row) use ($start, $end) {
                if (empty($row['kode']) || empty($row['tgl_in'])) {
                    return false;
                }

                $rowStart = Carbon::parse($row['tgl_in'])->startOfDay();
                $rowEnd = $this->latestValidDate([
                    $row['tgl_out'] ?? null,
                    $row['tgl_keluar'] ?? null,
                ]);

                if (!$rowEnd) {
                    return $rowStart->lt($end);
                }

                return $rowStart->lt($end) && $rowEnd->gt($start);
            })
            ->pluck('kode')
            ->values()
            ->all();
    }

    private function loadData2OccupancyRows(): array
    {
        return DB::table('DATA2')
            ->selectRaw('RTRIM(Kode) as Kode, TglIn, TglOut, TglKeluar')
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->get()
            ->map(fn ($row) => [
                'kode' => trim((string) $row->Kode),
                'tgl_in' => $this->dateString($row->TglIn ?? null),
                'tgl_out' => $this->dateString($row->TglOut ?? null),
                'tgl_keluar' => $this->dateString($row->TglKeluar ?? null),
            ])
            ->filter(fn (array $row) => $row['kode'] !== '')
            ->values()
            ->all();
    }

    private function loadBookingOccupancyRows(Carbon $start, Carbon $end): array
    {
        return DB::table('Book2')
            ->selectRaw('RTRIM(ResNo) as ResNo, RTRIM(Kode) as Kode, RTRIM(OriginalGuest) as Guest, TglIn, TglOut')
            ->whereRaw('ISNULL(Batal, 0) = 0')
            ->whereDate('TglIn', '<=', $end->format('Y-m-d'))
            ->whereDate('TglOut', '>=', $start->format('Y-m-d'))
            ->get()
            ->map(fn ($row) => [
                'res_no' => trim((string) $row->ResNo),
                'kode' => trim((string) $row->Kode),
                'guest' => trim((string) $row->Guest),
                'tgl_in' => $this->dateString($row->TglIn ?? null),
                'tgl_out' => $this->dateString($row->TglOut ?? null),
            ])
            ->filter(fn (array $row) => $row['kode'] !== '' && $row['tgl_in'] !== null)
            ->values()
            ->all();
    }

    private function restrictedRoomCodes(): array
    {
        $restrictedStatuses = ['OUT OF ORDER', 'RENOVATED'];

        return DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Status) as Status, RTRIM(StatusB) as StatusB, RTRIM(StatusC) as StatusC, RTRIM(STATUS2) as STATUS2')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->get()
            ->filter(function ($room) use ($restrictedStatuses) {
                $statuses = [
                    strtoupper(trim((string) ($room->Status ?? ''))),
                    strtoupper(trim((string) ($room->StatusB ?? ''))),
                    strtoupper(trim((string) ($room->StatusC ?? ''))),
                    strtoupper(trim((string) ($room->STATUS2 ?? ''))),
                ];

                return count(array_intersect($statuses, $restrictedStatuses)) > 0;
            })
            ->pluck('Kode')
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function latestValidDate(array $values): ?Carbon
    {
        return collect($values)
            ->map(fn ($value) => $this->dateString($value))
            ->filter()
            ->map(fn ($value) => Carbon::parse($value)->startOfDay())
            ->sort()
            ->last();
    }

    private function dateString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($date->year <= 2000) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function generateNextResNo(): string
    {
        $prefix = Carbon::now()->format('Ym') . '0088';
        $maxSequence = DB::table('Book')
            ->selectRaw('RTRIM(ResNo) as ResNo')
            ->whereRaw('RTRIM(ResNo) LIKE ?', [$prefix . '%'])
            ->get()
            ->map(function ($row) {
                $suffix = substr(trim((string) ($row->ResNo ?? '')), -4);
                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        return $prefix . str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private function buildResno2(string $resNo, string $roomCode): string
    {
        $base = $resNo . $roomCode;

        if (!DB::table('Book2')->whereRaw('RTRIM(Resno2) = ?', [$base])->exists()) {
            return $base;
        }

        $sequence = 2;
        do {
            $candidate = $base . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table('Book2')->whereRaw('RTRIM(Resno2) = ?', [$candidate])->exists());

        return $candidate;
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function normalizeTimeForSql(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            $raw = '00:00';
        }

        try {
            return Carbon::createFromFormat('H:i', $raw)->format('H:i:s');
        } catch (\Throwable $exception) {
            return '00:00:00';
        }
    }

    private function buildStrTgl(string $date, string $time): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)->format('YmdHis');
        } catch (\Throwable $exception) {
            return Carbon::parse($date)->format('Ymd') . '000000';
        }
    }
}
