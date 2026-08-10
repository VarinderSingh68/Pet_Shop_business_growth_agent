<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INT NULL,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(160) NOT NULL,
            description VARCHAR(500) NULL,
            icon VARCHAR(50) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT categories_parent_fk FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX categories_slug_unique ON categories(slug)');
        $pdo->exec('CREATE INDEX categories_parent_id_index ON categories(parent_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS categories');
    }
};
