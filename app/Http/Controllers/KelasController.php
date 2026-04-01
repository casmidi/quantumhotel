<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    // =========================
    // TAMPIL DATA
    // =========================
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

    // =========================
    // SIMPAN DATA
    // =========================
    public function store(Request $request)
    {
        DB::table('KELAS')->insert([
            'Kode' => $request->Kode,
            'Nama' => $request->Nama,
            'Rate1' => $request->Rate1 ?? 0,
            'Depo1' => $request->Depo1 ?? 0,
        ]);

        return redirect('/kelas')->with('success', 'Data berhasil disimpan');
    }

    // =========================
    // HAPUS DATA
    // =========================
    public function destroy($kode)
    {
        DB::table('KELAS')->where('Kode', $kode)->delete();

        return redirect('/kelas')->with('success', 'Data berhasil dihapus');
    }

    public function edit($kode)
{
    $kelas = DB::table('KELAS')->where('Kode', $kode)->first();

    return view('kelas.edit', compact('kelas'));
}

    public function update(Request $request, $kode)
    {
        DB::table('KELAS')
            ->where('Kode', $kode)
            ->update([
                'Nama' => $request->Nama,
                'Rate1' => $request->Rate1,
                'Depo1' => $request->Depo1,
            ]);

        return redirect('/kelas')->with('success', 'Data updated');
    }
}