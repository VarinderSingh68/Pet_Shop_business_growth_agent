<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Product;

final class CategoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/catalogue/categories', [
            'title' => 'Categories',
            'categories' => Category::db()->select('SELECT c.*, p.name AS parent_name FROM categories c LEFT JOIN categories p ON p.id = c.parent_id ORDER BY c.sort_order, c.name'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['name', 'parent_id', 'description', 'icon', 'sort_order']);

        $validator = Validator::make($data, ['name' => 'required|max:150']);
        if ($validator->fails()) {
            flash('error', 'Please enter a category name.');
            back();
        }

        Category::create([
            'name' => $data['name'],
            'slug' => slugify($data['name']),
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'icon' => !empty($data['icon']) ? $data['icon'] : 'other',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => 1,
        ]);

        flash('success', 'Category created.');
        $this->redirect('/admin/catalogue/categories');
    }

    public function update(Request $request, string $id): void
    {
        $data = $request->only(['name', 'parent_id', 'description', 'icon', 'sort_order', 'is_active']);

        Category::updateWhere((int) $id, [
            'name' => $data['name'],
            'slug' => slugify($data['name']),
            'parent_id' => !empty($data['parent_id']) && (int) $data['parent_id'] !== (int) $id ? (int) $data['parent_id'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'icon' => !empty($data['icon']) ? $data['icon'] : 'other',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Category updated.');
        $this->redirect('/admin/catalogue/categories');
    }

    public function destroy(Request $request, string $id): void
    {
        $productCount = Product::count(['category_id' => (int) $id]);
        if ($productCount > 0) {
            flash('error', "Can't delete — {$productCount} product(s) still use this category.");
            $this->redirect('/admin/catalogue/categories');
        }

        Category::destroy((int) $id);
        flash('success', 'Category deleted.');
        $this->redirect('/admin/catalogue/categories');
    }
}
