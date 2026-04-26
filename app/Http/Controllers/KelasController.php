<?php

namespace App\Http\Controllers;

use App\Events\KelasUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $idSelect = $this->legacyIdSelect('KELAS');
        $query = DB::table('KELAS')
            ->selectRaw("$idSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1, Depo1");

        if ($request->q) {
            $keyword = trim((string) $request->q);
            $query->where(function ($builder) use ($keyword) {
                $builder->whereRaw('RTRIM(Kode) like ?', ['%' . $keyword . '%'])
                    ->orWhereRaw('RTRIM(Nama) like ?', ['%' . $keyword . '%']);
            });
        }

        $kelasCollection = $query->orderBy('Kode')->get();
        $kelas = $this->paginateCollection($kelasCollection, 10, $request);

        if ($request->header('X-Partial-Component') === 'kelas-directory') {
            return view('kelas.partials.directory-section', [
                'kelas' => $kelas,
            ]);
        }

        return $this->respond($request, 'kelas.index', [
            'kelas' => $kelas,
        ], $kelas);
    }

    public function store(Request $request)
    {
        $kode = trim((string) $request->Kode);

        if ($kode === '') {
            return $this->respondError($request, 'Kode wajib diisi.');
        }

        $existing = DB::table('KELAS')
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->first();

        if ($existing) {
            DB::table('KELAS')
                ->whereRaw('RTRIM(Kode) = ?', [$kode])
                ->update([
                    'Nama' => $request->Nama,
                    'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
                    'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
                ]);

            event(new KelasUpdated());

            return $this->respondAfterMutation($request, '/kelas', 'Existing room class updated successfully', $this->findKelas($kode));
        }

        DB::table('KELAS')->insert([
            'Kode' => $kode,
            'Nama' => $request->Nama,
            'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
            'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
        ]);

        event(new KelasUpdated());

        return $this->respondAfterMutation($request, '/kelas', 'Data saved successfully', $this->findKelas($kode), 201);
    }

    public function update(Request $request, $kode)
    {
        $kode = trim((string) $kode);
        $existing = $this->findKelas($kode);

        if (!$existing) {
            return $this->respondError($request, 'Data kelas tidak ditemukan.', 404, [], '/kelas', false);
        }

        DB::table('KELAS')
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->update([
                'Nama' => $request->Nama,
                'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
                'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
            ]);

        event(new KelasUpdated());

        return $this->respondAfterMutation($request, '/kelas', 'Data updated successfully', $this->findKelas($kode));
    }

    public function destroy(Request $request, $kode)
    {
        $kode = trim((string) $kode);
        $existing = $this->findKelas($kode);

        if (!$existing) {
            return $this->respondError($request, 'Data kelas tidak ditemukan.', 404, [], '/kelas', false);
        }

        DB::table('KELAS')
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->delete();

        event(new KelasUpdated());

        return $this->respondAfterMutation($request, '/kelas', 'Data deleted successfully', [
            'Kode' => $kode,
        ]);
    }

    public function data()
    {
        $idSelect = $this->legacyIdSelect('KELAS');
        return response()->json([
            'success' => true,
            'data' => DB::table('KELAS')
                ->selectRaw("$idSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1, Depo1")
                ->orderBy('Kode')
                ->get(),
        ]);
    }

    private function findKelas(string $kode)
    {
        $idSelect = $this->legacyIdSelect('KELAS');
        return DB::table('KELAS')
            ->selectRaw("$idSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1, Depo1")
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->first();
    }
}
