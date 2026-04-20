<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $roomIdSelect = $this->legacyIdSelect('ROOM');
        $kelasIdSelect = $this->legacyIdSelect('KELAS');
        $roomQuery = DB::table('ROOM')
            ->selectRaw("$roomIdSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Fasilitas) as Fasilitas, RTRIM(ExtNo) as ExtNo, RTRIM(KUNCI) as KUNCI, Rate1, Rate2")
            ->whereRaw("RTRIM(Kode) <> '999'");

        $roomCollection = $roomQuery
            ->orderBy('Kode')
            ->get();

        $summary = [
            'total' => $roomCollection->count(),
            'avgRate' => (float) ($roomCollection->avg('Rate1') ?? 0),
            'avgBasicRate' => (float) ($roomCollection->avg('Rate2') ?? 0),
        ];

        $rooms = $this->paginateCollection($roomCollection, 10, $request);

        $classes = DB::table('KELAS')
            ->selectRaw("$kelasIdSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1")
            ->orderBy('Kode')
            ->get();

        return $this->respond($request, 'room.index', compact('rooms', 'classes', 'summary'), [
            'rooms' => $this->paginatorPayload($rooms),
            'classes' => $classes,
            'summary' => $summary,
        ]);
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

        if ($kode === '' || $classCode === '') {
            return $this->respondError($request, 'Room code and class are required.');
        }

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

            return $this->respondAfterMutation($request, '/room', 'Existing room updated successfully', $this->findRoom($kode));
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

        return $this->respondAfterMutation($request, '/room', 'Data saved successfully', $this->findRoom($kode), 201);
    }

    public function update(Request $request, $kode)
    {
        $normalizedKode = strtoupper(trim((string) $kode));
        $existing = $this->findRoom($normalizedKode);

        if (!$existing) {
            return $this->respondError($request, 'Room was not found.', 404, [], '/room', false);
        }

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

        return $this->respondAfterMutation($request, '/room', 'Data updated successfully', $this->findRoom($normalizedKode));
    }

    public function destroy(Request $request, $kode)
    {
        $normalizedKode = strtoupper(trim((string) $kode));
        $existing = $this->findRoom($normalizedKode);

        if (!$existing) {
            return $this->respondError($request, 'Room was not found.', 404, [], '/room', false);
        }

        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$normalizedKode])
            ->delete();

        return $this->respondAfterMutation($request, '/room', 'Data deleted successfully', [
            'id' => $existing->id ?? null,
            'Kode' => $normalizedKode,
        ]);
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function findRoom(string $kode)
    {
        $idSelect = $this->legacyIdSelect('ROOM');
        return DB::table('ROOM')
            ->selectRaw("$idSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Fasilitas) as Fasilitas, RTRIM(ExtNo) as ExtNo, RTRIM(KUNCI) as KUNCI, Rate1, Rate2")
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->first();
    }
}
