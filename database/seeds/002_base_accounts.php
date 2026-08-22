<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\User;

$db = Database::instance();

function seed_account(Database $db, array $roleIds, string $roleSlug, string $name, string $email, string $password): void
{
    $existing = $db->selectOne('SELECT id FROM users WHERE email = :e', ['e' => $email]);
    if ($existing !== null) {
        echo "  {$roleSlug} account already exists ({$email}) — skipped.\n";
        return;
    }

    $db->insert('users', [
        'role_id' => $roleIds[$roleSlug],
        'name' => $name,
        'email' => $email,
        'password_hash' => User::hashPassword($password),
        'is_active' => 1,
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "  Created {$roleSlug}: {$email} / {$password}\n";
}

$genPassword = static fn () => 'Ht' . bin2hex(random_bytes(4)) . '!9';

$managerPassword = $genPassword();
$developerPassword = $genPassword();
$riderPassword = $genPassword();

seed_account($db, $roleIds, 'owner', 'Store Owner', 'admin@gmail.com', 'admin123');
seed_account($db, $roleIds, 'manager', 'Store Manager', 'manager@happytails.test', $managerPassword);
seed_account($db, $roleIds, 'developer', 'Dev Account', 'developer@happytails.test', $developerPassword);
// Demo rider account for the delivery Android app — sign in with these in
// the app's login screen. Add more via Admin -> Users, role "Delivery Partner".
seed_account($db, $roleIds, 'delivery', 'Demo Rider', 'rider@happytails.test', $riderPassword);

echo "\n  ── Save these credentials now, they will not be shown again ──\n";
