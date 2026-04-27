<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoRoomBookingSeeder extends Seeder
{
    private string $prefix = 'DMBK';

    public function run(): void
    {
        $rooms = DB::table('ROOM')
            ->selectRaw('RTRIM(Kode) as Kode, RTRIM(Nama) as Kelas, Rate1')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->orderBy('Kode')
            ->get()
            ->values();

        if ($rooms->isEmpty()) {
            $this->command?->warn('No ROOM data found. Demo bookings were not seeded.');
            return;
        }

        $this->clearPreviousDemoRows();

        $today = Carbon::today();
        $patterns = [
            [0, 2, 0, 'ARIANA PUTRI', 'DIRECT', 'PA', 'Deluxe breakfast package'],
            [1, 4, 1, 'BIMA SAPUTRA', 'TRAVELOKA', 'OTA', 'Late arrival request'],
            [2, 3, 2, 'CINDY TAN', 'CORPORATE', 'COMPANY', 'Airport pickup'],
            [4, 6, 3, 'DANIEL WIJAYA', 'AGODA', 'OTA', 'Twin bed preference'],
            [5, 8, 4, 'ELENA SARI', 'DIRECT', 'CASH', 'Family stay'],
            [7, 10, 5, 'FARHAN HAKIM', 'BOOKING.COM', 'OTA', 'High floor request'],
            [9, 12, 6, 'GITA LESTARI', 'CORPORATE', 'COMPANY', 'Company guarantee'],
            [12, 16, 7, 'HENDRA MULYA', 'DIRECT', 'CARD', 'Anniversary stay'],
            [14, 17, 8, 'IRMA NATALIA', 'TRAVELOKA', 'OTA', 'Non smoking room'],
            [16, 20, 9, 'JASON LIM', 'DIRECT', 'PA', 'Early check-in when available'],
            [18, 22, 10, 'KARINA DEWI', 'AGODA', 'OTA', 'Extra pillow'],
            [21, 25, 11, 'LUKMAN AZIS', 'CORPORATE', 'COMPANY', 'Meeting package'],
            [24, 29, 12, 'MAYA SANTOSO', 'DIRECT', 'CASH', 'Long weekend stay'],
            [26, 29, 13, 'NIKO PRATAMA', 'TRAVELOKA', 'OTA', 'Demo stay band 27-30 style'],
        ];

        foreach ($patterns as $index => [$startOffset, $endOffset, $roomIndex, $guest, $source, $payment, $remark]) {
            $room = $rooms[$roomIndex % $rooms->count()];
            $checkIn = $today->copy()->addDays($startOffset);
            $checkOut = $today->copy()->addDays($endOffset);
            $resNo = $this->prefix . $today->format('ymd') . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $this->insertBookingHeader($resNo, $checkIn, $checkOut, $index % 3 === 0 ? 200000 : 0, $source === 'CORPORATE' ? 'Corporate' : 'Booking');
            $this->insertBookingDetail($resNo, $room, $checkIn, $checkOut, $guest, $source, $payment, $remark, $index + 1);
        }

        $this->seedFiveNightSameRoom($rooms, $today);
        $this->seedMultiRoomReservation($rooms, $today);
        $mayCheckIn = Carbon::create(2026, 5, 1);
        $mayCheckOut = Carbon::create(2026, 5, 7);
        $occupiedMayRooms = $this->occupiedRoomCodes($mayCheckIn, $mayCheckOut);
        $typeBlockRoomCodes = $this->seedMayBlockingByRoomType($rooms, $occupiedMayRooms);
        $this->seedMayBlockingByFloors($rooms, array_merge($occupiedMayRooms, $typeBlockRoomCodes));
        $this->seedMayTwentyRoomStay($rooms);

        $this->command?->info('Demo room bookings seeded, including 20 rooms for 11-20 May 2026.');
    }

    private function seedFiveNightSameRoom($rooms, Carbon $today): void
    {
        $room = $rooms[20 % $rooms->count()];
        $checkIn = $today->copy()->addDays(3);
        $checkOut = $today->copy()->addDays(8);
        $resNo = $this->prefix . $today->format('ymd') . '020';

        $this->insertBookingHeader($resNo, $checkIn, $checkOut, 500000, 'Booking');
        $this->insertBookingDetail($resNo, $room, $checkIn, $checkOut, 'SAME ROOM FIVE NIGHTS', 'DIRECT', 'CARD', 'One room, five consecutive nights', 20);
    }

    private function seedMultiRoomReservation($rooms, Carbon $today): void
    {
        $checkIn = $today->copy()->addDays(6);
        $checkOut = $today->copy()->addDays(10);
        $resNo = $this->prefix . $today->format('ymd') . '030';
        $multiRooms = $rooms->slice(22, 6)->values();

        $this->insertBookingHeader($resNo, $checkIn, $checkOut, 1000000, 'Group');

        foreach ($multiRooms as $index => $room) {
            $this->insertBookingDetail(
                $resNo,
                $room,
                $checkIn,
                $checkOut,
                'FAMILY GROUP ROOM ' . ($index + 1),
                'DIRECT GROUP',
                'PA',
                'One reservation with multiple rooms',
                30 + $index
            );
        }
    }

    private function seedMayBlockingByRoomType($rooms, array $excludedRoomCodes): array
    {
        $checkIn = Carbon::create(2026, 5, 1);
        $checkOut = Carbon::create(2026, 5, 7);
        $resNo = $this->prefix . '260501050';
        $roomType = 'DELUXE TWIN';
        $excluded = collect($excludedRoomCodes)->map(fn ($code) => trim((string) $code))->all();
        $groupRooms = $rooms
            ->filter(fn ($room) => trim((string) $room->Kelas) === $roomType)
            ->reject(fn ($room) => in_array(trim((string) $room->Kode), $excluded, true))
            ->take(50)
            ->values();

        if ($groupRooms->count() < 50) {
            $this->command?->warn('Only ' . $groupRooms->count() . ' ' . $roomType . ' rooms found for the 50-room type block.');
        }

        $this->insertBookingHeader($resNo, $checkIn, $checkOut, 5000000, 'Group');

        foreach ($groupRooms as $index => $room) {
            $this->insertBookingDetail(
                $resNo,
                $room,
                $checkIn,
                $checkOut,
                'MAY TYPE BLOCK',
                'TYPE BLOCK',
                'COMPANY',
                '50 rooms by type',
                100 + $index
            );
        }

        return $groupRooms
            ->pluck('Kode')
            ->map(fn ($code) => trim((string) $code))
            ->values()
            ->all();
    }

    private function seedMayBlockingByFloors($rooms, array $excludedRoomCodes): void
    {
        $checkIn = Carbon::create(2026, 5, 1);
        $checkOut = Carbon::create(2026, 5, 7);
        $resNo = $this->prefix . '260501030';
        $floors = ['3', '5'];
        $excluded = collect($excludedRoomCodes)->map(fn ($code) => trim((string) $code))->all();
        $floorRooms = $rooms
            ->filter(fn ($room) => in_array($this->roomFloor((string) $room->Kode), $floors, true))
            ->reject(fn ($room) => in_array(trim((string) $room->Kode), $excluded, true))
            ->take(30)
            ->values();

        if ($floorRooms->count() < 30) {
            $this->command?->warn('Only ' . $floorRooms->count() . ' rooms found for the 30-room floor block.');
        }

        $this->insertBookingHeader($resNo, $checkIn, $checkOut, 3000000, 'Group');

        foreach ($floorRooms as $index => $room) {
            $this->insertBookingDetail(
                $resNo,
                $room,
                $checkIn,
                $checkOut,
                'MAY FLOOR BLOCK',
                'FLOOR 3/5',
                'COMPANY',
                '30 rooms by floor',
                200 + $index
            );
        }
    }

    private function seedMayTwentyRoomStay($rooms): void
    {
        $checkIn = Carbon::create(2026, 5, 11);
        $checkOut = Carbon::create(2026, 5, 20);
        $resNo = $this->prefix . '260511020';
        $excludedRoomCodes = $this->occupiedRoomCodes($checkIn, $checkOut);
        $excluded = collect($excludedRoomCodes)->map(fn ($code) => trim((string) $code))->all();
        $groupRooms = $rooms
            ->reject(fn ($room) => in_array(trim((string) $room->Kode), $excluded, true))
            ->take(20)
            ->values();

        if ($groupRooms->count() < 20) {
            $this->command?->warn('Only ' . $groupRooms->count() . ' rooms available for the 20-room May 11-20 booking.');
        }

        $this->insertBookingHeader($resNo, $checkIn, $checkOut, 2000000, 'Group');

        foreach ($groupRooms as $index => $room) {
            $this->insertBookingDetail(
                $resNo,
                $room,
                $checkIn,
                $checkOut,
                'MAY GROUP 20 ROOMS',
                'DIRECT GROUP',
                'PA',
                '20 rooms from 11-05-2026 to 20-05-2026',
                300 + $index
            );
        }
    }

    private function roomFloor(string $roomCode): string
    {
        $code = preg_replace('/\D/', '', trim($roomCode));

        if (strlen($code) >= 4) {
            return (string) ((int) substr($code, 0, 2));
        }

        return substr($code, 0, 1);
    }

    private function occupiedRoomCodes(Carbon $checkIn, Carbon $checkOut): array
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

    private function bookedRoomCodes(Carbon $checkIn, Carbon $checkOut): array
    {
        return DB::table('Book2')
            ->selectRaw('RTRIM(Kode) as Kode')
            ->whereRaw('ISNULL(Batal, 0) = 0')
            ->whereDate('TglIn', '<', $checkOut->format('Y-m-d'))
            ->whereDate('TglOut', '>', $checkIn->format('Y-m-d'))
            ->pluck('Kode')
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function data2OccupiedRoomCodes(Carbon $checkIn, Carbon $checkOut): array
    {
        return DB::table('DATA2')
            ->selectRaw('RTRIM(Kode) as Kode, TglIn, TglOut, TglKeluar')
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->get()
            ->filter(function ($row) use ($checkIn, $checkOut) {
                if (empty($row->Kode) || empty($row->TglIn)) {
                    return false;
                }

                $rowStart = Carbon::parse($row->TglIn)->startOfDay();
                $rowEnd = $this->latestValidDate([$row->TglOut ?? null, $row->TglKeluar ?? null]);

                if (!$rowEnd) {
                    return $rowStart->lt($checkOut);
                }

                return $rowStart->lt($checkOut) && $rowEnd->gt($checkIn);
            })
            ->pluck('Kode')
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
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

    private function insertBookingHeader(string $resNo, Carbon $checkIn, Carbon $checkOut, float $deposit, string $type): void
    {
        DB::table('Book')->insert([
            'ResNo' => $resNo,
            'KodeCust' => '',
            'Deposit' => $deposit,
            'Tipe' => $type,
            'Discount' => 0,
            'UserName' => 'DEMO',
            'Batal' => 0,
            'strTgl' => $this->buildStrTgl($checkIn, '14:00:00'),
            'strTglOut' => $this->buildStrTgl($checkOut, '13:00:00'),
            'TglKeluar' => $checkOut->format('Y-m-d'),
            'Bendera' => null,
        ]);
    }

    private function insertBookingDetail(
        string $resNo,
        object $room,
        Carbon $checkIn,
        Carbon $checkOut,
        string $guest,
        string $source,
        string $payment,
        string $remark,
        int $sequence,
        ?string $suffix = null
    ): void {
        $roomCode = trim((string) $room->Kode);
        $rate = (float) ($room->Rate1 ?? 0);
        $rate = $rate > 0 ? $rate : 500000 + (($sequence % 5) * 75000);
        $resno2 = $resNo . $roomCode . ($suffix ? '-' . $suffix : '');

        DB::table('Book2')->insert([
            'ResNo' => $resNo,
            'Pst' => ' ',
            'TglIn' => $checkIn->format('Y-m-d'),
            'JamIn' => '14:00:00',
            'TglOut' => $checkOut->format('Y-m-d'),
            'JamOut' => '13:00:00',
            'OriginalGuest' => $guest,
            'BookingGuest' => $source,
            'KTP' => 'DEMO-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'Alamat' => 'DEMO ADDRESS ' . $sequence,
            'Telphone' => '08' . str_pad((string) (770000000 + $sequence), 10, '0', STR_PAD_LEFT),
            'Person' => ($sequence % 4) + 1,
            'Usaha' => $source,
            'Deposit' => $sequence % 3 === 0 ? 200000 : 0,
            'Batal' => 0,
            'ExecutiveSuite' => null,
            'NewSuite' => null,
            'Suite' => null,
            'Deluxe' => null,
            'Standard' => null,
            'Ekonomi' => null,
            'Payment' => $payment,
            'AcceptBy' => 'DEMO',
            'Remark' => $remark,
            'PostPhone' => 0,
            'Kelas' => trim((string) $room->Kelas),
            'Kode' => $roomCode,
            'Bendera' => null,
            'Rate' => $rate,
            'Resno2' => $resno2,
        ]);

        $this->seedPeriodeRows($resNo, $roomCode, $checkIn, $checkOut);
    }

    private function clearPreviousDemoRows(): void
    {
        DB::table('Book2')->whereRaw('RTRIM(ResNo) LIKE ?', [$this->prefix . '%'])->delete();
        DB::table('Book')->whereRaw('RTRIM(ResNo) LIKE ?', [$this->prefix . '%'])->delete();

        if (Schema::hasTable('Periode')) {
            DB::table('Periode')->whereRaw('RTRIM(ResNo) LIKE ?', [$this->prefix . '%'])->delete();
        }
    }

    private function seedPeriodeRows(string $resNo, string $roomCode, Carbon $checkIn, Carbon $checkOut): void
    {
        if (!Schema::hasTable('Periode')) {
            return;
        }

        $lastStayDate = $checkIn->isSameDay($checkOut) ? $checkIn->copy() : $checkOut->copy()->subDay();
        $cursor = $checkIn->copy();

        while ($cursor->lte($lastStayDate)) {
            DB::table('Periode')->insert([
                'ResNo' => $resNo,
                'Kode' => $roomCode,
                'TglIn' => $checkIn->format('Y-m-d'),
                'TglOut' => $checkOut->format('Y-m-d'),
                'Tgl' => $cursor->format('Y-m-d'),
            ]);

            $cursor->addDay();
        }
    }

    private function buildStrTgl(Carbon $date, string $time): string
    {
        return Carbon::parse($date->format('Y-m-d') . ' ' . $time)->format('YmdHis');
    }
}
