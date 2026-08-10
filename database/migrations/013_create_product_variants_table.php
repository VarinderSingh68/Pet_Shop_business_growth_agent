<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE product_variants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INT NOT NULL,
            sku VARCHAR(64) NOT NULL,
            label VARCHAR(150) NOT NULL,
            price_paise INT NOT NULL,
            compare_at_price_paise INT NULL,
            stock_quantity INT NOT NULL DEFAULT 0,
            weight_grams INT NULL,
            attributes JSON NULL,
            low_stock_threshold INT NOT NULL DEFAULT 5,
            is_default INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME NULL,
            CONSTRAINT product_variants_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX product_variants_sku_unique ON product_variants(sku)');
        $pdo->exec('CREATE INDEX product_variants_product_id_index ON product_variants(product_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS product_variants');
    }
};
