<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            contact_name VARCHAR(150) NULL,
            email VARCHAR(191) NULL,
            phone VARCHAR(20) NULL,
            address VARCHAR(255) NULL,
            notes VARCHAR(500) NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS suppliers');
    }
};
