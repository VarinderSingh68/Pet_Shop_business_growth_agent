<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE inventory_movements (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                variant_id INT UNSIGNED NOT NULL,
                change_quantity INT NOT NULL COMMENT 'Signed: negative for sales/deductions, positive for restocks',
                reason ENUM('order','restock','adjustment','return','stock_take') NOT NULL,
                reference_type VARCHAR(50) NULL,
                reference_id INT UNSIGNED NULL,
                note VARCHAR(255) NULL,
                created_by_user_id INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                KEY inventory_movements_variant_id_index (variant_id),
                KEY inventory_movements_reference_index (reference_type, reference_id),
                CONSTRAINT inventory_movements_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
                CONSTRAINT inventory_movements_user_fk FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS inventory_movements');
    }
};
