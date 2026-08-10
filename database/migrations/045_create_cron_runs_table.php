<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE cron_runs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                job_name VARCHAR(100) NOT NULL,
                started_at DATETIME NOT NULL,
                finished_at DATETIME NULL,
                duration_ms INT UNSIGNED NULL,
                outcome ENUM('success','failed') NULL,
                summary VARCHAR(500) NULL,
                error TEXT NULL,
                KEY cron_runs_job_name_index (job_name),
                KEY cron_runs_started_at_index (started_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS cron_runs');
    }
};
