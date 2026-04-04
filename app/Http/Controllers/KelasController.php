<?php

namespace App\Http\Controllers;

use App\Events\KelasUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('KELAS');

        if ($request->q) {
            $search = trim((string) $request->q);
            $query->where(function ($builder) use ($search) {
                $builder->where('Kode', 'like', '%' . $search . '%')
                    ->orWhere('Nama', 'like', '%' . $search . '%');
            });
        }

        $kelasCollection = $query
            ->orderBy('Kode')
            ->get();

        $kelas = $this->paginateCollection($kelasCollection, 10, $request);

        $summary = [
            'total' => $kelasCollection->count(),
            'avgRate' => (float) ($kelasCollection->avg('Rate1') ?? 0),
            'avgDepo' => (float) ($kelasCollection->avg('Depo1') ?? 0),
        ];

        return view('kelas.index', compact('kelas', 'summary'));
    }

    public function store(Request $request)
    {
        $kode = trim((string) $request->Kode);

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

            return redirect('/kelas')->with('success', 'Existing room class updated successfully');
        }

        DB::table('KELAS')->insert([
            'Kode' => $kode,
            'Nama' => $request->Nama,
            'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
            'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
        ]);

        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data saved successfully');
    }

    public function update(Request $request, $kode)
    {
        DB::table('KELAS')
            ->where('Kode', $kode)
            ->update([
                'Nama' => $request->Nama,
                'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
                'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
            ]);

        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data updated successfully');
    }

    public function destroy($kode)
    {
        DB::table('KELAS')->where('Kode', $kode)->delete();

        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data deleted successfully');
    }

    public function data()
    {
        return response()->json(
            DB::table('KELAS')->orderBy('Kode')->get()
        );
    }
}