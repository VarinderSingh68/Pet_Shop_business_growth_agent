<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE api_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            last_four VARCHAR(4) NOT NULL,
            last_used_at DATETIME NULL,
            rate_limit_per_minute INT NOT NULL DEFAULT 60,
            revoked_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT api_tokens_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX api_tokens_token_hash_unique ON api_tokens(token_hash)');
        $pdo->exec('CREATE INDEX api_tokens_user_id_index ON api_tokens(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS api_tokens');
    }
};
