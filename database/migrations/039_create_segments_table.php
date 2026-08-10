<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE segments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(50) NOT NULL,
                name VARCHAR(100) NOT NULL,
                description VARCHAR(255) NULL,
                is_dynamic TINYINT(1) NOT NULL DEFAULT 1,
                member_count INT UNSIGNED NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY segments_key_unique (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE segment_members (
                segment_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                added_at DATETIME NOT NULL,
                PRIMARY KEY (segment_id, user_id),
                CONSTRAINT segment_members_segment_fk FOREIGN KEY (segment_id) REFERENCES segments(id) ON DELETE CASCADE,
                CONSTRAINT segment_members_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS segment_members');
        $pdo->exec('DROP TABLE IF EXISTS segments');
    }
};
