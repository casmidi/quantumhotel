<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /*
    =========================================
    INDEX
    =========================================
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
    =========================================
    STORE (SUPER SAFE)
    =========================================
    */
    public function store(Request $request)
    {
        // 🔥 SANITIZE TOTAL (ANTI ERROR)
        $rate = $this->safeNumber($request->Rate1);
        $depo = $this->safeNumber($request->Depo1);

        DB::table('KELAS')->insert([
            'Kode'  => trim($request->Kode),
            'Nama'  => trim($request->Nama),
            'Rate1' => $rate,
            'Depo1' => $depo,
        ]);

        return redirect('/kelas')->with('success', 'Data saved successfully');
    }


    /*
    =========================================
    UPDATE (SUPER SAFE)
    =========================================
    */
    public function update(Request $request, $kode)
    {
        $rate = $this->safeNumber($request->Rate1);
        $depo = $this->safeNumber($request->Depo1);

        DB::table('KELAS')
            ->where('Kode', $kode)
            ->update([
                'Nama'  => trim($request->Nama),
                'Rate1' => $rate,
                'Depo1' => $depo,
            ]);

        return redirect('/kelas')->with('success', 'Data updated successfully');
    }


    /*
    =========================================
    DELETE
    =========================================
    */
    public function destroy($kode)
    {
        DB::table('KELAS')->where('Kode', $kode)->delete();

        return redirect('/kelas')->with('success', 'Data deleted successfully');
    }


    /*
    =========================================
    FUNCTION ANTI ERROR (KUNCI UTAMA)
    =========================================
    */
    private function safeNumber($value)
    {
        // kalau kosong → 0
        if (!$value) return 0;

        // ambil hanya angka
        $clean = preg_replace('/[^0-9]/', '', $value);

        // kalau hasil kosong → 0
        if ($clean === '' || !is_numeric($clean)) {
            return 0;
        }

        return (int) $clean;
    }
}