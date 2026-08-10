<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number VARCHAR(30) NOT NULL,
            user_id INT NULL,
            guest_email VARCHAR(191) NULL,
            guest_phone VARCHAR(20) NULL,
            status TEXT NOT NULL DEFAULT 'pending_payment' CHECK (status IN ('pending_payment','confirmed','processing','shipped','delivered','cancelled','refunded')),
            currency VARCHAR(3) NOT NULL DEFAULT 'INR',
            subtotal_paise INT NOT NULL,
            discount_paise INT NOT NULL DEFAULT 0,
            shipping_paise INT NOT NULL DEFAULT 0,
            tax_paise INT NOT NULL DEFAULT 0,
            total_paise INT NOT NULL,
            coupon_id INT NULL,
            coupon_code_snapshot VARCHAR(40) NULL,
            payment_method TEXT NOT NULL CHECK (payment_method IN ('razorpay','cod')),
            payment_status TEXT NOT NULL DEFAULT 'pending' CHECK (payment_status IN ('pending','paid','failed','refunded','partially_refunded')),
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
            CONSTRAINT orders_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT orders_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX orders_order_number_unique ON orders(order_number)');
        $pdo->exec('CREATE INDEX orders_user_id_index ON orders(user_id)');
        $pdo->exec('CREATE INDEX orders_status_index ON orders(status)');
        $pdo->exec('CREATE INDEX orders_placed_at_index ON orders(placed_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS orders');
    }
};
