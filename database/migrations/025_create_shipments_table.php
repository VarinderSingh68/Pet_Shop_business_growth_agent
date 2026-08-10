<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE shipments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INT NOT NULL,
            carrier VARCHAR(100) NULL,
            tracking_number VARCHAR(100) NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','packed','shipped','delivered','returned')),
            shipped_at DATETIME NULL,
            delivered_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT shipments_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX shipments_order_id_index ON shipments(order_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS shipments');
    }
};
