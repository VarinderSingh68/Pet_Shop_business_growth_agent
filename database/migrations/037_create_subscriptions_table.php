<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE subscriptions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                product_id INT UNSIGNED NOT NULL,
                variant_id INT UNSIGNED NOT NULL,
                address_id INT UNSIGNED NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                interval_days INT UNSIGNED NOT NULL DEFAULT 30,
                status ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
                payment_method ENUM('reminder','cod') NOT NULL DEFAULT 'reminder',
                next_order_date DATE NOT NULL,
                last_order_date DATE NULL,
                paused_until DATE NULL,
                cancelled_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY subscriptions_user_id_index (user_id),
                KEY subscriptions_next_order_date_index (next_order_date),
                KEY subscriptions_status_index (status),
                CONSTRAINT subscriptions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT subscriptions_product_fk FOREIGN KEY (product_id) REFERENCES products(id),
                CONSTRAINT subscriptions_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id),
                CONSTRAINT subscriptions_address_fk FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS subscriptions');
    }
};
