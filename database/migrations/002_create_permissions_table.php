<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            `group` VARCHAR(100) NOT NULL DEFAULT 'general',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX permissions_slug_unique ON permissions(slug)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS permissions');
    }
};
