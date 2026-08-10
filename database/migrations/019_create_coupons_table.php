<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(40) NOT NULL,
            description VARCHAR(200) NULL,
            type TEXT NOT NULL DEFAULT 'percent' CHECK (type IN ('percent','fixed')),
            value INT NOT NULL,
            min_order_paise INT NULL,
            max_discount_paise INT NULL,
            usage_limit INT NULL,
            usage_limit_per_customer INT NULL,
            usage_count INT NOT NULL DEFAULT 0,
            starts_at DATETIME NULL,
            expires_at DATETIME NULL,
            is_stackable INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            auto_generated INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX coupons_code_unique ON coupons(code)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS coupons');
    }
};
