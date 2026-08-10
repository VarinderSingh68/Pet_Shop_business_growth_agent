<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE inventory_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            variant_id INT NOT NULL,
            change_quantity INT NOT NULL,
            reason TEXT NOT NULL CHECK (reason IN ('order','restock','adjustment','return','stock_take')),
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            note VARCHAR(255) NULL,
            created_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT inventory_movements_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
            CONSTRAINT inventory_movements_user_fk FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX inventory_movements_variant_id_index ON inventory_movements(variant_id)');
        $pdo->exec('CREATE INDEX inventory_movements_reference_index ON inventory_movements(reference_type, reference_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS inventory_movements');
    }
};
