<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE reviews (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NULL,
                order_item_id INT UNSIGNED NULL,
                rating TINYINT UNSIGNED NOT NULL,
                title VARCHAR(150) NULL,
                body TEXT NULL,
                photo_paths JSON NULL,
                is_verified_purchase TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
                flagged_reason VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY reviews_product_id_index (product_id),
                KEY reviews_user_id_index (user_id),
                KEY reviews_status_index (status),
                CONSTRAINT reviews_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                CONSTRAINT reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS reviews');
    }
};
