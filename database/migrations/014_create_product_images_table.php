<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INT NOT NULL,
            variant_id INT NULL,
            path VARCHAR(255) NOT NULL,
            alt_text VARCHAR(200) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            CONSTRAINT product_images_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT product_images_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX product_images_product_id_index ON product_images(product_id)');
        $pdo->exec('CREATE INDEX product_images_variant_id_index ON product_images(variant_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS product_images');
    }
};
