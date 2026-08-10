<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INT NOT NULL,
            gateway TEXT NOT NULL CHECK (gateway IN ('razorpay','cod')),
            gateway_payment_id VARCHAR(100) NULL,
            gateway_order_id VARCHAR(100) NULL,
            amount_paise INT NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'INR',
            status TEXT NOT NULL DEFAULT 'created' CHECK (status IN ('created','authorized','captured','failed','refunded')),
            idempotency_key VARCHAR(100) NOT NULL,
            raw_payload JSON NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT payments_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX payments_idempotency_key_unique ON payments(idempotency_key)');
        $pdo->exec('CREATE INDEX payments_order_id_index ON payments(order_id)');
        $pdo->exec('CREATE INDEX payments_gateway_payment_id_index ON payments(gateway_payment_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS payments');
    }
};
