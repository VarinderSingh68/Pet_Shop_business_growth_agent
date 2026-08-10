<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE order_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                product_id INT UNSIGNED NULL,
                variant_id INT UNSIGNED NULL,
                product_name_snapshot VARCHAR(200) NOT NULL,
                variant_label_snapshot VARCHAR(150) NOT NULL,
                sku_snapshot VARCHAR(64) NOT NULL,
                unit_price_paise INT UNSIGNED NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                line_total_paise INT UNSIGNED NOT NULL,
                fulfilled_quantity INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                KEY order_items_order_id_index (order_id),
                KEY order_items_product_id_index (product_id),
                CONSTRAINT order_items_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT order_items_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
                CONSTRAINT order_items_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS order_items');
    }
};
