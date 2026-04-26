<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassTestController extends Controller
{
    private const API_TOKEN_TTL_HOURS = 12;

    public function index(Request $request)
    {
        return view('tools.class-test', $this->viewPayload($request));
    }

    public function fetch(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:120'],
        ]);

        $loginPayload = $this->loginWithBearerToken($validated['username'], $validated['password']);
        $loginJson = $loginPayload['json'];
        $token = (string) data_get($loginJson, 'data.access_token', '');

        if ($token === '') {
            return view('tools.class-test', $this->viewPayload($request, [
                'username' => $validated['username'],
                'message' => data_get($loginJson, 'message', 'Login API gagal.'),
                'status' => $loginPayload['status'],
                'loginJson' => $loginJson,
            ]));
        }

        $kelasPayload = $this->fetchKelasJson($token, data_get($loginJson, 'data', []));
        $kelasJson = $kelasPayload['json'];
        $rows = data_get($kelasJson, 'data.items', data_get($kelasJson, 'data', []));

        if (!is_array($rows)) {
            $rows = [];
        }

        return view('tools.class-test', $this->viewPayload($request, [
            'username' => $validated['username'],
            'token' => $token,
            'message' => data_get($kelasJson, 'message', data_get($loginJson, 'message', 'Data kelas berhasil diambil.')),
            'status' => $kelasPayload['status'],
            'kelasRows' => $rows,
            'loginJson' => $loginJson,
            'kelasJson' => $kelasJson,
        ]));
    }

    private function viewPayload(Request $request, array $overrides = []): array
    {
        return array_merge([
            'username' => old('username', (string) session('user', '')),
            'token' => '',
            'message' => '',
            'status' => null,
            'kelasRows' => [],
            'loginJson' => null,
            'kelasJson' => null,
        ], $overrides);
    }

    private function loginWithBearerToken(string $username, string $password): array
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return [
                'status' => 422,
                'json' => [
                    'success' => false,
                    'message' => 'Username dan password wajib diisi.',
                ],
            ];
        }

        $user = DB::table('SANDI')
            ->whereRaw('RTRIM(Kode) = ?', [$username])
            ->first();

        if (!$user || trim((string) $user->Passw) !== $password) {
            return [
                'status' => 401,
                'json' => [
                    'success' => false,
                    'message' => 'Login failed',
                ],
            ];
        }

        $plainToken = Str::random(64);
        $now = now();
        $payload = [
            'user' => trim((string) $user->Kode),
            'role' => trim((string) $user->Nama),
            'issued_at' => $now->toIso8601String(),
            'expires_at' => $now->copy()->addHours(self::API_TOKEN_TTL_HOURS)->toIso8601String(),
        ];

        Cache::store('file')->put(
            'api_token:' . hash('sha256', $plainToken),
            $payload,
            now()->addHours(self::API_TOKEN_TTL_HOURS)
        );

        return [
            'status' => 201,
            'json' => [
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'token_type' => 'Bearer',
                    'access_token' => $plainToken,
                    'expires_in' => self::API_TOKEN_TTL_HOURS * 3600,
                    'user' => $payload['user'],
                    'role' => $payload['role'],
                ],
            ],
        ];
    }

    private function fetchKelasJson(string $token, array $apiUser): array
    {
        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];

        $request = Request::create('/api/v1/kelas', 'GET', [], [], [], $server);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Authorization', 'Bearer ' . $token);
        $request->attributes->set('api_user', $apiUser);

        $response = app(KelasController::class)->index($request);
        $content = (string) $response->getContent();
        $json = json_decode($content, true);

        return [
            'status' => $response->getStatusCode(),
            'json' => is_array($json) ? $json : [
                'success' => false,
                'message' => $content,
            ],
        ];
    }
}
