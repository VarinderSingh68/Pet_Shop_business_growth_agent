<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE refunds (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INT NOT NULL,
            payment_id INT NOT NULL,
            amount_paise INT NOT NULL,
            reason VARCHAR(255) NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','processed','failed')),
            gateway_refund_id VARCHAR(100) NULL,
            processed_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT refunds_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT refunds_payment_fk FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
            CONSTRAINT refunds_user_fk FOREIGN KEY (processed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX refunds_order_id_index ON refunds(order_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS refunds');
    }
};
