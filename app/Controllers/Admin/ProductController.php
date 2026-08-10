<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\MediaService;

final class ProductController extends Controller
{
    public function __construct(private readonly MediaService $media = new MediaService())
    {
    }

    public function index(Request $request): void
    {
        $db = Database::instance();
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $where = ['p.deleted_at IS NULL'];
        $bindings = [];
        if ($search !== '') {
            $where[] = '(p.name LIKE :q OR EXISTS (SELECT 1 FROM product_variants v2 WHERE v2.product_id = p.id AND v2.sku LIKE :q))';
            $bindings['q'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'p.status = :status';
            $bindings['status'] = $status;
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) ($db->selectOne("SELECT COUNT(*) c FROM products p WHERE {$whereSql}", $bindings)['c'] ?? 0);

        $products = $db->select(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT COALESCE(SUM(stock_quantity),0) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS total_stock,
                    (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id AND v.deleted_at IS NULL) AS variant_count
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE {$whereSql}
             ORDER BY p.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $bindings,
        );

        $this->view('admin/catalogue/products/index', [
            'title' => 'Products',
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function bulkAction(Request $request): void
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        $action = (string) $request->input('bulk_action', '');

        if ($ids === [] || $action === '') {
            back();
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db = Database::instance();

        match ($action) {
            'activate' => $db->query("UPDATE products SET status = 'active', updated_at = ? WHERE id IN ({$placeholders})", [now(), ...$ids]),
            'archive' => $db->query("UPDATE products SET status = 'archived', updated_at = ? WHERE id IN ({$placeholders})", [now(), ...$ids]),
            'delete' => $db->query("UPDATE products SET deleted_at = ? WHERE id IN ({$placeholders})", [now(), ...$ids]),
            default => null,
        };

        flash('success', count($ids) . ' product(s) updated.');
        $this->redirect('/admin/catalogue');
    }

    public function create(Request $request): void
    {
        $this->view('admin/catalogue/products/form', [
            'title' => 'New product',
            'product' => null,
            'variants' => [],
            'images' => [],
            'categories' => Category::allActive(),
            'brands' => Brand::all('name ASC'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validatedProductData($request);

        $id = Product::create([
            ...$data,
            'slug' => $this->uniqueSlug($data['name']),
        ]);

        flash('success', 'Product created. Now add at least one variant.');
        $this->redirect('/admin/catalogue/products/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): void
    {
        $product = Product::find((int) $id);
        if ($product === null) {
            abort(404);
        }

        $this->view('admin/catalogue/products/form', [
            'title' => 'Edit ' . $product['name'],
            'product' => $product,
            'variants' => Product::variants((int) $id),
            'images' => Product::images((int) $id),
            'categories' => Category::allActive(),
            'brands' => Brand::all('name ASC'),
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $product = Product::find((int) $id);
        if ($product === null) {
            abort(404);
        }

        $data = $this->validatedProductData($request);
        $slug = $data['name'] !== $product['name'] ? $this->uniqueSlug($data['name'], (int) $id) : $product['slug'];

        Product::updateWhere((int) $id, [...$data, 'slug' => $slug]);

        flash('success', 'Product updated.');
        $this->redirect('/admin/catalogue/products/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): void
    {
        Product::destroy((int) $id);
        flash('success', 'Product deleted.');
        $this->redirect('/admin/catalogue');
    }

    // --- Variants -----------------------------------------------------

    public function storeVariant(Request $request, string $productId): void
    {
        $data = $request->only(['label', 'sku', 'price', 'compare_at_price', 'stock_quantity', 'weight_grams', 'low_stock_threshold']);

        $validator = Validator::make($data, [
            'label' => 'required|max:150',
            'sku' => 'required|max:64|unique:product_variants,sku',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the variant details.');
            back();
        }

        ProductVariant::create([
            'product_id' => (int) $productId,
            'sku' => strtoupper($data['sku']),
            'label' => $data['label'],
            'price_paise' => (int) round(((float) $data['price']) * 100),
            'compare_at_price_paise' => !empty($data['compare_at_price']) ? (int) round(((float) $data['compare_at_price']) * 100) : null,
            'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
            'weight_grams' => !empty($data['weight_grams']) ? (int) $data['weight_grams'] : null,
            'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? 5),
            'is_default' => Product::variants((int) $productId) === [] ? 1 : 0,
        ]);

        flash('success', 'Variant added.');
        $this->redirect('/admin/catalogue/products/' . $productId . '/edit');
    }

    public function updateVariant(Request $request, string $productId, string $variantId): void
    {
        $data = $request->only(['label', 'price', 'compare_at_price', 'stock_quantity', 'weight_grams', 'low_stock_threshold', 'is_default']);

        if (!empty($data['is_default'])) {
            Database::instance()->update('product_variants', ['is_default' => 0], 'product_id = :pid', ['pid' => $productId]);
        }

        ProductVariant::updateWhere((int) $variantId, [
            'label' => $data['label'],
            'price_paise' => (int) round(((float) $data['price']) * 100),
            'compare_at_price_paise' => !empty($data['compare_at_price']) ? (int) round(((float) $data['compare_at_price']) * 100) : null,
            'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
            'weight_grams' => !empty($data['weight_grams']) ? (int) $data['weight_grams'] : null,
            'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? 5),
            'is_default' => !empty($data['is_default']) ? 1 : 0,
        ]);

        flash('success', 'Variant updated.');
        $this->redirect('/admin/catalogue/products/' . $productId . '/edit');
    }

    public function destroyVariant(Request $request, string $productId, string $variantId): void
    {
        ProductVariant::destroy((int) $variantId);
        flash('success', 'Variant removed.');
        $this->redirect('/admin/catalogue/products/' . $productId . '/edit');
    }

    // --- Images ---------------------------------------------------------

    public function storeImage(Request $request, string $productId): void
    {
        $file = $request->file('image');
        if ($file === null) {
            flash('error', 'Please choose an image file.');
            back();
        }

        try {
            $path = $this->media->storeImage($file, 'products');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            back();
        }

        $maxOrder = Database::instance()->selectOne(
            'SELECT COALESCE(MAX(sort_order), -1) AS m FROM product_images WHERE product_id = :pid',
            ['pid' => $productId],
        );

        ProductImage::create([
            'product_id' => (int) $productId,
            'path' => $path,
            'alt_text' => (string) $request->input('alt_text', ''),
            'sort_order' => (int) $maxOrder['m'] + 1,
            'created_at' => now(),
        ]);

        flash('success', 'Image uploaded.');
        $this->redirect('/admin/catalogue/products/' . $productId . '/edit');
    }

    public function destroyImage(Request $request, string $productId, string $imageId): void
    {
        $image = ProductImage::find((int) $imageId);
        if ($image !== null) {
            $this->media->delete($image['path']);
            Database::instance()->delete('product_images', 'id = :id', ['id' => $imageId]);
        }

        flash('success', 'Image removed.');
        $this->redirect('/admin/catalogue/products/' . $productId . '/edit');
    }

    // --- Helpers ----------------------------------------------------------

    private function validatedProductData(Request $request): array
    {
        $data = $request->only([
            'name', 'category_id', 'brand_id', 'short_description', 'description',
            'pet_type', 'life_stage', 'status', 'feeding_grams_per_day', 'is_featured',
            'meta_title', 'meta_description',
        ]);

        $validator = Validator::make($data, [
            'name' => 'required|max:200',
            'category_id' => 'required|integer',
            'pet_type' => 'required|in:dog,cat,bird,fish,small_pet,other',
            'life_stage' => 'required|in:puppy_kitten,adult,senior,all',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the product details.');
            back();
        }

        return [
            'name' => $data['name'],
            'category_id' => (int) $data['category_id'],
            'brand_id' => !empty($data['brand_id']) ? (int) $data['brand_id'] : null,
            'short_description' => !empty($data['short_description']) ? $data['short_description'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'pet_type' => $data['pet_type'],
            'life_stage' => $data['life_stage'],
            'status' => in_array($data['status'] ?? '', ['draft', 'active', 'archived'], true) ? $data['status'] : 'draft',
            'feeding_grams_per_day' => !empty($data['feeding_grams_per_day']) ? (int) $data['feeding_grams_per_day'] : null,
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'meta_title' => !empty($data['meta_title']) ? $data['meta_title'] : null,
            'meta_description' => !empty($data['meta_description']) ? $data['meta_description'] : null,
        ];
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = slugify($name);
        $slug = $base;
        $i = 1;

        while (true) {
            $existing = Database::instance()->selectOne('SELECT id FROM products WHERE slug = :slug', ['slug' => $slug]);
            if ($existing === null || (int) $existing['id'] === $ignoreId) {
                return $slug;
            }
            $slug = $base . '-' . (++$i);
        }
    }
}
