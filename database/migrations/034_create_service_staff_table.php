<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE service_staff (
            service_id INT NOT NULL,
            staff_id INT NOT NULL,
            PRIMARY KEY (service_id, staff_id),
            CONSTRAINT service_staff_service_fk FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            CONSTRAINT service_staff_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE
            )
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS service_staff');
    }
};
