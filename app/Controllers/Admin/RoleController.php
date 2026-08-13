<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Permission;
use App\Models\Role;

final class RoleController extends Controller
{
    /** Roles with unrestricted access by design — edited via seed data, not this screen. */
    private const LOCKED_ROLE_SLUGS = ['owner', 'developer'];

    public function index(Request $request): void
    {
        $this->guardOwnerOrManager();

        // The customer role never has admin access regardless of grants
        // (see AuthController::hasAdminAccess), so it's not shown here.
        $roles = array_values(array_filter(Role::all('name ASC'), static fn (array $r) => $r['slug'] !== 'customer'));
        $grantsByRole = [];
        foreach ($roles as $role) {
            $grantsByRole[$role['id']] = Role::permissionIds((int) $role['id']);
        }

        $this->view('admin/roles/index', [
            'title' => 'Roles & permissions',
            'roles' => $roles,
            'permissionGroups' => Permission::grouped(),
            'grantsByRole' => $grantsByRole,
            'lockedRoleSlugs' => self::LOCKED_ROLE_SLUGS,
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $this->guardOwnerOrManager();

        $role = Role::find((int) $id);
        if ($role === null || in_array($role['slug'], self::LOCKED_ROLE_SLUGS, true)) {
            flash('error', "This role's permissions can't be changed here.");
            $this->redirect('/admin/roles');
        }

        $permissionIds = array_map('intval', (array) $request->input('permissions', []));
        Role::syncPermissions((int) $id, $permissionIds);

        Database::instance()->insert('activity_logs', [
            'user_id' => auth()->id(),
            'action' => 'roles.permissions_updated',
            'subject_type' => 'role',
            'subject_id' => (int) $id,
            'description' => "Updated permissions for the {$role['name']} role",
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        flash('success', "Permissions updated for {$role['name']}.");
        $this->redirect('/admin/roles');
    }

    /**
     * Letting any staff-level account reach this screen would let e.g. a
     * 'staff' user grant their own role more permissions — restrict it
     * beyond the general 'admin' middleware gate.
     */
    private function guardOwnerOrManager(): void
    {
        if (!auth()->hasRole('owner', 'manager', 'developer')) {
            abort(403, "You don't have permission to manage roles.");
        }
    }
}
