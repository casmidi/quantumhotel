<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPackageController extends Controller
{
    public function index(Request $request)
    {
        $idSelect = $this->legacyIdSelect('StockPackage');
        $itemQuery = DB::table('StockPackage')
            ->selectRaw("$idSelect, RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Satuan) as Satuan, RTRIM(Kind) as Kind, Hj");

        $itemCollection = $itemQuery
            ->orderBy('KodeBrg')
            ->get();

        $summary = [
            'total' => $itemCollection->count(),
            'room' => $itemCollection->where('Kind', 'ROOM')->count(),
            'restaurant' => $itemCollection->where('Kind', 'RESTAURANT')->count(),
            'avgSellingPrice' => (float) ($itemCollection->avg('Hj') ?? 0),
        ];

        $items = $this->paginateCollection($itemCollection, 10, $request);

        return $this->respond($request, 'package.item-global', compact('items', 'summary'), [
            'items' => $this->paginatorPayload($items),
            'summary' => $summary,
        ]);
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
            return $this->respondError($request, 'Item code, item name, and selling price must be valid.', 422, [], '/item-package-global', true);
        }

        if (!$this->isAllowedKind($kind)) {
            return $this->respondError($request, 'Category must be ROOM or RESTAURANT.', 422, [], '/item-package-global', true);
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

            return $this->respondAfterMutation($request, '/item-package-global', 'Existing package item updated successfully', $this->findStockPackage($kodeBrg));
        }

        DB::table('StockPackage')->insert(array_merge($payload, [
            'KodeBrg' => $kodeBrg,
        ]));

        return $this->respondAfterMutation($request, '/item-package-global', 'Data saved successfully', $this->findStockPackage($kodeBrg), 201);
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
            return $this->respondError($request, 'Item code, item name, and selling price must be valid.', 422, [], '/item-package-global', true);
        }

        if (!$this->isAllowedKind($kind)) {
            return $this->respondError($request, 'Category must be ROOM or RESTAURANT.', 422, [], '/item-package-global', true);
        }

        $exists = DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
            ->exists();

        if (!$exists) {
            return $this->respondError($request, 'Package item was not found.', 404, [], '/item-package-global', false);
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

        return $this->respondAfterMutation($request, '/item-package-global', 'Data updated successfully', $this->findStockPackage($kodeBrg));
    }

    public function destroy(Request $request, $kode)
    {
        $normalizedCode = $this->normalizeCode($kode);
        $existing = $this->findStockPackage($normalizedCode);

        if (!$existing) {
            return $this->respondError($request, 'Package item was not found.', 404, [], '/item-package-global', false);
        }

        $isUsed = DB::table('PackageD')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$normalizedCode])
            ->exists();

        if ($isUsed) {
            return $this->respondError($request, 'This package item is already used in package transactions and cannot be deleted.', 422, [], '/item-package-global', false);
        }

        DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$normalizedCode])
            ->delete();

        return $this->respondAfterMutation($request, '/item-package-global', 'Data deleted successfully', [
            'id' => $existing->id ?? null,
            'KodeBrg' => $normalizedCode,
        ]);
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

    private function findStockPackage(string $kodeBrg)
    {
        $idSelect = $this->legacyIdSelect('StockPackage');
        return DB::table('StockPackage')
            ->selectRaw("$idSelect, RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Satuan) as Satuan, RTRIM(Kind) as Kind, Hj")
            ->whereRaw('RTRIM(KodeBrg) = ?', [$kodeBrg])
            ->first();
    }
}
