<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE refunds (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                payment_id INT UNSIGNED NOT NULL,
                amount_paise INT UNSIGNED NOT NULL,
                reason VARCHAR(255) NULL,
                status ENUM('pending','processed','failed') NOT NULL DEFAULT 'pending',
                gateway_refund_id VARCHAR(100) NULL,
                processed_by_user_id INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY refunds_order_id_index (order_id),
                CONSTRAINT refunds_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT refunds_payment_fk FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
                CONSTRAINT refunds_user_fk FOREIGN KEY (processed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS refunds');
    }
};
