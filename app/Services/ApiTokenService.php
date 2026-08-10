<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class ApiTokenService
{
    /** @return array{token: string, id: int} plaintext token, shown once */
    public function create(int $userId, string $name, int $rateLimitPerMinute = 60): array
    {
        $plaintext = 'pst_' . bin2hex(random_bytes(24));

        $id = Database::instance()->insert('api_tokens', [
            'user_id' => $userId,
            'name' => $name,
            'token_hash' => hash('sha256', $plaintext),
            'last_four' => substr($plaintext, -4),
            'rate_limit_per_minute' => $rateLimitPerMinute,
            'created_at' => now(),
        ]);

        return ['token' => $plaintext, 'id' => $id];
    }

    public function revoke(int $tokenId): void
    {
        Database::instance()->update('api_tokens', ['revoked_at' => now()], 'id = :id', ['id' => $tokenId]);
    }
}
