<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE coupon_redemptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            coupon_id INT NOT NULL,
            user_id INT NULL,
            order_id INT NOT NULL,
            discount_paise INT NOT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT coupon_redemptions_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
            CONSTRAINT coupon_redemptions_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT coupon_redemptions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX coupon_redemptions_coupon_id_index ON coupon_redemptions(coupon_id)');
        $pdo->exec('CREATE INDEX coupon_redemptions_user_id_index ON coupon_redemptions(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS coupon_redemptions');
    }
};
