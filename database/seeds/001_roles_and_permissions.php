<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

$roles = [
    ['slug' => 'owner', 'name' => 'Owner', 'description' => 'Full access to every part of the store and admin panel.'],
    ['slug' => 'manager', 'name' => 'Manager', 'description' => 'Runs day-to-day operations: catalogue, orders, marketing, customers.'],
    ['slug' => 'staff', 'name' => 'Staff', 'description' => 'Front-desk and fulfilment access: orders, appointments, inventory.'],
    ['slug' => 'developer', 'name' => 'Developer', 'description' => 'Everything Owner has, plus the developer tools panel.'],
    ['slug' => 'customer', 'name' => 'Customer', 'description' => 'Shopper account on the storefront.'],
];

$roleIds = [];
foreach ($roles as $role) {
    $existing = $db->selectOne('SELECT id FROM roles WHERE slug = :slug', ['slug' => $role['slug']]);
    if ($existing !== null) {
        $roleIds[$role['slug']] = (int) $existing['id'];
        continue;
    }
    $roleIds[$role['slug']] = $db->insert('roles', [
        'slug' => $role['slug'],
        'name' => $role['name'],
        'description' => $role['description'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

$permissionGroups = [
    'catalogue' => ['products.view', 'products.manage', 'inventory.manage'],
    'orders' => ['orders.view', 'orders.manage', 'refunds.manage'],
    'customers' => ['customers.view', 'customers.manage', 'customers.impersonate'],
    'services' => ['services.view', 'services.manage', 'appointments.manage'],
    'marketing' => ['marketing.view', 'marketing.manage', 'coupons.manage'],
    'content' => ['content.manage', 'media.manage'],
    'reports' => ['reports.view', 'reports.export'],
    'settings' => ['settings.manage', 'users.manage'],
    'developer' => ['developer.access'],
];

$permissionIds = [];
foreach ($permissionGroups as $group => $slugs) {
    foreach ($slugs as $slug) {
        $existing = $db->selectOne('SELECT id FROM permissions WHERE slug = :slug', ['slug' => $slug]);
        if ($existing !== null) {
            $permissionIds[$slug] = (int) $existing['id'];
            continue;
        }
        $permissionIds[$slug] = $db->insert('permissions', [
            'slug' => $slug,
            'name' => ucwords(str_replace('.', ' ', $slug)),
            'group' => $group,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

$grants = [
    'owner' => array_keys($permissionIds),
    'developer' => array_keys($permissionIds),
    'manager' => array_diff(array_keys($permissionIds), ['users.manage', 'developer.access']),
    'staff' => ['orders.view', 'orders.manage', 'customers.view', 'services.view', 'appointments.manage', 'inventory.manage'],
    'customer' => [],
];

foreach ($grants as $roleSlug => $permSlugs) {
    $roleId = $roleIds[$roleSlug];
    foreach ($permSlugs as $permSlug) {
        $permId = $permissionIds[$permSlug];
        $exists = $db->selectOne(
            'SELECT 1 AS x FROM role_permission WHERE role_id = :r AND permission_id = :p',
            ['r' => $roleId, 'p' => $permId],
        );
        if ($exists === null) {
            $db->insert('role_permission', ['role_id' => $roleId, 'permission_id' => $permId]);
        }
    }
}

echo "  Roles and permissions seeded.\n";

return $roleIds;
