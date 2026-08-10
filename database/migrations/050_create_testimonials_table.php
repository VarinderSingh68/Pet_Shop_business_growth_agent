<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE testimonials (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name VARCHAR(150) NOT NULL,
            pet_description VARCHAR(150) NULL,
            quote VARCHAR(500) NOT NULL,
            rating INTEGER NOT NULL DEFAULT 5,
            is_published INTEGER NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS testimonials');
    }
};
