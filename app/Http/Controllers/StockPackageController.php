<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockPackageController extends Controller
{
    public function index()
    {
        $packages = DB::table('StockPackage')
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
            'total' => $packages->count(),
            'room' => $packages->where('Kind', 'ROOM')->count(),
            'restaurant' => $packages->where('Kind', 'RESTAURANT')->count(),
        ];

        return view('stock-package.index', [
            'packages' => $packages,
            'summary' => $summary,
            'processResult' => session('process_result'),
        ]);
    }

    public function store(Request $request)
    {
        $kodeBrg = $this->normalizeCode($request->KodeBrg);
        $namaBrg = strtoupper(trim((string) $request->NamaBrg));
        $satuan = strtoupper(trim((string) $request->Satuan ?: 'PAX'));
        $kind = strtoupper(trim((string) $request->Kind ?: 'ROOM'));
        $hj = $this->normalizeMoney($request->Hj);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        if ($kodeBrg === '' || $namaBrg === '' || $hj <= 0) {
            return redirect('/stock-package')
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

            return redirect('/stock-package')->with('success', 'Existing stock package updated successfully');
        }

        DB::table('StockPackage')->insert(array_merge($payload, [
            'KodeBrg' => $kodeBrg,
        ]));

        return redirect('/stock-package')->with('success', 'Data saved successfully');
    }

    public function update(Request $request, $kode)
    {
        $kodeBrg = $this->normalizeCode($kode);
        $namaBrg = strtoupper(trim((string) $request->NamaBrg));
        $satuan = strtoupper(trim((string) $request->Satuan ?: 'PAX'));
        $kind = strtoupper(trim((string) $request->Kind ?: 'ROOM'));
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

        return redirect('/stock-package')->with('success', 'Data updated successfully');
    }

    public function destroy($kode)
    {
        DB::table('StockPackage')
            ->whereRaw('RTRIM(KodeBrg) = ?', [$this->normalizeCode($kode)])
            ->delete();

        return redirect('/stock-package')->with('success', 'Data deleted successfully');
    }

    public function process(Request $request)
    {
        $nominal = $this->normalizeMoney($request->Nominal);
        $expired = $request->Expired ? Carbon::parse($request->Expired)->startOfDay() : null;
        $formula = trim((string) $request->Formula ?: 'GROUP');
        $packageCode = trim((string) $request->PackageCode);
        $packageCode = $packageCode !== '' ? strtoupper($packageCode) : $this->defaultPackageCode($nominal);
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        if ($nominal <= 0) {
            return redirect('/stock-package')
                ->with('error', 'Nominal package is not valid.')
                ->withInput();
        }

        if (!$expired) {
            return redirect('/stock-package')
                ->with('error', 'Expired date is required.')
                ->withInput();
        }

        if ($expired->lt(Carbon::today())) {
            return redirect('/stock-package')
                ->with('error', 'Expired date must be greater than or equal to today.')
                ->withInput();
        }

        $duplicate = DB::table('Package as p')
            ->join('PackageD as pd', 'p.Nofak', '=', 'pd.Nofak')
            ->where('p.JumlahRes', $nominal)
            ->whereDate('p.Expired', $expired->format('Y-m-d'))
            ->exists();

        if ($duplicate) {
            return redirect('/stock-package')
                ->with('error', 'A package with the same nominal and expired date already exists.')
                ->withInput();
        }

        [$roomAmount, $restaurantAmount] = $this->splitPackageAmounts($formula, $nominal);

        if ($roomAmount === null) {
            return redirect('/stock-package')
                ->with('error', $restaurantAmount)
                ->withInput();
        }

        $roomCode = $this->generateUniqueStockCode('R', $packageCode);
        $restaurantCode = $restaurantAmount > 0 ? $this->generateUniqueStockCode('M', $packageCode) : null;
        $nofak = $this->generatePackageNofak();
        $now = Carbon::now();
        $roomName = strtoupper($packageCode . ' ROOM');
        $restaurantName = strtoupper($packageCode . ' RESTAURANT');

        DB::transaction(function () use (
            $roomCode,
            $restaurantCode,
            $roomName,
            $restaurantName,
            $roomAmount,
            $restaurantAmount,
            $nofak,
            $now,
            $packageCode,
            $nominal,
            $expired,
            $username
        ) {
            DB::table('StockPackage')->insert([
                'KodeBrg' => $roomCode,
                'NamaBrg' => $roomName,
                'Satuan' => 'PAX',
                'Hb' => 0,
                'Hj' => $roomAmount,
                'Hpr' => 0,
                'HprAwal' => 0,
                'StockMin' => 0,
                'StockMax' => 0,
                'Stock' => 0,
                'UserName' => $username,
                'Tgl' => $now,
                'Kind' => 'ROOM',
            ]);

            if ($restaurantAmount > 0 && $restaurantCode) {
                DB::table('StockPackage')->insert([
                    'KodeBrg' => $restaurantCode,
                    'NamaBrg' => $restaurantName,
                    'Satuan' => 'PAX',
                    'Hb' => 0,
                    'Hj' => $restaurantAmount,
                    'Hpr' => 0,
                    'HprAwal' => 0,
                    'StockMin' => 0,
                    'StockMax' => 0,
                    'Stock' => 0,
                    'UserName' => $username,
                    'Tgl' => $now,
                    'Kind' => 'RESTAURANT',
                ]);
            }

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

            DB::table('PackageD')->insert([
                'Nofak' => $nofak,
                'KodeBrg' => $roomCode,
                'Qty' => 1,
                'Harga' => $roomAmount,
                'Disc' => 0,
                'Jumlah' => $roomAmount,
                'NoUrut' => 1,
            ]);

            if ($restaurantAmount > 0 && $restaurantCode) {
                DB::table('PackageD')->insert([
                    'Nofak' => $nofak,
                    'KodeBrg' => $restaurantCode,
                    'Qty' => 1,
                    'Harga' => $restaurantAmount,
                    'Disc' => 0,
                    'Jumlah' => $restaurantAmount,
                    'NoUrut' => 2,
                ]);
            }
        });

        return redirect('/stock-package')->with('success', 'Package automation completed successfully')->with('process_result', [
            'room_code' => $roomCode,
            'room_name' => $roomName,
            'room_amount' => $roomAmount,
            'restaurant_code' => $restaurantCode,
            'restaurant_name' => $restaurantAmount > 0 ? $restaurantName : '',
            'restaurant_amount' => $restaurantAmount,
            'package_code' => $packageCode,
            'expired' => $expired->format('Y-m-d'),
            'formula' => $formula,
            'nofak' => $nofak,
        ]);
    }

    private function splitPackageAmounts(string $formula, float $nominal): array
    {
        switch ($formula) {
            case 'GROUP':
                return [round($nominal * 0.6, 2), round($nominal * 0.4, 2)];

            case 'ROOM_ONLY':
                return [$nominal, 0];

            case 'OTA':
                if ($nominal <= 100000) {
                    return [null, 'Price room must be greater than 100,000.'];
                }

                return [$nominal - 100000, 100000];

            case 'EXECUTIVE':
                if ($nominal <= 200000) {
                    return [null, 'Price room must be greater than 200,000.'];
                }

                return [$nominal - 200000, 200000];

            default:
                return [null, 'Package formula is not valid.'];
        }
    }

    private function generateUniqueStockCode(string $prefix, string $packageCode): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', $packageCode));
        $base = $base !== '' ? $base : Carbon::now()->format('Ymd');
        $base = substr($base, 0, 10);

        for ($counter = 0; $counter < 9999; $counter++) {
            $suffix = $counter === 0 ? '' : str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $candidate = substr($prefix . $base . $suffix, 0, 16);

            $exists = DB::table('StockPackage')
                ->whereRaw('RTRIM(KodeBrg) = ?', [$candidate])
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        return $prefix . Carbon::now()->format('ymdHis');
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

    private function defaultPackageCode(float $nominal): string
    {
        return 'P- ' . number_format($nominal, 0, ',', '.');
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
