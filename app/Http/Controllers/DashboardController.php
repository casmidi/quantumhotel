<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $roomSubquery = DB::table('ROOM')
            ->select([
                'ROOM.Urut',
                'ROOM.Layar',
                'ROOM.Kode',
                DB::raw('ROOM.Nama as Kelas'),
                DB::raw('ROOM.Status as StatusKamar'),
                'ROOM.StatusB',
                'ROOM.StatusC',
                'ROOM.StatusMove',
                'ROOM.Status2',
            ])
            ->where('ROOM.Kode', '<>', '999');

        $guestSubquery = DB::table('DATA2')
            ->select([
                'DATA2.Kode',
                'DATA2.Payment',
                'DATA2.Person',
                'DATA2.Package',
                'DATA2.TglIn',
            ])
            ->where('Pst', '=', ' ')
            ->where('DATA2.Kode', '<>', '999');

        $statusExpression = "CASE
            WHEN MasDATA2.Kode = MasROOM.Kode THEN 'Occupied'
            WHEN MasROOM.StatusKamar = 'Check Out' THEN 'Vacant Dirty'
            WHEN MasROOM.StatusKamar = 'Out Of Order' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Vacant Dirty' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Renovated' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Vacant Ready' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Vacant Clean' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Owner Unit' THEN MasROOM.StatusKamar
            WHEN MasROOM.StatusKamar = 'Complimentary' THEN MasROOM.StatusKamar
            ELSE COALESCE(MasROOM.StatusKamar, 'Unknown')
        END";

        $roomStatuses = DB::query()
            ->fromSub($roomSubquery, 'MasROOM')
            ->leftJoinSub($guestSubquery, 'MasDATA2', function ($join) {
                $join->on('MasROOM.Kode', '=', 'MasDATA2.Kode');
            })
            ->selectRaw("MasROOM.Kode, MasROOM.StatusKamar, MasROOM.Status2, MasDATA2.Payment, MasDATA2.TglIn, {$statusExpression} as Status")
            ->orderBy('MasROOM.Urut')
            ->get();

        $today = Carbon::today();

        $counts = [
            'occupied' => 0,
            'vacant_dirty' => 0,
            'vacant_clean' => 0,
            'vacant_ready' => 0,
            'renovated' => 0,
            'out_of_order' => 0,
            'complimentary' => 0,
            'owner_unit' => 0,
            'check_out' => 0,
            'occupied_clean' => 0,
            'occupied_dirty' => 0,
            'occupied_clean_short' => 0,
            'occupied_clean_long' => 0,
            'occupied_dirty_short' => 0,
            'occupied_dirty_long' => 0,
        ];

        $roomLists = [
            'occupied_clean' => [],
            'occupied_dirty' => [],
            'occupied_clean_short' => [],
            'occupied_clean_long' => [],
            'occupied_dirty_short' => [],
            'occupied_dirty_long' => [],
        ];

        $statusRoomLists = [
            'occupied' => [],
            'complimentary' => [],
            'owner_unit' => [],
            'vacant_ready' => [],
            'vacant_clean' => [],
            'vacant_dirty' => [],
            'renovated' => [],
            'out_of_order' => [],
        ];

        foreach ($roomStatuses as $room) {
            $status = trim((string) $room->Status);
            $rawStatus = trim((string) $room->StatusKamar);
            $payment = strtoupper(trim((string) $room->Payment));
            $status2 = trim((string) $room->Status2);
            $daysStayed = 0;
            $kode = trim((string) $room->Kode);

            if (!empty($room->TglIn) && $room->TglIn !== '2000-01-01') {
                $daysStayed = Carbon::parse($room->TglIn)->startOfDay()->diffInDays($today);
            }

            if ($status === 'Occupied') {
                if ($payment === 'COMPLIMENT') {
                    $counts['complimentary']++;
                    $statusRoomLists['complimentary'][] = $kode;
                    continue;
                }

                $counts['occupied']++;
                $statusRoomLists['occupied'][] = $kode;

                if ($status2 === 'Occupied Clean') {
                    $counts['occupied_clean']++;
                    $roomLists['occupied_clean'][] = $kode;

                    if ($daysStayed > 2) {
                        $counts['occupied_clean_long']++;
                        $roomLists['occupied_clean_long'][] = $kode;
                    } else {
                        $counts['occupied_clean_short']++;
                        $roomLists['occupied_clean_short'][] = $kode;
                    }
                } else {
                    $counts['occupied_dirty']++;
                    $roomLists['occupied_dirty'][] = $kode;

                    if ($daysStayed > 2) {
                        $counts['occupied_dirty_long']++;
                        $roomLists['occupied_dirty_long'][] = $kode;
                    } else {
                        $counts['occupied_dirty_short']++;
                        $roomLists['occupied_dirty_short'][] = $kode;
                    }
                }

                continue;
            }

            if ($rawStatus === 'Check Out') {
                $counts['check_out']++;
                $counts['vacant_dirty']++;
                $statusRoomLists['vacant_dirty'][] = $kode;
                continue;
            }

            if ($status === 'Vacant Dirty') {
                $counts['vacant_dirty']++;
                $statusRoomLists['vacant_dirty'][] = $kode;
                continue;
            }

            if ($status === 'Vacant Clean') {
                $counts['vacant_clean']++;
                $statusRoomLists['vacant_clean'][] = $kode;
                continue;
            }

            if ($status === 'Vacant Ready') {
                $counts['vacant_ready']++;
                $statusRoomLists['vacant_ready'][] = $kode;
                continue;
            }

            if ($status === 'Renovated') {
                $counts['renovated']++;
                $statusRoomLists['renovated'][] = $kode;
                continue;
            }

            if ($status === 'Out Of Order') {
                $counts['out_of_order']++;
                $statusRoomLists['out_of_order'][] = $kode;
                continue;
            }

            if ($status === 'Owner Unit') {
                $counts['owner_unit']++;
                $counts['complimentary']++;
                $statusRoomLists['owner_unit'][] = $kode;
                continue;
            }

            if ($status === 'Complimentary') {
                $counts['complimentary']++;
                $statusRoomLists['complimentary'][] = $kode;
            }
        }

        $totalRooms = $roomStatuses->count();
        $operationalBase = $totalRooms - $counts['check_out'] - $counts['renovated'] - $counts['out_of_order'] - $counts['complimentary'];
        $operationalBase = $operationalBase > 0 ? $operationalBase : 1;
        $totalBase = $totalRooms > 0 ? $totalRooms : 1;
        $complimentaryOnly = max($counts['complimentary'] - $counts['owner_unit'], 0);

        $metrics = collect([
            [
                'key' => 'occupied',
                'label' => 'Occupied',
                'count' => $counts['occupied'],
                'percentage' => round(($counts['occupied'] / $operationalBase) * 100, 2),
                'tone' => 'occupied',
            ],
            [
                'key' => 'complimentary',
                'label' => 'Complimentary',
                'count' => $complimentaryOnly,
                'percentage' => round(($complimentaryOnly / $totalBase) * 100, 2),
                'tone' => 'complimentary',
            ],
            [
                'key' => 'owner_unit',
                'label' => 'Owner Unit',
                'count' => $counts['owner_unit'],
                'percentage' => round(($counts['owner_unit'] / $totalBase) * 100, 2),
                'tone' => 'owner-unit',
            ],
            [
                'key' => 'vacant_ready',
                'label' => 'Vacant Ready',
                'count' => $counts['vacant_ready'],
                'percentage' => round(($counts['vacant_ready'] / $operationalBase) * 100, 2),
                'tone' => 'ready',
            ],
            [
                'key' => 'vacant_clean',
                'label' => 'Vacant Clean',
                'count' => $counts['vacant_clean'],
                'percentage' => round(($counts['vacant_clean'] / $operationalBase) * 100, 2),
                'tone' => 'clean',
            ],
            [
                'key' => 'vacant_dirty',
                'label' => 'Vacant Dirty',
                'count' => $counts['vacant_dirty'],
                'percentage' => round(($counts['vacant_dirty'] / $operationalBase) * 100, 2),
                'tone' => 'dirty',
            ],
            [
                'key' => 'renovated',
                'label' => 'Renovated',
                'count' => $counts['renovated'],
                'percentage' => round(($counts['renovated'] / $totalBase) * 100, 2),
                'tone' => 'renovated',
            ],
            [
                'key' => 'out_of_order',
                'label' => 'Out Of Order',
                'count' => $counts['out_of_order'],
                'percentage' => round(($counts['out_of_order'] / $totalBase) * 100, 2),
                'tone' => 'out-of-order',
            ],
        ]);

        $occupiedBreakdown = collect([
            [
                'label' => 'Occupied Clean',
                'count' => $counts['occupied_clean'],
                'percentage' => round(($counts['occupied_clean'] / $totalBase) * 100, 2),
                'icon' => '&#128719;',
                'rooms' => $roomLists['occupied_clean'],
            ],
            [
                'label' => 'Occupied Dirty',
                'count' => $counts['occupied_dirty'],
                'percentage' => round(($counts['occupied_dirty'] / $totalBase) * 100, 2),
                'icon' => '&#129532;',
                'rooms' => $roomLists['occupied_dirty'],
            ],
            [
                'label' => 'Occ Clean <= 2',
                'count' => $counts['occupied_clean_short'],
                'percentage' => round(($counts['occupied_clean_short'] / $totalBase) * 100, 2),
                'icon' => '&#9989;',
                'rooms' => $roomLists['occupied_clean_short'],
            ],
            [
                'label' => 'Occ Clean > 2',
                'count' => $counts['occupied_clean_long'],
                'percentage' => round(($counts['occupied_clean_long'] / $totalBase) * 100, 2),
                'icon' => '&#9201;',
                'rooms' => $roomLists['occupied_clean_long'],
            ],
            [
                'label' => 'Occ Dirty <= 2',
                'count' => $counts['occupied_dirty_short'],
                'percentage' => round(($counts['occupied_dirty_short'] / $totalBase) * 100, 2),
                'icon' => '&#129533;',
                'rooms' => $roomLists['occupied_dirty_short'],
            ],
            [
                'label' => 'Occ Dirty > 2',
                'count' => $counts['occupied_dirty_long'],
                'percentage' => round(($counts['occupied_dirty_long'] / $totalBase) * 100, 2),
                'icon' => '&#8987;',
                'rooms' => $roomLists['occupied_dirty_long'],
            ],
        ]);

        $todaySnapshot = $this->buildTodaySnapshot(
            $today->toDateString(),
            $counts,
            $totalRooms,
            $operationalBase,
            $complimentaryOnly
        );

        return view('dashboard', [
            'metrics' => $metrics,
            'occupiedBreakdown' => $occupiedBreakdown,
            'statusRoomLists' => $statusRoomLists,
            'totalRooms' => $totalRooms,
            'operationalBase' => $operationalBase,
            'todaySnapshot' => $todaySnapshot,
        ]);
    }

    private function buildTodaySnapshot(
        string $businessDate,
        array $counts,
        int $totalRooms,
        int $operationalBase,
        int $complimentaryOnly
    ): array {
        $inHouseRooms = $counts['occupied'] + $complimentaryOnly + $counts['owner_unit'];
        $roomsAvailable = $counts['vacant_ready'] + $counts['vacant_clean'];
        $occupancyPercent = $operationalBase > 0 ? round(($inHouseRooms / $operationalBase) * 100, 2) : 0;
        $activeData2 = $this->activeData2Stats($businessDate);

        $summary = [
            'source_label' => 'Live DATA2',
            'business_date' => $businessDate,
            'rooms_available' => $roomsAvailable,
            'estimated_occupied' => $inHouseRooms,
            'guest_in_house' => $activeData2['guest_in_house'],
            'arrival_count' => $activeData2['arrival_count'],
            'departure_count' => $activeData2['departure_count'],
            'complimentary_rooms' => $complimentaryOnly,
            'owner_unit' => $counts['owner_unit'],
            'vacant_ready' => $counts['vacant_ready'],
            'vacant_clean' => $counts['vacant_clean'],
            'vacant_dirty' => $counts['vacant_dirty'],
            'renovated' => $counts['renovated'],
            'out_of_order' => $counts['out_of_order'],
            'occupancy_percent' => $occupancyPercent,
            'room_revenue' => $activeData2['room_revenue'],
            'gross_revenue' => $activeData2['room_revenue'],
            'deposit_total' => $activeData2['deposit_total'],
            'exception_count' => 0,
        ];

        $nightAudit = $this->nightAuditSummary($businessDate);
        if ($nightAudit !== null) {
            $summary = array_merge($summary, $nightAudit);
        }

        return [
            'source_label' => $summary['source_label'],
            'business_date' => $summary['business_date'],
            'rows' => [
                ['label' => 'Rooms Available', 'value' => $summary['rooms_available'], 'tone' => 'ready', 'format' => 'number'],
                ['label' => 'Estimated Occupied', 'value' => $summary['estimated_occupied'], 'tone' => 'occupied', 'format' => 'number'],
                ['label' => 'Guests In House', 'value' => $summary['guest_in_house'], 'tone' => 'occupied', 'format' => 'number'],
                ['label' => 'Arrival Today', 'value' => $summary['arrival_count'], 'tone' => 'primary', 'format' => 'number'],
                ['label' => 'Departure Today', 'value' => $summary['departure_count'], 'tone' => 'primary', 'format' => 'number'],
                ['label' => 'Complimentary Rooms', 'value' => $summary['complimentary_rooms'], 'tone' => 'complimentary', 'format' => 'number'],
                ['label' => 'Owner Unit', 'value' => $summary['owner_unit'], 'tone' => 'owner', 'format' => 'number'],
                ['label' => 'Vacant Ready', 'value' => $summary['vacant_ready'], 'tone' => 'ready', 'format' => 'number'],
                ['label' => 'Vacant Clean', 'value' => $summary['vacant_clean'], 'tone' => 'clean', 'format' => 'number'],
                ['label' => 'Vacant Dirty', 'value' => $summary['vacant_dirty'], 'tone' => 'dirty', 'format' => 'number'],
                ['label' => 'Renovated', 'value' => $summary['renovated'], 'tone' => 'restricted', 'format' => 'number'],
                ['label' => 'Out Of Order', 'value' => $summary['out_of_order'], 'tone' => 'restricted', 'format' => 'number'],
                ['label' => '% Occupancy', 'value' => $summary['occupancy_percent'], 'tone' => 'occupied', 'format' => 'percent'],
                ['label' => 'Room Revenue', 'value' => $summary['room_revenue'], 'tone' => 'primary', 'format' => 'money'],
                ['label' => 'Total Revenue', 'value' => $summary['gross_revenue'], 'tone' => 'primary', 'format' => 'money'],
                ['label' => 'Deposit Total', 'value' => $summary['deposit_total'], 'tone' => 'ready', 'format' => 'money'],
                ['label' => 'Night Audit Exceptions', 'value' => $summary['exception_count'], 'tone' => 'restricted', 'format' => 'number'],
            ],
        ];
    }

    private function nightAuditSummary(string $businessDate): ?array
    {
        if (!Schema::hasTable('night_audit_batches')) {
            return null;
        }

        $batch = DB::table('night_audit_batches')
            ->whereDate('business_date', $businessDate)
            ->orderByDesc('id')
            ->first();

        if (!$batch) {
            return null;
        }

        $source = 'Night Audit';
        if (!empty($batch->status)) {
            $source .= ' ' . trim((string) $batch->status);
        }

        return [
            'source_label' => $source,
            'business_date' => Carbon::parse($batch->business_date)->format('Y-m-d'),
            'rooms_available' => (int) ($batch->vacant_rooms ?? 0),
            'estimated_occupied' => (int) ($batch->occupied_rooms ?? 0),
            'guest_in_house' => (int) ($batch->in_house_count ?? 0),
            'arrival_count' => (int) ($batch->arrival_count ?? 0),
            'departure_count' => (int) ($batch->departure_count ?? 0),
            'complimentary_rooms' => (int) ($batch->complimentary_rooms ?? 0),
            'owner_unit' => (int) ($batch->house_use_rooms ?? 0),
            'occupancy_percent' => (float) ($batch->occupancy_percent ?? 0),
            'room_revenue' => (float) ($batch->room_revenue ?? 0),
            'gross_revenue' => (float) ($batch->gross_revenue ?? 0),
            'deposit_total' => (float) ($batch->deposit_total ?? 0),
            'exception_count' => (int) ($batch->exception_count ?? 0),
        ];
    }

    private function activeData2Stats(string $businessDate): array
    {
        return [
            'arrival_count' => $this->countRowsByDate('DATA2', 'TglIn', $businessDate),
            'departure_count' => $this->countRowsByDate('DATA2', 'TglKeluar', $businessDate),
            'guest_in_house' => $this->sumActiveData2People(),
            'room_revenue' => $this->sumActiveData2RoomRevenue(),
            'deposit_total' => $this->sumActiveDeposit(),
        ];
    }

    private function countRowsByDate(string $table, string $column, string $businessDate): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        $query = DB::table($table)->whereDate($column, $businessDate);

        if (Schema::hasColumn($table, 'Kode')) {
            $query->whereRaw("RTRIM(Kode) <> '999'");
        }

        return (int) $query->count();
    }

    private function activeData2BaseQuery()
    {
        return DB::table('DATA2')
            ->where('DATA2.Pst', '=', ' ')
            ->whereRaw("RTRIM(DATA2.Kode) <> '999'");
    }

    private function sumActiveData2People(): int
    {
        if (!Schema::hasTable('DATA2') || !Schema::hasColumn('DATA2', 'Person')) {
            return 0;
        }

        try {
            return (int) $this->activeData2BaseQuery()
                ->selectRaw('SUM(COALESCE(TRY_CONVERT(int, Person), 0)) as total')
                ->value('total');
        } catch (\Throwable $exception) {
            return (int) $this->activeData2BaseQuery()->sum('Person');
        }
    }

    private function sumActiveData2RoomRevenue(): float
    {
        if (!Schema::hasTable('DATA2') || !Schema::hasColumn('DATA2', 'Nominal')) {
            return 0;
        }

        $discountExpression = Schema::hasColumn('DATA2', 'Disc')
            ? 'COALESCE(Nominal, 0) * COALESCE(Disc, 0) / 100'
            : '0';

        $paymentExpression = Schema::hasColumn('DATA2', 'Payment')
            ? "WHEN UPPER(RTRIM(Payment)) LIKE '%COMPLIMENT%' OR UPPER(RTRIM(Payment)) LIKE '%HOUSE%' THEN 0"
            : '';

        return (float) $this->activeData2BaseQuery()
            ->selectRaw("
                SUM(CASE
                    {$paymentExpression}
                    ELSE COALESCE(Nominal, 0) - {$discountExpression}
                END) as total
            ")
            ->value('total');
    }

    private function sumActiveDeposit(): float
    {
        if (!Schema::hasTable('DATA') || !Schema::hasTable('DATA2') || !Schema::hasColumn('DATA', 'Deposit')) {
            return 0;
        }

        return (float) $this->activeData2BaseQuery()
            ->leftJoin('DATA', 'DATA2.RegNo', '=', 'DATA.RegNo')
            ->sum('DATA.Deposit');
    }
}
