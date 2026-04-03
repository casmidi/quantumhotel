<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                    continue;
                }

                $counts['occupied']++;

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
                continue;
            }

            if ($status === 'Vacant Dirty') {
                $counts['vacant_dirty']++;
                continue;
            }

            if ($status === 'Vacant Clean') {
                $counts['vacant_clean']++;
                continue;
            }

            if ($status === 'Vacant Ready') {
                $counts['vacant_ready']++;
                continue;
            }

            if ($status === 'Renovated') {
                $counts['renovated']++;
                continue;
            }

            if ($status === 'Out Of Order') {
                $counts['out_of_order']++;
                continue;
            }

            if ($status === 'Owner Unit') {
                $counts['owner_unit']++;
                $counts['complimentary']++;
                continue;
            }

            if ($status === 'Complimentary') {
                $counts['complimentary']++;
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

        return view('dashboard', [
            'metrics' => $metrics,
            'occupiedBreakdown' => $occupiedBreakdown,
            'totalRooms' => $totalRooms,
            'operationalBase' => $operationalBase,
        ]);
    }
}
