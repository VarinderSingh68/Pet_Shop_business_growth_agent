<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CatalogueService;
use App\Services\Growth\UpsellService;

final class CatalogueController extends Controller
{
    public function __construct(
        private readonly CatalogueService $catalogue = new CatalogueService(),
        private readonly UpsellService $upsell = new UpsellService(),
    ) {
    }

    public function index(Request $request): void
    {
        $filters = [
            'category' => $request->query('category'),
            'brand' => array_filter((array) $request->query('brand', [])),
            'pet' => $request->query('pet'),
            'stage' => $request->query('stage'),
            'price_min' => $request->query('price_min'),
            'price_max' => $request->query('price_max'),
            'rating' => $request->query('rating'),
            'in_stock' => $request->query('in_stock'),
            'q' => $request->query('q'),
            'sort' => $request->query('sort'),
            'page' => $request->query('page', 1),
        ];

        $result = $this->catalogue->search($filters);

        $this->view('site/catalogue/index', [
            'title' => !empty($filters['q']) ? 'Search: ' . $filters['q'] : 'Shop',
            'result' => $result,
            'filters' => $filters,
            'categories' => Category::allActive(),
            'brands' => Brand::all('name ASC'),
        ]);
    }

    public function show(Request $request, string $slug): void
    {
        $product = Product::findBySlug($slug);

        if ($product === null) {
            abort(404);
        }

        $variants = Product::variants((int) $product['id']);
        $images = Product::images((int) $product['id']);
        $reviews = Product::approvedReviews((int) $product['id']);
        $userId = App::auth()->id();

        $frequentlyBoughtWith = $this->upsell->frequentlyBoughtWith((int) $product['id']);
        $related = $frequentlyBoughtWith !== [] ? $frequentlyBoughtWith : Product::relatedTo($product);

        $this->view('site/catalogue/show', [
            'title' => $product['name'],
            'description' => $product['short_description'],
            'product' => $product,
            'variants' => $variants,
            'images' => $images,
            'reviews' => $reviews,
            'related' => $related,
            'relatedIsCoPurchase' => $frequentlyBoughtWith !== [],
            'isWishlisted' => $userId !== null && Wishlist::has($userId, (int) $product['id']),
        ]);
    }
}
