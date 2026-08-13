<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Database;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Database::instance()->query('DELETE FROM categories');
    }

    public function testInsertSelectUpdateDeleteRoundTrip(): void
    {
        $db = Database::instance();

        $id = $db->insert('categories', [
            'name' => 'Dog Food',
            'slug' => 'dog-food',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertGreaterThan(0, $id);

        $row = $db->selectOne('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
        $this->assertNotNull($row);
        $this->assertSame('Dog Food', $row['name']);

        $db->update('categories', ['name' => 'Dog Treats'], 'id = :id', ['id' => $id]);
        $row = $db->selectOne('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
        $this->assertSame('Dog Treats', $row['name']);

        $deleted = $db->delete('categories', 'id = :id', ['id' => $id]);
        $this->assertSame(1, $deleted);
        $this->assertNull($db->selectOne('SELECT * FROM categories WHERE id = :id', ['id' => $id]));
    }

    public function testTransactionRollsBackOnException(): void
    {
        $db = Database::instance();

        try {
            $db->transaction(static function (Database $db): void {
                $db->insert('categories', [
                    'name' => 'Temp',
                    'slug' => 'temp-category',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                throw new \RuntimeException('force rollback');
            });
            $this->fail('Expected exception was not thrown.');
        } catch (\RuntimeException) {
            // expected
        }

        $row = $db->selectOne('SELECT * FROM categories WHERE slug = :s', ['s' => 'temp-category']);
        $this->assertNull($row);
    }
}
