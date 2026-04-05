<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PackageTransactionController extends Controller
{
    public function index(Request $request)
    {
        $searchType = trim((string) $request->query('search_type', 'all'));
        $searchValue = trim((string) $request->query('search', ''));
        $sortBy = trim((string) $request->query('sort_by', 'invoice'));
        $sortDir = strtolower(trim((string) $request->query('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';
        $perPage = 10;
        $allowedSorts = ['invoice', 'package', 'items', 'expired', 'nominal'];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'invoice';
        }

        $items = DB::table('StockPackage')
            ->selectRaw("RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Kind) as Kind, Hj")
            ->orderBy('KodeBrg')
            ->get();

        [$packages, $summary] = $this->loadPagedPackages($request, $searchType, $searchValue, $sortBy, $sortDir, $perPage);
        $nextNofak = $this->previewPackageNofak();

        if ($request->ajax()) {
            return view('package.partials.transaction-directory-section', compact('packages', 'summary', 'searchType', 'searchValue', 'sortBy', 'sortDir'))->render();
        }

        return view('package.transaction', compact('items', 'packages', 'summary', 'nextNofak', 'searchType', 'searchValue', 'sortBy', 'sortDir'));
    }

    public function store(Request $request)
    {
        return $this->savePackage($request);
    }

    public function update(Request $request, $nofak)
    {
        return $this->savePackage($request, trim((string) $nofak));
    }

    public function destroy($nofak)
    {
        $normalized = trim((string) $nofak);

        if ($this->packageIsUsed($normalized)) {
            return redirect('/menu-package-transaction')->with('error', 'This package transaction is already used in guest transactions and cannot be deleted.');
        }

        DB::transaction(function () use ($normalized) {
            DB::table('PackageD')->whereRaw('RTRIM(Nofak) = ?', [$normalized])->delete();
            DB::table('Package')->whereRaw('RTRIM(Nofak) = ?', [$normalized])->delete();
        });

        return redirect('/menu-package-transaction')->with('success', 'Package transaction deleted successfully');
    }

    private function loadPagedPackages(Request $request, string $searchType, string $searchValue, string $sortBy, string $sortDir, int $perPage): array
    {
        $page = max((int) $request->query('page', 1), 1);
        $offsetStart = (($page - 1) * $perPage) + 1;
        $offsetEnd = $offsetStart + $perPage - 1;

        $bindings = [];
        $whereClause = $this->buildPackageWhereClause($searchType, $searchValue, $bindings);
        $summaryRow = DB::selectOne(
            "SELECT COUNT(*) AS total, ISNULL(SUM(CAST(P.JumlahRes AS float)), 0) AS nominal FROM Package P WHERE $whereClause",
            $bindings
        );

        $summary = [
            'total' => (int) ($summaryRow->total ?? 0),
            'nominal' => (float) ($summaryRow->nominal ?? 0),
        ];

        if ($summary['total'] === 0) {
            $packages = new LengthAwarePaginator(collect(), 0, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return [$packages, $summary];
        }

        $sortExpression = $this->resolvePackageSortExpression($sortBy);
        $direction = $sortDir === 'asc' ? 'ASC' : 'DESC';
        $rowSql = "
            SELECT paged.Nofak, paged.Meja, paged.JumlahRes, paged.Expired
            FROM (
                SELECT
                    ROW_NUMBER() OVER (ORDER BY $sortExpression $direction, RTRIM(P.Nofak) $direction) AS row_num,
                    RTRIM(P.Nofak) AS Nofak,
                    RTRIM(P.Meja) AS Meja,
                    P.JumlahRes,
                    P.Expired
                FROM Package P
                WHERE $whereClause
            ) AS paged
            WHERE paged.row_num BETWEEN ? AND ?
            ORDER BY paged.row_num
        ";

        $pageRows = collect(DB::select($rowSql, array_merge($bindings, [$offsetStart, $offsetEnd])));
        $packageNofaks = $pageRows->pluck('Nofak')->map(fn ($value) => trim((string) $value))->filter()->values()->all();
        $details = $this->loadPackageDetails($packageNofaks);
        $usedPackages = $this->loadUsedPackages($packageNofaks);
        $itemMap = $this->loadStockItemMap();

        $pageRows = $pageRows->map(function ($package) use ($details, $itemMap, $usedPackages) {
            $detailRows = collect($details->get($package->Nofak, []))->map(function ($detail) use ($itemMap) {
                $mapped = $itemMap->get($detail->KodeBrg);

                return [
                    'kode' => $detail->KodeBrg,
                    'name' => $mapped->NamaBrg ?? $detail->KodeBrg,
                    'kind' => $mapped->Kind ?? '',
                    'qty' => (float) $detail->Qty,
                    'price' => (float) $detail->Harga,
                    'amount' => (float) $detail->Jumlah,
                    'sort' => (int) $detail->NoUrut,
                ];
            })->values();

            $package->detail_json = $detailRows->toJson();
            $package->detail_summary = $detailRows->pluck('kode')->implode(', ');
            $package->is_used = $usedPackages->has(trim((string) $package->Nofak));

            return $package;
        })->values();

        $packages = new LengthAwarePaginator(
            $pageRows,
            $summary['total'],
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [$packages, $summary];
    }

    private function buildPackageWhereClause(string $searchType, string $searchValue, array &$bindings): string
    {
        $bindings[] = Carbon::today()->format('Y-m-d');
        $clauses = ['P.Expired >= ?'];

        if ($searchValue === '') {
            return implode(' AND ', $clauses);
        }

        $normalizedSearch = strtoupper(trim($searchValue));
        $normalizedNominal = $this->normalizeMoney($searchValue);

        if (in_array($searchType, ['all', 'invoice'], true)) {
            $invoiceClause = 'UPPER(RTRIM(P.Nofak)) LIKE ?';
        }

        if (in_array($searchType, ['all', 'package'], true)) {
            $packageClause = 'UPPER(RTRIM(P.Meja)) LIKE ?';
        }

        $searchClauses = [];

        if (!empty($invoiceClause ?? null)) {
            $searchClauses[] = $invoiceClause;
            $bindings[] = '%' . $normalizedSearch . '%';
        }

        if (!empty($packageClause ?? null)) {
            $searchClauses[] = $packageClause;
            $bindings[] = '%' . $normalizedSearch . '%';
        }

        if ($normalizedNominal > 0 && in_array($searchType, ['all', 'nominal'], true)) {
            $searchClauses[] = 'P.JumlahRes = ?';
            $bindings[] = $normalizedNominal;
        }

        if (!empty($searchClauses)) {
            $clauses[] = '(' . implode(' OR ', $searchClauses) . ')';
        }

        return implode(' AND ', $clauses);
    }

    private function resolvePackageSortExpression(string $sortBy): string
    {
        return match ($sortBy) {
            'package' => 'RTRIM(P.Meja)',
            'items' => "ISNULL((SELECT TOP 1 RTRIM(PD.KodeBrg) FROM PackageD PD WHERE RTRIM(PD.Nofak) = RTRIM(P.Nofak) ORDER BY PD.NoUrut ASC, RTRIM(PD.KodeBrg) ASC), '')",
            'expired' => 'P.Expired',
            'nominal' => 'P.JumlahRes',
            default => 'RTRIM(P.Nofak)',
        };
    }

    private function loadPackageDetails(array $packageNofaks)
    {
        if (empty($packageNofaks)) {
            return collect();
        }

        return DB::table('PackageD')
            ->selectRaw("RTRIM(Nofak) as Nofak, RTRIM(KodeBrg) as KodeBrg, Qty, Harga, Jumlah, NoUrut")
            ->whereIn(DB::raw('RTRIM(Nofak)'), $packageNofaks)
            ->orderBy('NoUrut')
            ->get()
            ->groupBy('Nofak');
    }

    private function loadUsedPackages(array $packageNofaks)
    {
        if (empty($packageNofaks)) {
            return collect();
        }

        return DB::table('DATA2')
            ->selectRaw("RTRIM(Package) as Package")
            ->whereIn(DB::raw('RTRIM(Package)'), $packageNofaks)
            ->get()
            ->pluck('Package')
            ->map(fn ($value) => trim((string) $value))
            ->flip();
    }

    private function loadStockItemMap()
    {
        return DB::table('StockPackage')
            ->selectRaw("RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Kind) as Kind")
            ->get()
            ->keyBy('KodeBrg');
    }

    private function savePackage(Request $request, ?string $existingNofak = null)
    {
        $packageCode = strtoupper(trim((string) $request->Meja));
        $expired = $request->Expired ? Carbon::parse($request->Expired)->startOfDay() : null;
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));
        $details = $this->extractDetails($request);
        $duplicateItemCodes = $this->findDuplicateItemCodes($request);

        if ($packageCode === '') {
            return redirect('/menu-package-transaction')->with('error', 'Package code is required.')->withInput();
        }

        if (!$expired) {
            return redirect('/menu-package-transaction')->with('error', 'Expired date is required.')->withInput();
        }

        if ($expired->lt(Carbon::today())) {
            return redirect('/menu-package-transaction')->with('error', 'Expired date must be greater than or equal to today.')->withInput();
        }

        if (!empty($duplicateItemCodes)) {
            return redirect('/menu-package-transaction')
                ->with('error', 'Duplicate item codes are not allowed in one package transaction: ' . implode(', ', $duplicateItemCodes) . '.')
                ->withInput();
        }

        if (empty($details)) {
            return redirect('/menu-package-transaction')->with('error', 'At least one package item is required.')->withInput();
        }

        if ($existingNofak && !$this->packageExists($existingNofak)) {
            return redirect('/menu-package-transaction')->with('error', 'Package transaction was not found.');
        }

        if ($existingNofak && $this->packageIsUsed($existingNofak)) {
            return redirect('/menu-package-transaction')->with('error', 'This package transaction is already used in guest transactions and cannot be updated.');
        }

        if ($this->hasDuplicatePackageCode($packageCode, $expired, $existingNofak)) {
            return redirect('/menu-package-transaction')
                ->with('error', 'The same package code and expired date already exist.')
                ->withInput();
        }

        $nominal = collect($details)->sum('amount');
        $now = Carbon::now();
        $savedNofak = $existingNofak;

        DB::transaction(function () use ($existingNofak, $packageCode, $nominal, $expired, $username, $details, $now, &$savedNofak) {
            $nofak = $existingNofak ?: $this->reservePackageNofak();
            $savedNofak = $nofak;

            if ($existingNofak) {
                DB::table('PackageD')->whereRaw('RTRIM(Nofak) = ?', [$nofak])->delete();

                DB::table('Package')
                    ->whereRaw('RTRIM(Nofak) = ?', [$nofak])
                    ->update([
                        'Meja' => $packageCode,
                        'JumlahRes' => $nominal,
                        'Expired' => $expired->format('Y-m-d'),
                        'UserName' => $username,
                    ]);
            } else {
                DB::table('Package')->insert([
                    'Nofak' => $nofak,
                    'KArea' => '02',
                    'strTgl' => $now->format('YmdHis'),
                    'Tgl' => $now->format('Y-m-d'),
                    'Jam' => $now->format('H:i:s'),
                    'Kode' => '',
                    'Meja' => $packageCode,
                    'JumlahRes' => $nominal,
                    'UserName' => $username,
                    'PPn' => 0,
                    'Pst' => ' ',
                    'Expired' => $expired->format('Y-m-d'),
                ]);
            }

            foreach ($details as $index => $detail) {
                DB::table('PackageD')->insert([
                    'Nofak' => $nofak,
                    'KodeBrg' => $detail['kode'],
                    'Qty' => $detail['qty'],
                    'Harga' => $detail['price'],
                    'Disc' => 0,
                    'Jumlah' => $detail['amount'],
                    'NoUrut' => $index + 1,
                ]);
            }
        });

        if ($existingNofak) {
            return redirect('/menu-package-transaction')->with('success', 'Package transaction updated successfully');
        }

        return redirect('/menu-package-transaction')->with('success', 'Package transaction saved successfully. Invoice: ' . $savedNofak);
    }

    private function extractDetails(Request $request): array
    {
        $codes = $request->input('ItemCode', []);
        $qtys = $request->input('Qty', []);
        $details = [];
        $stockItems = DB::table('StockPackage')
            ->selectRaw('RTRIM(KodeBrg) as KodeBrg, Hj')
            ->get()
            ->keyBy(fn ($item) => strtoupper(trim((string) $item->KodeBrg)));

        foreach ($codes as $index => $code) {
            $normalizedCode = strtoupper(trim((string) $code));

            if ($normalizedCode === '') {
                continue;
            }

            $stockItem = $stockItems->get($normalizedCode);

            if (!$stockItem) {
                continue;
            }

            $qty = (float) preg_replace('/[^\d.]/', '', (string) ($qtys[$index] ?? '1'));
            $qty = $qty > 0 ? $qty : 1;
            $price = (float) ($stockItem->Hj ?? 0);

            if ($price <= 0) {
                continue;
            }

            $details[] = [
                'kode' => $normalizedCode,
                'qty' => $qty,
                'price' => $price,
                'amount' => round($qty * $price, 2),
            ];
        }

        return $details;
    }

    private function findDuplicateItemCodes(Request $request): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($request->input('ItemCode', []) as $code) {
            $normalizedCode = strtoupper(trim((string) $code));

            if ($normalizedCode === '') {
                continue;
            }

            if (isset($seen[$normalizedCode])) {
                $duplicates[$normalizedCode] = $normalizedCode;
                continue;
            }

            $seen[$normalizedCode] = true;
        }

        return array_values($duplicates);
    }

    private function packageExists(string $nofak): bool
    {
        return DB::table('Package')
            ->whereRaw('RTRIM(Nofak) = ?', [trim($nofak)])
            ->exists();
    }

    private function packageIsUsed(string $nofak): bool
    {
        return DB::table('DATA2')
            ->whereRaw('RTRIM(Package) = ?', [trim($nofak)])
            ->exists();
    }

    private function hasDuplicatePackageCode(string $packageCode, Carbon $expired, ?string $existingNofak = null): bool
    {
        $query = DB::table('Package')
            ->whereRaw('RTRIM(Meja) = ?', [$packageCode])
            ->whereDate('Expired', $expired->format('Y-m-d'));

        if ($existingNofak) {
            $query->whereRaw('RTRIM(Nofak) <> ?', [trim($existingNofak)]);
        }

        return $query->exists();
    }

    private function previewPackageNofak(): string
    {
        $prefix = $this->getPackageNofakPrefix();

        $latest = DB::table('Package')
            ->selectRaw('MAX(RTRIM(Nofak)) as latest_nofak')
            ->whereRaw('RTRIM(Nofak) like ?', [$prefix . '%'])
            ->value('latest_nofak');

        $sequence = 1;

        if ($latest) {
            $sequence = ((int) substr(trim((string) $latest), -4)) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function reservePackageNofak(): string
    {
        $prefix = $this->getPackageNofakPrefix();

        $latest = DB::selectOne(
            "SELECT TOP 1 RTRIM(Nofak) AS Nofak FROM Package WITH (TABLOCKX, HOLDLOCK) WHERE RTRIM(Nofak) LIKE ? ORDER BY RTRIM(Nofak) DESC",
            [$prefix . '%']
        );

        $sequence = 1;

        if ($latest && !empty($latest->Nofak)) {
            $sequence = ((int) substr(trim((string) $latest->Nofak), -4)) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function getPackageNofakPrefix(): string
    {
        return Carbon::now()->format('Ym') . '9002';
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }
}