<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPackageController extends Controller
{
    public function index()
    {
        $items = DB::table('StockPackage')
            ->selectRaw("
                RTRIM(KodeBrg) as KodeBrg,
                RTRIM(NamaBrg) as NamaBrg,
                RTRIM(Satuan) as Satuan,
                RTRIM(Kind) as Kind,
                Hj
            ")
            ->orderBy('KodeBrg')
            ->get();

        $summary = [
            'total' => $items->count(),
            'room' => $items->where('Kind', 'ROOM')->count(),
            'restaurant' => $items->where('Kind', 'RESTAURANT')->count(),
        ];

        return view('package.item-global', compact('items', 'summary'));
    }

    public function store(Request $request)
    {
        $kodeBrg = $this->normalizeCode($request->KodeBrg);
        $namaBrg = strtoupper(trim((string) $request->NamaBrg));
        $satuan = strtoupper(trim((string) ($request->Satuan ?: 'PAX')));
        $kind = strtoupper(trim((string) ($request->Kind ?: 'ROOM')));
        $hj = $this->normalizeMoney($request->Hj);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        if ($kodeBrg === '' || $namaBrg === '' || $hj <= 0) {
            return redirect('/item-package-global')
                ->with('error', 'Item code, item name, and selling price must be valid.')
                ->withInput();
        }

        $payload = [
            'NamaBrg' => $namaBrg,
            'Satuan' => $satuan,
            'Hj' => $hj,
            'Kind' => $kind,
            'Hb' => 0,
            'Hpr' => 0,
            'HprAwal' => 0,
            'StockMin' => 0,
            'StockMax' => 0,
            'Stock' => 0,
            'UserName' => $username,
            'Tgl' => now(),
        ];

        $existing = DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
            ->first();

        if ($existing) {
            DB::table('StockPackage')
                ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
                ->update($payload);

            return redirect('/item-package-global')->with('success', 'Existing package item updated successfully');
        }

        DB::table('StockPackage')->insert(array_merge($payload, [
            'KodeBrg' => $kodeBrg,
        ]));

        return redirect('/item-package-global')->with('success', 'Data saved successfully');
    }

    public function update(Request $request, $kode)
    {
        $kodeBrg = $this->normalizeCode($kode);
        $namaBrg = strtoupper(trim((string) $request->NamaBrg));
        $satuan = strtoupper(trim((string) ($request->Satuan ?: 'PAX')));
        $kind = strtoupper(trim((string) ($request->Kind ?: 'ROOM')));
        $hj = $this->normalizeMoney($request->Hj);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
            ->update([
                'NamaBrg' => $namaBrg,
                'Satuan' => $satuan,
                'Hj' => $hj,
                'Kind' => $kind,
                'UserName' => $username,
                'Tgl' => now(),
            ]);

        return redirect('/item-package-global')->with('success', 'Data updated successfully');
    }

    public function destroy($kode)
    {
        DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$this->normalizeCode($kode)])
            ->delete();

        return redirect('/item-package-global')->with('success', 'Data deleted successfully');
    }

    private function normalizeCode($value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }
}
