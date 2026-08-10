<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE staff_blackout_dates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                staff_id INT UNSIGNED NOT NULL,
                date DATE NOT NULL,
                reason VARCHAR(200) NULL,
                created_at DATETIME NOT NULL,
                UNIQUE KEY staff_blackout_dates_unique (staff_id, date),
                CONSTRAINT staff_blackout_dates_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS staff_blackout_dates');
    }
};
