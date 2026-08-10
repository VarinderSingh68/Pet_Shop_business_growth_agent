<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE service_slots (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                service_id INT UNSIGNED NOT NULL,
                staff_id INT UNSIGNED NOT NULL,
                start_at DATETIME NOT NULL,
                end_at DATETIME NOT NULL,
                is_booked TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                UNIQUE KEY service_slots_staff_start_unique (staff_id, start_at),
                KEY service_slots_service_id_index (service_id),
                KEY service_slots_start_at_index (start_at),
                CONSTRAINT service_slots_service_fk FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
                CONSTRAINT service_slots_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS service_slots');
    }
};
