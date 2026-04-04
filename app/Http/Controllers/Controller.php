<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
}