<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const API_TOKEN_TTL_HOURS = 12;

    public function loginForm(Request $request)
    {
        return $this->respond($request, 'login', [], [
            'authenticated' => (bool) session('user'),
            'user' => session('user'),
            'role' => session('role'),
        ]);
    }

    public function login(Request $request)
    {
        $username = trim((string) $request->username);
        $user = DB::select("
            SELECT TOP 1 *
            FROM dbo.sandi
            WHERE RTRIM(Kode) = ?
        ", [$username]);

        if ($user) {
            $user = $user[0];

            if (trim($user->Passw) == trim($request->password)) {

                session([
                    'user' => trim($user->Kode),
                    'role' => trim($user->Nama)
                ]);

                return $this->respondAfterMutation($request, '/dashboard', 'Login berhasil.', [
                    'user' => trim((string) $user->Kode),
                    'role' => trim((string) $user->Nama),
                ]);
            }
        }

        return $this->respondError($request, 'Login failed', 401, [], '/', true);
    }

    public function logout(Request $request)
    {
        session()->flush();

        return $this->respondAfterMutation($request, '/', 'Logout berhasil.');
    }

    public function apiLogin(Request $request)
    {
        $username = trim((string) $request->input('username'));
        $password = trim((string) $request->input('password'));

        if ($username === '' || $password === '') {
            return $this->respondError($request, 'Username dan password wajib diisi.', 422);
        }

        $user = $this->findUserByKode($username);

        if (!$user || trim((string) $user->Passw) !== $password) {
            return $this->respondError($request, 'Login failed', 401);
        }

        $plainToken = Str::random(64);
        $tokenKey = $this->apiTokenCacheKey($plainToken);
        $now = now();
        $payload = [
            'user' => trim((string) $user->Kode),
            'role' => trim((string) $user->Nama),
            'issued_at' => $now->toIso8601String(),
            'expires_at' => $now->copy()->addHours(self::API_TOKEN_TTL_HOURS)->toIso8601String(),
        ];

        Cache::store('file')->put($tokenKey, $payload, now()->addHours(self::API_TOKEN_TTL_HOURS));

        return $this->respondAfterMutation($request, '/api/me', 'Login berhasil.', [
            'token_type' => 'Bearer',
            'access_token' => $plainToken,
            'expires_in' => self::API_TOKEN_TTL_HOURS * 3600,
            'user' => $payload['user'],
            'role' => $payload['role'],
        ], 201);
    }

    public function apiMe(Request $request)
    {
        return $this->respond($request, 'login', [], $request->attributes->get('api_user', []));
    }

    public function apiLogout(Request $request)
    {
        $token = (string) $request->bearerToken();

        if ($token !== '') {
            Cache::store('file')->forget($this->apiTokenCacheKey($token));
        }

        return $this->respondAfterMutation($request, '/api/login', 'Logout berhasil.');
    }

    private function findUserByKode(string $kode): ?object
    {
        $rows = DB::select("
            SELECT TOP 1 *
            FROM dbo.sandi
            WHERE RTRIM(Kode) = ?
        ", [$kode]);

        return $rows[0] ?? null;
    }

    private function apiTokenCacheKey(string $plainToken): string
    {
        return 'api_token:' . hash('sha256', $plainToken);
    }
}
