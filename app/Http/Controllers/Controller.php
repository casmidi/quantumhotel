<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

abstract class Controller
{
    protected function paginateCollection($items, int $perPage = 10, ?Request $request = null, array $options = []): LengthAwarePaginator
    {
        $request = $request ?: request();
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $page = max((int) $request->query('page', 1), 1);
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $collection->count(),
            $perPage,
            $page,
            array_merge([
                'path' => $request->url(),
                'query' => $request->query(),
            ], $options)
        );
    }

    protected function isApiRequest(?Request $request = null): bool
    {
        $request = $request ?: request();
        $accept = strtolower((string) $request->header('Accept', ''));

        return $request->expectsJson()
            || $request->wantsJson()
            || $request->is('api/*')
            || $request->ajax()
            || str_contains($accept, '/json');
    }

    protected function respond(Request $request, string $view, array $viewData = [], mixed $payload = null, int $status = 200): View|JsonResponse
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $this->normalizeResponseData($payload ?? $viewData),
            ], $status);
        }

        return view($view, $viewData);
    }

    protected function respondAfterMutation(Request $request, string $redirectTo, string $message, mixed $payload = null, int $status = 200): RedirectResponse|JsonResponse
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->normalizeResponseData($payload),
            ], $status);
        }

        return redirect($redirectTo)->with('success', $message);
    }

    protected function respondError(?Request $request, string $message, int $status = 422, array $data = [], ?string $redirectTo = null, bool $withInput = true): RedirectResponse|JsonResponse
    {
        $request = $request ?: request();

        if ($this->isApiRequest($request)) {
            return response()->json(array_merge([
                'success' => false,
                'message' => $message,
            ], $data), $status);
        }

        $redirect = $redirectTo ? redirect($redirectTo) : back();

        if ($withInput) {
            $redirect = $redirect->withInput();
        }

        return $redirect->with('error', $message);
    }

    protected function paginatorPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    protected function normalizeResponseData(mixed $payload): mixed
    {
        if ($payload instanceof LengthAwarePaginator) {
            return $this->paginatorPayload($payload);
        }

        if ($payload instanceof Collection) {
            return $payload->values();
        }

        if ($payload instanceof Arrayable) {
            return $payload->toArray();
        }

        return $payload;
    }

    protected function legacyIdSelect(string $table, string $alias = 'id'): string
    {
        $column = $this->legacyIdColumn($table);

        return $column ? sprintf('%s as %s', $column, $alias) : sprintf('NULL as %s', $alias);
    }

    protected function legacyIdColumn(string $table): ?string
    {
        static $columnCache = [];

        if (!array_key_exists($table, $columnCache)) {
            $columnCache[$table] = Schema::hasColumn($table, 'id')
                ? 'id'
                : (Schema::hasColumn($table, 'ID') ? 'ID' : null);
        }

        return $columnCache[$table];
    }
}
