<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\SearchService;

final class SearchController extends Controller
{
    public function __construct(private readonly SearchService $search = new SearchService())
    {
    }

    public function autocomplete(Request $request): void
    {
        $query = (string) $request->query('q', '');

        $results = $this->search->suggest($query);

        $this->json([
            'query' => $query,
            'results' => array_map(static fn (array $r) => [
                'name' => $r['name'],
                'url' => '/shop/' . $r['slug'],
                'price' => $r['min_price_paise'] !== null ? money((int) $r['min_price_paise']) : null,
            ], $results),
        ]);
    }
}
