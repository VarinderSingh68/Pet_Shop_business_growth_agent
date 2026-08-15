<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Supplier;

final class SupplierController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/inventory/suppliers', [
            'title' => 'Suppliers',
            'suppliers' => Supplier::all('name ASC'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['name', 'contact_name', 'email', 'phone', 'address', 'notes']);

        $validator = Validator::make($data, ['name' => 'required|max:150']);
        if ($validator->fails()) {
            flash('error', 'Please enter a supplier name.');
            back();
        }

        Supplier::create([
            'name' => $data['name'],
            'contact_name' => !empty($data['contact_name']) ? $data['contact_name'] : null,
            'email' => !empty($data['email']) ? $data['email'] : null,
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'address' => !empty($data['address']) ? $data['address'] : null,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'is_active' => 1,
        ]);

        flash('success', 'Supplier added.');
        $this->redirect('/admin/inventory/suppliers');
    }

    public function update(Request $request, string $id): void
    {
        $data = $request->only(['name', 'contact_name', 'email', 'phone', 'address', 'notes', 'is_active']);

        Supplier::updateWhere((int) $id, [
            'name' => $data['name'],
            'contact_name' => !empty($data['contact_name']) ? $data['contact_name'] : null,
            'email' => !empty($data['email']) ? $data['email'] : null,
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'address' => !empty($data['address']) ? $data['address'] : null,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Supplier updated.');
        $this->redirect('/admin/inventory/suppliers');
    }

    public function destroy(Request $request, string $id): void
    {
        $poCount = (int) (Database::instance()->selectOne(
            'SELECT COUNT(*) c FROM purchase_orders WHERE supplier_id = :id',
            ['id' => $id],
        )['c'] ?? 0);

        if ($poCount > 0) {
            flash('error', "Can't delete — {$poCount} purchase order(s) reference this supplier.");
            $this->redirect('/admin/inventory/suppliers');
        }

        Database::instance()->delete('suppliers', 'id = :id', ['id' => $id]);
        flash('success', 'Supplier deleted.');
        $this->redirect('/admin/inventory/suppliers');
    }
}
