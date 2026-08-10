<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE carts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                session_token VARCHAR(64) NULL COMMENT 'Identifies a guest cart via cookie when user_id is null',
                coupon_id INT UNSIGNED NULL,
                abandoned_notified_at DATETIME NULL COMMENT 'Last abandoned-cart reminder step sent, drives the Growth Agent recovery sequence',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY carts_user_id_index (user_id),
                KEY carts_session_token_index (session_token),
                CONSTRAINT carts_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS carts');
    }
};
