<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Campaign extends Model
{
    protected static string $table = 'campaigns';

    /** @return array<int, array<string, mixed>> */
    public static function withStats(): array
    {
        return static::db()->select(
            "SELECT c.*, s.name AS segment_name,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id) AS recipient_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.status IN ('sent','opened','clicked','converted')) AS sent_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.opened_at IS NOT NULL) AS opened_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.clicked_at IS NOT NULL) AS clicked_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.converted_at IS NOT NULL) AS converted_count,
                    (SELECT COALESCE(SUM(revenue_attributed_paise),0) FROM campaign_recipients cr WHERE cr.campaign_id = c.id) AS revenue_paise
             FROM campaigns c
             LEFT JOIN segments s ON s.id = c.segment_id
             ORDER BY c.created_at DESC",
        );
    }

    public static function withStatsById(int $id): ?array
    {
        $rows = static::db()->select(
            "SELECT c.*, s.name AS segment_name,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id) AS recipient_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.opened_at IS NOT NULL) AS opened_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.clicked_at IS NOT NULL) AS clicked_count,
                    (SELECT COUNT(*) FROM campaign_recipients cr WHERE cr.campaign_id = c.id AND cr.converted_at IS NOT NULL) AS converted_count,
                    (SELECT COALESCE(SUM(revenue_attributed_paise),0) FROM campaign_recipients cr WHERE cr.campaign_id = c.id) AS revenue_paise
             FROM campaigns c LEFT JOIN segments s ON s.id = c.segment_id WHERE c.id = :id",
            ['id' => $id],
        );
        return $rows[0] ?? null;
    }
}
