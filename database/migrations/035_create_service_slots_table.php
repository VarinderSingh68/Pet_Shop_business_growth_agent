<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE service_slots (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_id INT NOT NULL,
            staff_id INT NOT NULL,
            start_at DATETIME NOT NULL,
            end_at DATETIME NOT NULL,
            is_booked INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            CONSTRAINT service_slots_service_fk FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            CONSTRAINT service_slots_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX service_slots_staff_start_unique ON service_slots(staff_id, start_at)');
        $pdo->exec('CREATE INDEX service_slots_service_id_index ON service_slots(service_id)');
        $pdo->exec('CREATE INDEX service_slots_start_at_index ON service_slots(start_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS service_slots');
    }
};
