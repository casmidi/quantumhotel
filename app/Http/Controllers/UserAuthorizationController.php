<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class UserAuthorizationController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->users();
        $selectedKode = strtoupper(trim((string) $request->query('user', $users->first()['kode'] ?? '')));
        $selectedUser = $users->firstWhere('kode', $selectedKode);
        $menus = $this->menus();
        $permissions = $selectedKode !== '' ? $this->permissions($selectedKode) : collect();
        $permissionRows = $menus->map(function (array $menu) use ($permissions) {
            $menu['allowed'] = $permissions->get($menu['ket'], '') === '*';
            return $menu;
        });

        return $this->respond($request, 'settings.user-authorization', [
            'users' => $users,
            'selectedKode' => $selectedKode,
            'selectedUser' => $selectedUser,
            'permissionRows' => $permissionRows,
            'allowedCount' => $permissionRows->where('allowed', true)->count(),
            'positions' => $this->positions(),
            'suggestedCashierCode' => $this->suggestCashierCode(),
        ], [
            'users' => $users,
            'selected_user' => $selectedUser,
            'permissions' => $permissionRows,
        ]);
    }

    public function update(Request $request)
    {
        $kode = strtoupper(trim((string) $request->input('kode')));
        $allowedMenus = collect((array) $request->input('permissions', []))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($kode === '') {
            return $this->respondError($request, 'Please select a user first.');
        }

        if (!$this->userExists($kode)) {
            return $this->respondError($request, 'User was not found.');
        }

        $menus = $this->menus();
        $validMenuKeys = $menus->pluck('ket');
        $invalidCount = $allowedMenus->diff($validMenuKeys)->count();

        if ($invalidCount > 0) {
            return $this->respondError($request, 'Some selected menus are invalid. Refresh the page and try again.');
        }

        DB::transaction(function () use ($kode, $allowedMenus, $menus) {
            foreach ($menus as $menu) {
                $isAllowed = $allowedMenus->contains($menu['ket']);
                $payload = [
                    'Kode' => $kode,
                    'Ket' => $menu['ket'],
                    'Kunci' => $menu['kunci'],
                    'Boleh' => $isAllowed ? '*' : '',
                ];

                $updated = DB::table('SANDI2')
                    ->whereRaw('RTRIM(Kode) = ?', [$kode])
                    ->whereRaw('RTRIM(Ket) = ?', [$menu['ket']])
                    ->update([
                        'Kunci' => $payload['Kunci'],
                        'Boleh' => $payload['Boleh'],
                    ]);

                if ($updated === 0) {
                    DB::table('SANDI2')->insert($payload);
                }
            }
        });

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?user=' . rawurlencode($kode),
            'User authorization has been saved.'
        );
    }

    public function storeUser(Request $request)
    {
        $positions = $this->positions();
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', Rule::in($positions)],
            'password' => ['required', 'string', 'max:30'],
            'kode_kasir' => ['required', 'digits:4'],
            'sheet' => ['required', 'string', Rule::in(['I', 'II', 'III', 'IV'])],
            'active' => ['nullable', 'boolean'],
        ]);

        $kode = strtoupper(trim($validated['kode']));

        if ($this->userExists($kode)) {
            return $this->respondError($request, 'User code already exists.');
        }

        $nama = trim($validated['nama']);

        if ($this->cashierCodeExists($validated['kode_kasir'])) {
            return $this->respondError($request, 'Cashier code is already used by another user.');
        }

        try {
            DB::table('SANDI')->insert([
                'Kode' => $kode,
                'Nama' => $nama,
                'Passw' => trim($validated['password']),
                'KodeKasir' => $validated['kode_kasir'],
                'Sheet' => $validated['sheet'],
                'Active' => $request->boolean('active') ? '*' : '',
                'Lihat' => null,
            ]);
        } catch (QueryException $exception) {
            if ($this->userExists($kode)) {
                return $this->respondError($request, 'User code already exists.');
            }

            if ($this->cashierCodeExists($validated['kode_kasir'])) {
                return $this->respondError($request, 'Cashier code is already used by another user.');
            }

            throw $exception;
        }

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?user=' . rawurlencode($kode),
            'New user has been added.'
        );
    }

    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'ket' => ['required', 'string', 'max:50'],
            'kunci' => ['required', 'string', 'max:50'],
        ]);

        $ket = trim($validated['ket']);
        $kunci = trim($validated['kunci']);

        $exists = DB::table('SANDI3')
            ->whereRaw('RTRIM(Ket) = ?', [$ket])
            ->exists();

        if ($exists) {
            return $this->respondError($request, 'That menu already exists in SANDI3.');
        }

        DB::table('SANDI3')->insert([
            'Ket' => $ket,
            'Kunci' => $kunci,
        ]);

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?user=' . rawurlencode((string) $request->input('selected_user')),
            'New master menu has been added.'
        );
    }

    private function users(): Collection
    {
        return collect(DB::select("
            SELECT RTRIM(Kode) AS kode,
                   RTRIM(Nama) AS nama,
                   RTRIM(KodeKasir) AS kode_kasir,
                   RTRIM(Sheet) AS sheet,
                   RTRIM(Active) AS active
            FROM dbo.SANDI
            ORDER BY RTRIM(Kode)
        "))->map(fn ($row) => [
            'kode' => (string) $row->kode,
            'nama' => (string) $row->nama,
            'kode_kasir' => (string) $row->kode_kasir,
            'sheet' => (string) $row->sheet,
            'active' => trim((string) $row->active) === '*',
        ]);
    }

    private function positions(): array
    {
        return [
            'OWNER',
            'MANAGER',
            'KEUANGAN',
            'SUPERVISOR',
            'RESEPSIONIS',
            'HOUSE KEEPING',
            'TEKNISI',
            'KASIR',
            'KEPALA GUDANG',
            'STAFF GUDANG',
            'ADMINISTRASI',
            'F&B',
        ];
    }

    private function menus(): Collection
    {
        $legacyMenus = collect(DB::select("
            SELECT RTRIM(Ket) AS ket,
                   RTRIM(Kunci) AS kunci
            FROM dbo.SANDI3
            ORDER BY RTRIM(Ket)
        "))->map(function ($row) {
            $ket = (string) $row->ket;

            return [
                'ket' => $ket,
                'kunci' => (string) $row->kunci,
                'code' => trim(substr($ket, 0, 3)),
                'label' => trim(substr($ket, 3)) ?: $ket,
            ];
        });

        $applicationMenus = collect([
            [
                'ket' => 'Q01 Hotel Branding',
                'kunci' => 'route.settings.hotel-branding',
                'code' => 'Q01',
                'label' => 'Hotel Branding',
            ],
            [
                'ket' => 'Q02 Synchronise',
                'kunci' => 'route.synchronise',
                'code' => 'Q02',
                'label' => 'Synchronise',
            ],
        ]);

        $existingKeys = $legacyMenus->pluck('ket')->flip();

        return $legacyMenus
            ->concat($applicationMenus->reject(fn ($menu) => $existingKeys->has($menu['ket'])))
            ->sortBy('ket')
            ->values();
    }

    private function permissions(string $kode): Collection
    {
        return collect(DB::select("
            SELECT RTRIM(Ket) AS ket,
                   RTRIM(Boleh) AS boleh
            FROM dbo.SANDI2
            WHERE RTRIM(Kode) = ?
        ", [$kode]))->mapWithKeys(fn ($row) => [
            (string) $row->ket => trim((string) $row->boleh),
        ]);
    }

    private function userExists(string $kode): bool
    {
        return DB::table('SANDI')
            ->whereRaw('UPPER(LTRIM(RTRIM(Kode))) = ?', [strtoupper(trim($kode))])
            ->exists();
    }

    private function cashierCodeExists(string $kodeKasir): bool
    {
        return DB::table('SANDI')
            ->whereRaw('LTRIM(RTRIM(KodeKasir)) = ?', [trim($kodeKasir)])
            ->exists();
    }

    private function suggestCashierCode(): string
    {
        $usedCodes = DB::table('SANDI')
            ->selectRaw('RTRIM(KodeKasir) AS kode_kasir')
            ->whereNotNull('KodeKasir')
            ->get()
            ->map(fn ($row) => trim((string) $row->kode_kasir))
            ->filter(fn ($code) => preg_match('/^\d{4}$/', $code))
            ->flip();

        for ($code = 1; $code <= 9999; $code++) {
            $candidate = str_pad((string) $code, 4, '0', STR_PAD_LEFT);

            if (!$usedCodes->has($candidate)) {
                return $candidate;
            }
        }

        return '';
    }
}
