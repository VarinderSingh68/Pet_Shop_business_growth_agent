<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class NewsletterSubscriber extends Model
{
    protected static string $table = 'newsletter_subscribers';

    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere(['email' => strtolower(trim($email))]);
    }

    public static function findByToken(string $token): ?array
    {
        return static::firstWhere(['unsubscribe_token' => $token]);
    }
}
