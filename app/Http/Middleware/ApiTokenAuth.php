<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    private const TOKEN_TTL_HOURS = 12;

    public function handle(Request $request, Closure $next): Response
    {
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
}
