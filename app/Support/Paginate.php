<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * One shape for every paginated list.
 *
 * Laravel's paginator serialises its counters at the top level; an API Resource
 * collection nests them under `meta`. The front end was written against the
 * second and served the first, so `meta.last_page` was read off `undefined` and
 * every index screen in the application rendered as a blank page — while the
 * server tests, which only ever assert on the payload, stayed green.
 *
 * So the shape is pinned here and asserted by a test: `data`, the page `links`,
 * and the counters under `meta`. Nothing hands a raw paginator to Inertia.
 */
class Paginate
{
    /**
     * @template T
     *
     * @param  LengthAwarePaginator<int, T>  $paginator
     * @return array{data: list<T>, links: list<array{url: string|null, label: string, active: bool}>, meta: array{current_page: int, from: int|null, last_page: int, per_page: int, to: int|null, total: int}}
     */
    public static function shape(LengthAwarePaginator $paginator): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $paginator->toArray();

        return [
            'data' => array_values($paginator->items()),
            'links' => array_map(
                fn (array $link): array => [
                    'url' => $link['url'],
                    'label' => (string) $link['label'],
                    'active' => (bool) $link['active'],
                ],
                (array) ($payload['links'] ?? []),
            ),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
