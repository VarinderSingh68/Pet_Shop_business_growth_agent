<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE payments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                gateway ENUM('razorpay','cod') NOT NULL,
                gateway_payment_id VARCHAR(100) NULL,
                gateway_order_id VARCHAR(100) NULL,
                amount_paise INT UNSIGNED NOT NULL,
                currency VARCHAR(3) NOT NULL DEFAULT 'INR',
                status ENUM('created','authorized','captured','failed','refunded') NOT NULL DEFAULT 'created',
                idempotency_key VARCHAR(100) NOT NULL,
                raw_payload JSON NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY payments_idempotency_key_unique (idempotency_key),
                KEY payments_order_id_index (order_id),
                KEY payments_gateway_payment_id_index (gateway_payment_id),
                CONSTRAINT payments_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS payments');
    }
};
