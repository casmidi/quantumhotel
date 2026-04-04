<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        $roomQuery = DB::table('ROOM')
            ->selectRaw("RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Fasilitas) as Fasilitas, RTRIM(ExtNo) as ExtNo, RTRIM(KUNCI) as KUNCI, Rate1, Rate2")
            ->whereRaw("RTRIM(Kode) <> '999'");

        $roomCollection = $roomQuery
            ->orderBy('Kode')
            ->get();

        $summary = [
            'total' => $roomCollection->count(),
            'avgRate' => (float) ($roomCollection->avg('Rate1') ?? 0),
            'avgBasicRate' => (float) ($roomCollection->avg('Rate2') ?? 0),
        ];

        $rooms = $this->paginateCollection($roomCollection, 10);

        $classes = DB::table('KELAS')
            ->selectRaw("RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1")
            ->orderBy('Kode')
            ->get();

        return view('room.index', compact('rooms', 'classes', 'summary'));
    }

    public function store(Request $request)
    {
        $kode = strtoupper(trim((string) $request->Kode));
        $classCode = strtoupper(trim((string) $request->Nama));
        $facility = trim((string) $request->Fasilitas);
        $rateWithTax = $this->normalizeMoney($request->Rate1);
        $basicRate = $this->normalizeMoney($request->Rate2);
        $basicRate = $basicRate > 0 ? $basicRate : $rateWithTax;
        $extNo = trim((string) $request->ExtNo);
        $roomKey = trim((string) $request->KUNCI);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        $payload = [
            'Nama' => $classCode,
            'Fasilitas' => $facility,
            'ExtNo' => $extNo,
            'Rate1' => $rateWithTax,
            'Rate2' => $basicRate,
            'Rate3' => $basicRate,
            'Rate4' => $basicRate,
            'KUNCI' => $roomKey !== '' ? $roomKey : $kode,
            'Urut' => $kode,
            'UserName' => $username !== '' ? $username : null,
        ];

        $existing = DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->first();

        if ($existing) {
            DB::table('ROOM')
                ->whereRaw('RTRIM(Kode) = ?', [$kode])
                ->update($payload);

            return redirect('/room')->with('success', 'Existing room updated successfully');
        }

        DB::table('ROOM')->insert(array_merge($payload, [
            'Kode' => $kode,
            'Status' => 'Vacant Ready',
            'StatusB' => null,
            'StatusC' => null,
            'Statusmove' => null,
            'MEETING' => 0,
            'VILA' => 'N',
            'STATUS2' => null,
        ]));

        return redirect('/room')->with('success', 'Data saved successfully');
    }

    public function update(Request $request, $kode)
    {
        $normalizedKode = strtoupper(trim((string) $kode));
        $rateWithTax = $this->normalizeMoney($request->Rate1);
        $basicRate = $this->normalizeMoney($request->Rate2);
        $basicRate = $basicRate > 0 ? $basicRate : $rateWithTax;
        $roomKey = trim((string) $request->KUNCI);

        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$normalizedKode])
            ->update([
                'Nama' => strtoupper(trim((string) $request->Nama)),
                'Fasilitas' => trim((string) $request->Fasilitas),
                'ExtNo' => trim((string) $request->ExtNo),
                'Rate1' => $rateWithTax,
                'Rate2' => $basicRate,
                'Rate3' => $basicRate,
                'Rate4' => $basicRate,
                'KUNCI' => $roomKey !== '' ? $roomKey : $normalizedKode,
                'Urut' => $normalizedKode,
                'UserName' => strtoupper(trim((string) session('user', 'SYSTEM'))),
            ]);

        return redirect('/room')->with('success', 'Data updated successfully');
    }

    public function destroy($kode)
    {
        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [strtoupper(trim((string) $kode))])
            ->delete();

        return redirect('/room')->with('success', 'Data deleted successfully');
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }
}