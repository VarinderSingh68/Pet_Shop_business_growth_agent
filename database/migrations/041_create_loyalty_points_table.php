<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE loyalty_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            points INT NOT NULL,
            type TEXT NOT NULL CHECK (type IN ('earned','redeemed','expired','adjusted')),
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            note VARCHAR(255) NULL,
            expires_at DATE NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT loyalty_points_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX loyalty_points_user_id_index ON loyalty_points(user_id)');
        $pdo->exec('CREATE INDEX loyalty_points_expires_at_index ON loyalty_points(expires_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS loyalty_points');
    }
};
