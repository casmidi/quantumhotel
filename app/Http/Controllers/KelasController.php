<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\KelasUpdated;

class KelasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST + SEARCH)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = DB::table('KELAS');

        if ($request->q) {
            $query->where('Kode', 'like', '%' . $request->q . '%')
                  ->orWhere('Nama', 'like', '%' . $request->q . '%');
        }

        $kelas = $query->orderBy('Kode')->get();

        return view('kelas.index', compact('kelas'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (INSERT)
    |--------------------------------------------------------------------------
    */
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

        // 🔥 trigger realtime
        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data saved successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $kode)
    {
        DB::table('KELAS')
            ->where('Kode', $kode)
            ->update([
                'Nama' => $request->Nama,
                'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
                'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
            ]);

        // 🔥 trigger realtime
        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($kode)
    {
        DB::table('KELAS')->where('Kode', $kode)->delete();

        // 🔥 trigger realtime
        event(new KelasUpdated());

        return redirect('/kelas')->with('success', 'Data deleted successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DATA API (UNTUK AJAX / REALTIME LOAD)
    |--------------------------------------------------------------------------
    */
    public function data()
    {
        return response()->json(
            DB::table('KELAS')->orderBy('Kode')->get()
        );
    }
}
