<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE orders (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(30) NOT NULL,
                user_id INT UNSIGNED NULL,
                guest_email VARCHAR(191) NULL,
                guest_phone VARCHAR(20) NULL,
                status ENUM('pending_payment','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending_payment',
                currency VARCHAR(3) NOT NULL DEFAULT 'INR',
                subtotal_paise INT UNSIGNED NOT NULL,
                discount_paise INT UNSIGNED NOT NULL DEFAULT 0,
                shipping_paise INT UNSIGNED NOT NULL DEFAULT 0,
                tax_paise INT UNSIGNED NOT NULL DEFAULT 0,
                total_paise INT UNSIGNED NOT NULL,
                coupon_id INT UNSIGNED NULL,
                coupon_code_snapshot VARCHAR(40) NULL,
                payment_method ENUM('razorpay','cod') NOT NULL,
                payment_status ENUM('pending','paid','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
                shipping_full_name VARCHAR(150) NOT NULL,
                shipping_phone VARCHAR(20) NOT NULL,
                shipping_line1 VARCHAR(200) NOT NULL,
                shipping_line2 VARCHAR(200) NULL,
                shipping_city VARCHAR(100) NOT NULL,
                shipping_state VARCHAR(100) NOT NULL,
                shipping_postal_code VARCHAR(12) NOT NULL,
                shipping_country VARCHAR(2) NOT NULL DEFAULT 'IN',
                customer_notes VARCHAR(500) NULL,
                internal_notes VARCHAR(500) NULL,
                placed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME NULL,
                UNIQUE KEY orders_order_number_unique (order_number),
                KEY orders_user_id_index (user_id),
                KEY orders_status_index (status),
                KEY orders_placed_at_index (placed_at),
                CONSTRAINT orders_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT orders_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS orders');
    }
};
