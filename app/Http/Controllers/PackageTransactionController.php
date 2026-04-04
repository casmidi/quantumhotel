<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageTransactionController extends Controller
{
    public function index()
    {
        $items = DB::table('StockPackage')
            ->selectRaw("RTRIM(KodeBrg) as KodeBrg, RTRIM(NamaBrg) as NamaBrg, RTRIM(Kind) as Kind, Hj")
            ->orderBy('KodeBrg')
            ->get();

        $packages = DB::table('Package')
            ->selectRaw("RTRIM(Nofak) as Nofak, RTRIM(Meja) as Meja, JumlahRes, Expired")
            ->whereDate('Expired', '>=', Carbon::today()->format('Y-m-d'))
            ->orderByDesc('Nofak')
            ->get();

        $details = DB::table('PackageD')
            ->selectRaw("RTRIM(Nofak) as Nofak, RTRIM(KodeBrg) as KodeBrg, Qty, Harga, Jumlah, NoUrut")
            ->orderBy('NoUrut')
            ->get()
            ->groupBy('Nofak');

        $itemMap = $items->keyBy('KodeBrg');
        $packages = $packages->map(function ($package) use ($details, $itemMap) {
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

            return $package;
        });

        $summary = [
            'total' => $packages->count(),
            'nominal' => $packages->sum('JumlahRes'),
        ];

        return view('package.transaction', compact('items', 'packages', 'summary'));
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

        DB::transaction(function () use ($normalized) {
            DB::table('PackageD')->whereRaw('RTRIM(Nofak) = ?', [$normalized])->delete();
            DB::table('Package')->whereRaw('RTRIM(Nofak) = ?', [$normalized])->delete();
        });

        return redirect('/menu-package-transaction')->with('success', 'Package transaction deleted successfully');
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

        if ($this->hasDuplicatePackageCode($packageCode, $expired, $existingNofak)) {
            return redirect('/menu-package-transaction')
                ->with('error', 'The same package code and expired date already exist.')
                ->withInput();
        }

        $nominal = collect($details)->sum('amount');
        $now = Carbon::now();
        $nofak = $existingNofak ?: $this->generatePackageNofak();

        DB::transaction(function () use ($existingNofak, $nofak, $packageCode, $nominal, $expired, $username, $details, $now) {
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

        return redirect('/menu-package-transaction')->with('success', $existingNofak ? 'Package transaction updated successfully' : 'Package transaction saved successfully');
    }

    private function extractDetails(Request $request): array
    {
        $codes = $request->input('ItemCode', []);
        $qtys = $request->input('Qty', []);
        $prices = $request->input('Price', []);
        $details = [];
        $existingItemCodes = DB::table('StockPackage')
            ->selectRaw('RTRIM(KodeBrg) as KodeBrg')
            ->pluck('KodeBrg')
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->flip();

        foreach ($codes as $index => $code) {
            $normalizedCode = strtoupper(trim((string) $code));

            if ($normalizedCode === '') {
                continue;
            }

            if (!$existingItemCodes->has($normalizedCode)) {
                continue;
            }

            $qty = (float) preg_replace('/[^\d.]/', '', (string) ($qtys[$index] ?? '1'));
            $qty = $qty > 0 ? $qty : 1;
            $price = $this->normalizeMoney($prices[$index] ?? 0);

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

    private function generatePackageNofak(): string
    {
        $prefix = Carbon::now()->format('Ym') . '9002';

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

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }
}
