<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPackageController extends Controller
{
    public function index()
    {
        $itemQuery = DB::table('StockPackage')
            ->selectRaw("RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Satuan) as Satuan, RTRIM(Kind) as Kind, Hj");

        $summary = [
            'total' => (clone $itemQuery)->count(),
            'room' => (clone $itemQuery)->whereRaw("RTRIM(Kind) = 'ROOM'")->count(),
            'restaurant' => (clone $itemQuery)->whereRaw("RTRIM(Kind) = 'RESTAURANT'")->count(),
            'avgSellingPrice' => (float) ((clone $itemQuery)->avg('Hj') ?? 0),
        ];

        $items = $itemQuery
            ->orderBy('KodeBrg')
            ->paginate(10);

        return view('package.item-global', compact('items', 'summary'));
    }

    public function store(Request $request)
    {
        $kodeBrg = $this->normalizeCode($request->KodeBrg);
        $namaBrg = strtoupper(trim((string) $request->NamaBrg));
        $satuan = strtoupper(trim((string) ($request->Satuan ?: 'PAX')));
        $kind = $this->normalizeKind($request->Kind);
        $hj = $this->normalizeMoney($request->Hj);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        if ($kodeBrg === '' || $namaBrg === '' || $hj <= 0) {
            return redirect('/item-package-global')
                ->with('error', 'Item code, item name, and selling price must be valid.')
                ->withInput();
        }

        if (!$this->isAllowedKind($kind)) {
            return redirect('/item-package-global')
                ->with('error', 'Category must be ROOM or RESTAURANT.')
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
        $kind = $this->normalizeKind($request->Kind);
        $hj = $this->normalizeMoney($request->Hj);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        if ($kodeBrg === '' || $namaBrg === '' || $hj <= 0) {
            return redirect('/item-package-global')
                ->with('error', 'Item code, item name, and selling price must be valid.')
                ->withInput();
        }

        if (!$this->isAllowedKind($kind)) {
            return redirect('/item-package-global')
                ->with('error', 'Category must be ROOM or RESTAURANT.')
                ->withInput();
        }

        $exists = DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
            ->exists();

        if (!$exists) {
            return redirect('/item-package-global')->with('error', 'Package item was not found.');
        }

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
        $normalizedCode = $this->normalizeCode($kode);
        $isUsed = DB::table('PackageD')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$normalizedCode])
            ->exists();

        if ($isUsed) {
            return redirect('/item-package-global')
                ->with('error', 'This package item is already used in package transactions and cannot be deleted.');
        }

        DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$normalizedCode])
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

    private function normalizeKind($value): string
    {
        $kind = strtoupper(trim((string) ($value ?: 'ROOM')));

        return $kind === '' ? 'ROOM' : $kind;
    }

    private function isAllowedKind(string $kind): bool
    {
        return in_array($kind, ['ROOM', 'RESTAURANT'], true);
    }
}