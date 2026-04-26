<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
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
            'masterMenus' => $this->masterMenus(),
            'positionDefaults' => $this->positionDefaults(),
            'allMenus' => $menus,
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
            return $this->respondError($request, 'User code already exists. The user cannot be saved.');
        }

        $nama = trim($validated['nama']);

        if ($this->cashierCodeExists($validated['kode_kasir'])) {
            return $this->respondError($request, 'Cashier code is already used by another user.');
        }

        try {
            DB::transaction(function () use ($kode, $nama, $validated, $request) {
                DB::table('SANDI')->insert([
                    'Kode' => $kode,
                    'Nama' => $nama,
                    'Passw' => trim($validated['password']),
                    'KodeKasir' => $validated['kode_kasir'],
                    'Sheet' => $validated['sheet'],
                    'Active' => $request->boolean('active') ? '*' : '',
                    'Lihat' => null,
                ]);

                foreach ($this->defaultMenusForPosition($nama) as $menu) {
                    DB::table('SANDI2')->insert([
                        'Kode' => $kode,
                        'Ket' => $menu['ket'],
                        'Kunci' => $menu['kunci'],
                        'Boleh' => '*',
                    ]);
                }
            });
        } catch (QueryException $exception) {
            if ($this->userExists($kode)) {
                return $this->respondError($request, 'User code already exists. The user cannot be saved.');
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
            '/settings/user-authorization?user=' . rawurlencode((string) $request->input('selected_user')) . '&tab=menus',
            'New master menu has been added.'
        );
    }

    public function updateMenu(Request $request)
    {
        $validated = $request->validate([
            'original_ket' => ['required', 'string', 'max:50'],
            'ket' => ['required', 'string', 'max:50'],
            'kunci' => ['nullable', 'string', 'max:255'],
        ]);

        $originalKet = trim($validated['original_ket']);
        $ket = trim($validated['ket']);
        $kunci = trim((string) ($validated['kunci'] ?? ''));
        $menuCode = trim(substr($originalKet, 0, 3));

        if (!$this->masterMenuExists($originalKet)) {
            return $this->respondError($request, 'Menu was not found in SANDI3.');
        }

        $duplicate = DB::table('SANDI3')
            ->whereRaw('RTRIM(Ket) = ?', [$ket])
            ->whereRaw('RTRIM(Ket) <> ?', [$originalKet])
            ->exists();

        if ($duplicate) {
            return $this->respondError($request, 'That menu description already exists in SANDI3.');
        }

        DB::transaction(function () use ($originalKet, $ket, $kunci, $menuCode) {
            DB::table('SANDI3')
                ->whereRaw('RTRIM(Ket) = ?', [$originalKet])
                ->update(['Ket' => $ket]);

            $sandi2Query = DB::table('SANDI2')
                ->whereRaw('RTRIM(Ket) = ?', [$originalKet]);

            if ($kunci !== '' && $menuCode !== '') {
                $sandi2Query->orWhere(function ($query) use ($kunci, $menuCode) {
                    $query->whereRaw('RTRIM(Kunci) = ?', [$kunci])
                        ->whereRaw('LEFT(LTRIM(RTRIM(Ket)), 3) = ?', [$menuCode]);
                });
            }

            $sandi2Query->update(['Ket' => $ket]);

            if (Schema::hasTable('position_menu_defaults')) {
                $defaultQuery = DB::table('position_menu_defaults')
                    ->whereRaw('RTRIM(ket) = ?', [$originalKet]);

                if ($kunci !== '' && $menuCode !== '') {
                    $defaultQuery->orWhere(function ($query) use ($kunci, $menuCode) {
                        $query->whereRaw('RTRIM(kunci) = ?', [$kunci])
                            ->whereRaw('LEFT(LTRIM(RTRIM(ket)), 3) = ?', [$menuCode]);
                    });
                }

                $defaultQuery->update([
                    'ket' => $ket,
                    'updated_at' => now(),
                ]);
            }
        });

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?user=' . rawurlencode((string) $request->input('selected_user')) . '&tab=menus',
            'Master menu description has been updated.'
        );
    }

    public function destroyMenu(Request $request)
    {
        $validated = $request->validate([
            'ket' => ['required', 'string', 'max:50'],
        ]);

        $ket = trim($validated['ket']);

        if (!$this->masterMenuExists($ket)) {
            return $this->respondError($request, 'Menu was not found in SANDI3.');
        }

        if ($this->masterMenuUsageCount($ket) > 0) {
            return $this->respondError($request, 'Menu cannot be deleted because it has already been used by a user.');
        }

        DB::table('SANDI3')
            ->whereRaw('RTRIM(Ket) = ?', [$ket])
            ->delete();

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?user=' . rawurlencode((string) $request->input('selected_user')) . '&tab=menus',
            'Master menu has been deleted.'
        );
    }

    public function storePositionDefaultMenus(Request $request)
    {
        $positions = $this->positions();
        $validated = $request->validate([
            'position' => ['required', 'string', Rule::in($positions)],
            'mode' => ['required', 'string', Rule::in(['selected', 'all'])],
            'menu_ket' => ['nullable', 'string', 'max:120'],
        ]);

        if (!Schema::hasTable('position_menu_defaults')) {
            return $this->respondError($request, 'Position default menu table is not available. Please run the database migration first.');
        }

        $position = trim($validated['position']);
        $mode = $validated['mode'];
        $selectedKet = trim((string) ($validated['menu_ket'] ?? ''));
        $menus = $this->menus();
        $existingMenus = DB::table('position_menu_defaults')
            ->selectRaw('RTRIM(ket) AS ket')
            ->whereRaw('RTRIM(position) = ?', [$position])
            ->get()
            ->map(fn ($row) => (string) $row->ket)
            ->flip();

        $menusToAdd = $menus
            ->reject(fn (array $menu) => $existingMenus->has($menu['ket']))
            ->when($mode === 'selected', fn (Collection $items) => $items->where('ket', $selectedKet))
            ->values();

        if ($mode === 'selected' && $selectedKet === '') {
            return $this->respondError($request, 'Please select a menu to add.');
        }

        if ($mode === 'selected' && $menus->where('ket', $selectedKet)->isEmpty()) {
            return $this->respondError($request, 'Selected menu was not found.');
        }

        if ($menusToAdd->isEmpty()) {
            return $this->respondError($request, 'No new menu can be added for this position.');
        }

        $sourceUser = strtoupper(trim((string) session('user')));

        DB::transaction(function () use ($position, $menusToAdd, $sourceUser) {
            foreach ($menusToAdd as $menu) {
                $exists = DB::table('position_menu_defaults')
                    ->whereRaw('RTRIM(position) = ?', [$position])
                    ->whereRaw('RTRIM(ket) = ?', [$menu['ket']])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('position_menu_defaults')->insert([
                    'position' => $position,
                    'ket' => $menu['ket'],
                    'kunci' => $menu['kunci'],
                    'source_user_code' => $sourceUser !== '' ? $sourceUser : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $message = $menusToAdd->count() . ' default menu has been added for ' . $position . '.';

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?tab=positions&position=' . rawurlencode($position),
            $message,
            $this->positionDefaultPanelPayload($position)
        );
    }

    public function applyPositionDefaultMenus(Request $request)
    {
        $positions = $this->positions();
        $validated = $request->validate([
            'position' => ['required', 'string', Rule::in($positions)],
        ]);

        if (!Schema::hasTable('position_menu_defaults')) {
            return $this->respondError($request, 'Position default menu table is not available. Please run the database migration first.');
        }

        $position = trim($validated['position']);
        $users = DB::table('SANDI')
            ->selectRaw('RTRIM(Kode) AS kode')
            ->whereRaw('RTRIM(Nama) = ?', [$position])
            ->get()
            ->map(fn ($row) => (string) $row->kode)
            ->filter()
            ->values();

        if ($users->isEmpty()) {
            return $this->respondError($request, 'No users are recorded for ' . $position . '.');
        }

        $menus = DB::table('position_menu_defaults')
            ->selectRaw('RTRIM(ket) AS ket, RTRIM(kunci) AS kunci')
            ->whereRaw('RTRIM(position) = ?', [$position])
            ->orderByRaw('RTRIM(ket)')
            ->get()
            ->map(fn ($row) => [
                'ket' => (string) $row->ket,
                'kunci' => (string) $row->kunci,
            ])
            ->filter(fn (array $menu) => $menu['ket'] !== '')
            ->values();

        if ($menus->isEmpty()) {
            return $this->respondError($request, 'No default menus are recorded for ' . $position . '.');
        }

        DB::transaction(function () use ($users, $menus) {
            foreach ($users as $kode) {
                foreach ($menus as $menu) {
                    $updated = DB::table('SANDI2')
                        ->whereRaw('RTRIM(Kode) = ?', [$kode])
                        ->whereRaw('RTRIM(Ket) = ?', [$menu['ket']])
                        ->update([
                            'Kunci' => $menu['kunci'],
                            'Boleh' => '*',
                        ]);

                    if ($updated === 0) {
                        DB::table('SANDI2')->insert([
                            'Kode' => $kode,
                            'Ket' => $menu['ket'],
                            'Kunci' => $menu['kunci'],
                            'Boleh' => '*',
                        ]);
                    }
                }
            }
        });

        $message = 'Default menus have been applied to ' . $users->count() . ' ' . $position . ' user.';

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?tab=positions&position=' . rawurlencode($position),
            $message,
            $this->positionDefaultPanelPayload($position)
        );
    }

    public function destroyPositionDefaultMenu(Request $request)
    {
        $positions = $this->positions();
        $validated = $request->validate([
            'position' => ['required', 'string', Rule::in($positions)],
            'ket' => ['required', 'string', 'max:120'],
        ]);

        if (!Schema::hasTable('position_menu_defaults')) {
            return $this->respondError($request, 'Position default menu table is not available. Please run the database migration first.');
        }

        $position = trim($validated['position']);
        $ket = trim($validated['ket']);

        $deleted = DB::table('position_menu_defaults')
            ->whereRaw('RTRIM(position) = ?', [$position])
            ->whereRaw('RTRIM(ket) = ?', [$ket])
            ->delete();

        if ($deleted === 0) {
            return $this->respondError($request, 'Default menu was not found for this position.');
        }

        $message = 'Default menu has been deleted from ' . $position . '.';

        return $this->respondAfterMutation(
            $request,
            '/settings/user-authorization?tab=positions&position=' . rawurlencode($position),
            $message,
            $this->positionDefaultPanelPayload($position)
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

    private function masterMenus(): Collection
    {
        return collect(DB::select("
            SELECT RTRIM(s3.Ket) AS ket,
                   RTRIM(s3.Kunci) AS kunci,
                   COUNT(s2.Ket) AS usage_count
            FROM dbo.SANDI3 s3
            LEFT JOIN dbo.SANDI2 s2 ON RTRIM(s2.Ket) = RTRIM(s3.Ket)
            GROUP BY RTRIM(s3.Ket), RTRIM(s3.Kunci)
            ORDER BY RTRIM(s3.Ket)
        "))->map(function ($row) {
            $ket = (string) $row->ket;

            return [
                'ket' => $ket,
                'kunci' => (string) $row->kunci,
                'code' => trim(substr($ket, 0, 3)),
                'label' => trim(substr($ket, 3)) ?: $ket,
                'usage_count' => (int) $row->usage_count,
            ];
        });
    }

    private function positionDefaults(): Collection
    {
        $positions = collect($this->positions())->mapWithKeys(fn ($position) => [
            $position => [
                'position' => $position,
                'user_count' => 0,
                'menus' => collect(),
            ],
        ]);

        $userCounts = collect(DB::select("
            SELECT RTRIM(Nama) AS position,
                   COUNT(*) AS user_count
            FROM dbo.SANDI
            GROUP BY RTRIM(Nama)
        "));

        foreach ($userCounts as $row) {
            $position = (string) $row->position;

            if (!$positions->has($position)) {
                $positions->put($position, [
                    'position' => $position,
                    'user_count' => 0,
                    'menus' => collect(),
                ]);
            }

            $current = $positions->get($position);
            $current['user_count'] = (int) $row->user_count;
            $positions->put($position, $current);
        }

        $allowedMenus = Schema::hasTable('position_menu_defaults')
            ? collect(DB::select("
                SELECT RTRIM(position) AS position,
                       RTRIM(ket) AS ket,
                       RTRIM(kunci) AS kunci,
                       RTRIM(source_user_code) AS source_user_code,
                       0 AS user_count
                FROM position_menu_defaults
                ORDER BY RTRIM(position), RTRIM(ket)
            "))
            : collect(DB::select("
                SELECT RTRIM(s.Nama) AS position,
                       RTRIM(s2.Ket) AS ket,
                       RTRIM(MAX(s2.Kunci)) AS kunci,
                       RTRIM(MIN(s.Kode)) AS source_user_code,
                       COUNT(DISTINCT s.Kode) AS user_count
                FROM dbo.SANDI s
                INNER JOIN dbo.SANDI2 s2 ON RTRIM(s2.Kode) = RTRIM(s.Kode)
                WHERE RTRIM(s2.Boleh) = '*'
                GROUP BY RTRIM(s.Nama), RTRIM(s2.Ket)
                ORDER BY RTRIM(s.Nama), RTRIM(s2.Ket)
            "));

        foreach ($allowedMenus as $row) {
            $position = (string) $row->position;

            if (!$positions->has($position)) {
                $positions->put($position, [
                    'position' => $position,
                    'user_count' => 0,
                    'menus' => collect(),
                ]);
            }

            $current = $positions->get($position);
            $ket = (string) $row->ket;
            $current['menus']->push([
                'ket' => $ket,
                'kunci' => (string) $row->kunci,
                'code' => trim(substr($ket, 0, 3)),
                'label' => trim(substr($ket, 3)) ?: $ket,
                'source_user_code' => (string) $row->source_user_code,
                'user_count' => (int) $row->user_count,
            ]);
            $positions->put($position, $current);
        }

        return $positions->values();
    }

    private function positionDefaultPanelPayload(string $positionName): array
    {
        $positionDefaults = $this->positionDefaults();
        $positionIndex = $positionDefaults->search(
            fn (array $position) => strtoupper(trim($position['position'])) === strtoupper(trim($positionName))
        );
        $positionIndex = $positionIndex === false ? 1 : $positionIndex + 1;
        $position = $positionDefaults->firstWhere('position', $positionName) ?? $positionDefaults->first();

        return [
            'position' => $positionName,
            'html' => view('settings.partials.position-default-panel', [
                'position' => $position,
                'allMenus' => $this->menus(),
                'positionIndex' => $positionIndex,
            ])->render(),
        ];
    }

    private function defaultMenusForPosition(string $position): Collection
    {
        if (!Schema::hasTable('position_menu_defaults')) {
            return collect();
        }

        return collect(DB::select("
            SELECT RTRIM(ket) AS ket,
                   RTRIM(kunci) AS kunci
            FROM position_menu_defaults
            WHERE RTRIM(position) = ?
            ORDER BY RTRIM(ket)
        ", [trim($position)]))->map(fn ($row) => [
            'ket' => (string) $row->ket,
            'kunci' => (string) $row->kunci,
        ])->filter(fn (array $menu) => $menu['ket'] !== '')->values();
    }

    private function masterMenuExists(string $ket): bool
    {
        return DB::table('SANDI3')
            ->whereRaw('RTRIM(Ket) = ?', [trim($ket)])
            ->exists();
    }

    private function masterMenuUsageCount(string $ket): int
    {
        return DB::table('SANDI2')
            ->whereRaw('RTRIM(Ket) = ?', [trim($ket)])
            ->count();
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
