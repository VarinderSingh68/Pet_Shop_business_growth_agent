<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE purchase_orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            supplier_id INT NOT NULL,
            reference VARCHAR(40) NOT NULL,
            status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','ordered','received','cancelled')),
            notes VARCHAR(500) NULL,
            expected_at DATE NULL,
            created_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT purchase_orders_supplier_fk FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
            CONSTRAINT purchase_orders_user_fk FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX purchase_orders_reference_unique ON purchase_orders(reference)');
        $pdo->exec('CREATE INDEX purchase_orders_supplier_id_index ON purchase_orders(supplier_id)');

        $pdo->exec(<<<SQL
            CREATE TABLE purchase_order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_order_id INT NOT NULL,
            variant_id INT NOT NULL,
            quantity INT NOT NULL,
            received_quantity INT NOT NULL DEFAULT 0,
            unit_cost_paise INT NOT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT purchase_order_items_po_fk FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            CONSTRAINT purchase_order_items_variant_fk FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT
            )
        SQL);
        $pdo->exec('CREATE INDEX purchase_order_items_po_id_index ON purchase_order_items(purchase_order_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS purchase_order_items');
        $pdo->exec('DROP TABLE IF EXISTS purchase_orders');
    }
};
