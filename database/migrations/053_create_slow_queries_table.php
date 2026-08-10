<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE slow_queries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sql_text TEXT NOT NULL,
            bindings JSON NULL,
            duration_ms DECIMAL(10,2) NOT NULL,
            request_path VARCHAR(255) NULL,
            created_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX slow_queries_duration_index ON slow_queries(duration_ms)');
        $pdo->exec('CREATE INDEX slow_queries_created_at_index ON slow_queries(created_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS slow_queries');
    }
};
