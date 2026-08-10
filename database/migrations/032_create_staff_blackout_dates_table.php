<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE staff_blackout_dates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            staff_id INT NOT NULL,
            date DATE NOT NULL,
            reason VARCHAR(200) NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT staff_blackout_dates_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX staff_blackout_dates_unique ON staff_blackout_dates(staff_id, date)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS staff_blackout_dates');
    }
};
