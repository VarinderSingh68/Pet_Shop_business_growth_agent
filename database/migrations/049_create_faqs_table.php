<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE faqs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question VARCHAR(255) NOT NULL,
            answer TEXT NOT NULL,
            `group` VARCHAR(100) NOT NULL DEFAULT 'general',
            sort_order INT NOT NULL DEFAULT 0,
            is_published INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS faqs');
    }
};
