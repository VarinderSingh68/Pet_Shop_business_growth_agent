<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE product_variants (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id INT UNSIGNED NOT NULL,
                sku VARCHAR(64) NOT NULL,
                label VARCHAR(150) NOT NULL COMMENT 'e.g. "5kg - Chicken"',
                price_paise INT UNSIGNED NOT NULL,
                compare_at_price_paise INT UNSIGNED NULL,
                stock_quantity INT NOT NULL DEFAULT 0,
                weight_grams INT UNSIGNED NULL,
                attributes JSON NULL COMMENT 'e.g. {"size":"5kg","flavour":"Chicken"}',
                low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME NULL,
                UNIQUE KEY product_variants_sku_unique (sku),
                KEY product_variants_product_id_index (product_id),
                CONSTRAINT product_variants_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS product_variants');
    }
};
