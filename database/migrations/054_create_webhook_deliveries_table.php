<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE webhook_deliveries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            source VARCHAR(50) NOT NULL DEFAULT 'razorpay',
            event_type VARCHAR(100) NULL,
            signature_valid INTEGER NOT NULL DEFAULT 0,
            payload TEXT NOT NULL,
            headers JSON NULL,
            outcome TEXT NOT NULL CHECK (outcome IN ('success','failed')),
            error VARCHAR(500) NULL,
            created_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX webhook_deliveries_source_index ON webhook_deliveries(source)');
        $pdo->exec('CREATE INDEX webhook_deliveries_created_at_index ON webhook_deliveries(created_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS webhook_deliveries');
    }
};
