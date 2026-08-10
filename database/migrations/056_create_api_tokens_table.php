<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE api_tokens (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                name VARCHAR(150) NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                last_four VARCHAR(4) NOT NULL,
                last_used_at DATETIME NULL,
                rate_limit_per_minute INT UNSIGNED NOT NULL DEFAULT 60,
                revoked_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                UNIQUE KEY api_tokens_token_hash_unique (token_hash),
                KEY api_tokens_user_id_index (user_id),
                CONSTRAINT api_tokens_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS api_tokens');
    }
};
