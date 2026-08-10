<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE coupons (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(40) NOT NULL,
                description VARCHAR(200) NULL,
                type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
                value INT UNSIGNED NOT NULL COMMENT 'Percent 1-100, or paise for fixed',
                min_order_paise INT UNSIGNED NULL,
                max_discount_paise INT UNSIGNED NULL COMMENT 'Caps a percent discount',
                usage_limit INT UNSIGNED NULL COMMENT 'Total redemptions allowed, null = unlimited',
                usage_limit_per_customer INT UNSIGNED NULL,
                usage_count INT UNSIGNED NOT NULL DEFAULT 0,
                starts_at DATETIME NULL,
                expires_at DATETIME NULL,
                is_stackable TINYINT(1) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                auto_generated TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'True for Growth Agent-issued coupons (e.g. cart recovery)',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY coupons_code_unique (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS coupons');
    }
};
