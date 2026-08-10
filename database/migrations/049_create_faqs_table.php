<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE faqs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                question VARCHAR(255) NOT NULL,
                answer TEXT NOT NULL,
                `group` VARCHAR(100) NOT NULL DEFAULT 'general',
                sort_order INT NOT NULL DEFAULT 0,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS faqs');
    }
};
