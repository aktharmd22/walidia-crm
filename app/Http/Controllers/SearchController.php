<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Global search. Every source query runs through the same ownership scope as
 * the module it belongs to (D-017) — search is not a back door around
 * visibility, and a record the user cannot open never appears here.
 */
class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['hits' => []]);
        }

        return response()->json(['hits' => $this->hits($query, limit: 8)]);
    }

    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('q', ''));

        return Inertia::render('Search/Results', [
            'query' => $query,
            'hits' => mb_strlen($query) >= 2 ? $this->hits($query, limit: 50) : [],
        ]);
    }

    /**
     * @return list<array{type: string, label: string, subtitle: string|null, href: string}>
     */
    private function hits(string $query, int $limit): array
    {
        // Sources register here as their phase lands: clients, yachts,
        // bookings, listings, documents.
        return [];
    }
}
