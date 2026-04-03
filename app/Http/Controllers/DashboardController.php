<?php

namespace App\Http\Controllers;

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
            ->selectRaw("MasROOM.Kode, {$statusExpression} as Status")
            ->orderBy('MasROOM.Urut')
            ->get();

        $totalRooms = $roomStatuses->count();

        $metrics = [
            [
                'key' => 'occupied',
                'label' => 'Occupied',
                'count' => $roomStatuses->where('Status', 'Occupied')->count(),
                'tone' => 'occupied',
            ],
            [
                'key' => 'vacant_dirty',
                'label' => 'Vacant Dirty',
                'count' => $roomStatuses->where('Status', 'Vacant Dirty')->count(),
                'tone' => 'dirty',
            ],
            [
                'key' => 'vacant_clean',
                'label' => 'Vacant Clean',
                'count' => $roomStatuses->filter(function ($room) {
                    return in_array($room->Status, ['Vacant Clean', 'Vacant Ready'], true);
                })->count(),
                'tone' => 'clean',
            ],
            [
                'key' => 'renovated',
                'label' => 'Renovated',
                'count' => $roomStatuses->where('Status', 'Renovated')->count(),
                'tone' => 'renovated',
            ],
            [
                'key' => 'out_of_order',
                'label' => 'Out Of Order',
                'count' => $roomStatuses->where('Status', 'Out Of Order')->count(),
                'tone' => 'out-of-order',
            ],
        ];

        $metrics = collect($metrics)
            ->map(function ($metric) use ($totalRooms) {
                $metric['percentage'] = $totalRooms > 0
                    ? round(($metric['count'] / $totalRooms) * 100, 2)
                    : 0;

                return $metric;
            })
            ->values();

        return view('dashboard', [
            'metrics' => $metrics,
            'totalRooms' => $totalRooms,
        ]);
    }
}
