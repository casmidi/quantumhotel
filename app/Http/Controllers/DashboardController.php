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
            ->selectRaw("MasROOM.Kode, MasROOM.Kelas, MasDATA2.Person, MasDATA2.Payment, MasDATA2.Package, MasDATA2.TglIn, MasROOM.Layar, MasROOM.Urut, MasROOM.Status2, {$statusExpression} as Status, MasROOM.StatusB, MasROOM.StatusC, MasROOM.StatusMove")
            ->orderBy('MasROOM.Urut')
            ->get();

        $dashboardCounts = [
            'occupied' => $roomStatuses->where('Status', 'Occupied')->count(),
            'vacant_dirty' => $roomStatuses->where('Status', 'Vacant Dirty')->count(),
            'vacant_clean' => $roomStatuses->filter(function ($room) {
                return in_array($room->Status, ['Vacant Clean', 'Vacant Ready'], true);
            })->count(),
            'renovated' => $roomStatuses->where('Status', 'Renovated')->count(),
            'out_of_order' => $roomStatuses->where('Status', 'Out Of Order')->count(),
        ];

        $statusGroups = [
            'Occupied',
            'Vacant Dirty',
            'Vacant Clean',
            'Vacant Ready',
            'Renovated',
            'Out Of Order',
            'Owner Unit',
            'Complimentary',
        ];

        $statusBreakdown = collect($statusGroups)
            ->map(function ($status) use ($roomStatuses) {
                return [
                    'label' => $status,
                    'count' => $roomStatuses->where('Status', $status)->count(),
                ];
            })
            ->filter(fn ($item) => $item['count'] > 0)
            ->values();

        $roomHighlights = $roomStatuses
            ->filter(fn ($room) => in_array($room->Status, ['Occupied', 'Vacant Dirty', 'Out Of Order', 'Renovated'], true))
            ->take(8)
            ->values();

        return view('dashboard', [
            'dashboardCounts' => $dashboardCounts,
            'statusBreakdown' => $statusBreakdown,
            'roomHighlights' => $roomHighlights,
            'totalRooms' => $roomStatuses->count(),
        ]);
    }
}
