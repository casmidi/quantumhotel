<?php

namespace App\Http\Middleware;

use App\Support\ApiSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    private const TOKEN_TTL_HOURS = 12;

    public function handle(Request $request, Closure $next): Response
    {
        $settings = ApiSettings::current();

        if (($settings['auth_mode'] ?? 'basic') === 'basic') {
            return $this->handleBasicAuth($request, $next, $settings);
        }

        $token = (string) $request->bearerToken();

        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $cacheKey = 'api_token:' . hash('sha256', $token);
        $payload = Cache::store('file')->get($cacheKey);

        if (!is_array($payload) || empty($payload['user'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Cache::store('file')->put($cacheKey, $payload, now()->addHours(self::TOKEN_TTL_HOURS));
        $request->attributes->set('api_user', $payload);

        return $next($request);
    }

    private function handleBasicAuth(Request $request, Closure $next, array $settings): Response
    {
        $username = trim((string) $request->getUser());
        $password = (string) $request->getPassword();

        if ($username === '' || $password === '') {
            return $this->basicAuthFailed();
        }

        if (ApiSettings::hasStaticBasicCredential($settings)) {
            if (!ApiSettings::staticBasicCredentialMatches($settings, $username, $password)) {
                return $this->basicAuthFailed();
            }

            $request->attributes->set('api_user', [
                'user' => $username,
                'role' => 'API BASIC',
                'auth_mode' => 'basic',
            ]);

            return $next($request);
        }

        $user = DB::table('SANDI')
            ->whereRaw('RTRIM(Kode) = ?', [$username])
            ->first();

        if (!$user || trim((string) ($user->Passw ?? '')) !== trim($password)) {
            return $this->basicAuthFailed();
        }

        $request->attributes->set('api_user', [
            'user' => trim((string) $user->Kode),
            'role' => trim((string) ($user->Nama ?? '')),
            'auth_mode' => 'basic',
        ]);

        return $next($request);
    }

    private function basicAuthFailed(): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401)->header('WWW-Authenticate', 'Basic realm="Quantum API"');
    }
}
