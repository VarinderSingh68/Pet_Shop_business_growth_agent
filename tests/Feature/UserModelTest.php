<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Database;
use App\Models\User;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    private int $roleId;

    protected function setUp(): void
    {
        $db = Database::instance();
        $db->query('DELETE FROM users');
        $db->query('DELETE FROM roles');

        $this->roleId = $db->insert('roles', [
            'name' => 'Customer',
            'slug' => 'customer-' . bin2hex(random_bytes(4)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function testHashPasswordProducesAVerifiableArgon2idHash(): void
    {
        $hash = User::hashPassword('correct-horse');

        $this->assertStringStartsWith('$argon2id$', $hash);
        $this->assertTrue(password_verify('correct-horse', $hash));
    }

    public function testVerifyPasswordReturnsFalseForGoogleOnlyAccount(): void
    {
        $user = ['password_hash' => null];

        $this->assertFalse(User::verifyPassword($user, 'anything'));
    }

    public function testFindByEmailIsCaseInsensitiveAndTrimsInput(): void
    {
        User::create([
            'role_id' => $this->roleId,
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'password_hash' => User::hashPassword('secret123'),
        ]);

        $found = User::findByEmail('  Priya@Example.com  ');

        $this->assertNotNull($found);
        $this->assertSame('Priya Sharma', $found['name']);
    }

    public function testFindByEmailReturnsNullWhenNoMatch(): void
    {
        $this->assertNull(User::findByEmail('nobody@example.com'));
    }
}
