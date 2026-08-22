<?php

declare(strict_types=1);

// Backs the delivery-partner Android app: a rider is assigned to an order,
// walks it through picked_up -> out_for_delivery -> delivered/failed, and
// pings their location periodically while a delivery is active. Riders are
// plain `users` rows with role 'delivery' (see seeds/001), authenticated the
// same way as any other API client (a hashed bearer token in `api_tokens`) —
// there's no separate account system to keep in sync.
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE delivery_assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INT NOT NULL,
            delivery_partner_id INT NOT NULL,
            status TEXT NOT NULL DEFAULT 'assigned' CHECK (status IN ('assigned','picked_up','out_for_delivery','delivered','failed')),
            assigned_by_user_id INT NULL,
            notes VARCHAR(255) NULL,
            assigned_at DATETIME NOT NULL,
            picked_up_at DATETIME NULL,
            out_for_delivery_at DATETIME NULL,
            delivered_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT delivery_assignments_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT delivery_assignments_partner_fk FOREIGN KEY (delivery_partner_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT delivery_assignments_assigned_by_fk FOREIGN KEY (assigned_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX delivery_assignments_order_id_index ON delivery_assignments(order_id)');
        $pdo->exec('CREATE INDEX delivery_assignments_partner_id_index ON delivery_assignments(delivery_partner_id)');
        $pdo->exec('CREATE INDEX delivery_assignments_status_index ON delivery_assignments(status)');

        $pdo->exec(<<<SQL
            CREATE TABLE delivery_locations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            delivery_partner_id INT NOT NULL,
            order_id INT NULL,
            lat REAL NOT NULL,
            lng REAL NOT NULL,
            recorded_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT delivery_locations_partner_fk FOREIGN KEY (delivery_partner_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT delivery_locations_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX delivery_locations_partner_id_index ON delivery_locations(delivery_partner_id, recorded_at)');
        $pdo->exec('CREATE INDEX delivery_locations_order_id_index ON delivery_locations(order_id, recorded_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS delivery_locations');
        $pdo->exec('DROP TABLE IF EXISTS delivery_assignments');
    }
};
