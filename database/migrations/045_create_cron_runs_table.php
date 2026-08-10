<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE cron_runs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            job_name VARCHAR(100) NOT NULL,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            duration_ms INT NULL,
            outcome TEXT NULL CHECK (outcome IN ('success','failed')),
            summary VARCHAR(500) NULL,
            error TEXT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX cron_runs_job_name_index ON cron_runs(job_name)');
        $pdo->exec('CREATE INDEX cron_runs_started_at_index ON cron_runs(started_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS cron_runs');
    }
};
