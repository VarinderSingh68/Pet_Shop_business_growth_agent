<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE staff_members (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                name VARCHAR(150) NOT NULL,
                title VARCHAR(100) NULL,
                bio VARCHAR(500) NULL,
                photo_path VARCHAR(255) NULL,
                working_hours JSON NULL COMMENT 'Per-weekday start/end, e.g. {"mon":["10:00","18:00"], ...}',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY staff_members_user_id_index (user_id),
                CONSTRAINT staff_members_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS staff_members');
    }
};
