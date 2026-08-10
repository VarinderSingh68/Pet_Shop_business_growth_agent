<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE coupon_redemptions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                coupon_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NULL,
                order_id INT UNSIGNED NOT NULL,
                discount_paise INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL,
                KEY coupon_redemptions_coupon_id_index (coupon_id),
                KEY coupon_redemptions_user_id_index (user_id),
                CONSTRAINT coupon_redemptions_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
                CONSTRAINT coupon_redemptions_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT coupon_redemptions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS coupon_redemptions');
    }
};
