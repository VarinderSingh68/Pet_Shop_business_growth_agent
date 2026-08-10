<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CampaignRecipient extends Model
{
    protected static string $table = 'campaign_recipients';
    protected static bool $timestamps = false;

    public static function findByToken(string $token): ?array
    {
        return static::firstWhere(['tracking_token' => $token]);
    }
}
