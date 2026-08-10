<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            variant_id INT NOT NULL,
            address_id INT NULL,
            quantity INT NOT NULL DEFAULT 1,
            interval_days INT NOT NULL DEFAULT 30,
            status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','paused','cancelled')),
            payment_method TEXT NOT NULL DEFAULT 'reminder' CHECK (payment_method IN ('reminder','cod')),
            next_order_date DATE NOT NULL,
            last_order_date DATE NULL,
            paused_until DATE NULL,
            cancelled_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT subscriptions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT subscriptions_product_fk FOREIGN KEY (product_id) REFERENCES products(id),
            CONSTRAINT subscriptions_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id),
            CONSTRAINT subscriptions_address_fk FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX subscriptions_user_id_index ON subscriptions(user_id)');
        $pdo->exec('CREATE INDEX subscriptions_next_order_date_index ON subscriptions(next_order_date)');
        $pdo->exec('CREATE INDEX subscriptions_status_index ON subscriptions(status)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS subscriptions');
    }
};
