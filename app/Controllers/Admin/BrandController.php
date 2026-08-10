<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Brand;
use App\Models\Product;

final class BrandController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/catalogue/brands', [
            'title' => 'Brands',
            'brands' => Brand::all('name ASC'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['name', 'description']);

        $validator = Validator::make($data, ['name' => 'required|max:150']);
        if ($validator->fails()) {
            flash('error', 'Please enter a brand name.');
            back();
        }

        Brand::create([
            'name' => $data['name'],
            'slug' => slugify($data['name']),
            'description' => !empty($data['description']) ? $data['description'] : null,
        ]);

        flash('success', 'Brand created.');
        $this->redirect('/admin/catalogue/brands');
    }

    public function update(Request $request, string $id): void
    {
        $data = $request->only(['name', 'description']);

        Brand::updateWhere((int) $id, [
            'name' => $data['name'],
            'slug' => slugify($data['name']),
            'description' => !empty($data['description']) ? $data['description'] : null,
        ]);

        flash('success', 'Brand updated.');
        $this->redirect('/admin/catalogue/brands');
    }

    public function destroy(Request $request, string $id): void
    {
        $productCount = Product::count(['brand_id' => (int) $id]);
        if ($productCount > 0) {
            flash('error', "Can't delete — {$productCount} product(s) still use this brand.");
            $this->redirect('/admin/catalogue/brands');
        }

        Brand::destroy((int) $id);
        flash('success', 'Brand deleted.');
        $this->redirect('/admin/catalogue/brands');
    }
}
