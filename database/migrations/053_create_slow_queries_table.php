<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE slow_queries (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sql_text TEXT NOT NULL,
                bindings JSON NULL,
                duration_ms DECIMAL(10,2) NOT NULL,
                request_path VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                KEY slow_queries_duration_index (duration_ms),
                KEY slow_queries_created_at_index (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS slow_queries');
    }
};
