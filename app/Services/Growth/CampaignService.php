<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Core\Mailer;
use App\Models\Campaign;
use App\Models\GrowthAction;
use App\Models\Segment;

/**
 * Sends a campaign to every member of its segment, one recipient row per
 * customer with a unique tracking token used for the open pixel and
 * click-through redirect. Conversion is detected on the next cron pass by
 * AutomationService's sibling job (see CampaignService::detectConversions),
 * matching any order a recipient places within 7 days of the send.
 */
final class CampaignService
{
    private const CONVERSION_WINDOW_DAYS = 7;

    public function send(int $campaignId): int
    {
        $db = Database::instance();
        $campaign = $db->selectOne('SELECT * FROM campaigns WHERE id = :id', ['id' => $campaignId]);

        if ($campaign === null || $campaign['status'] === 'sent') {
            return 0;
        }

        $memberIds = $campaign['segment_id'] !== null ? Segment::memberIds((int) $campaign['segment_id']) : [];
        $sentCount = 0;

        foreach ($memberIds as $userId) {
            $user = $db->selectOne('SELECT name, email FROM users WHERE id = :id', ['id' => $userId]);
            if ($user === null) {
                continue;
            }

            $existing = $db->selectOne(
                'SELECT id FROM campaign_recipients WHERE campaign_id = :cid AND user_id = :uid',
                ['cid' => $campaignId, 'uid' => $userId],
            );
            if ($existing !== null) {
                continue; // already sent to this recipient (e.g. a retry)
            }

            $token = bin2hex(random_bytes(20));
            $recipientId = $db->insert('campaign_recipients', [
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'status' => 'pending',
                'tracking_token' => $token,
            ]);

            $body = $this->renderTemplate((string) $campaign['template_body'], (string) $user['name'], $token);

            if ($campaign['channel'] === 'email') {
                $sent = Mailer::send((string) $user['email'], (string) $user['name'], (string) ($campaign['template_subject'] ?? $campaign['name']), $body);
            } else {
                // WhatsApp/SMS/banner: no live gateway configured, so the
                // rendered message is logged as a notification instead of
                // silently doing nothing — visible in the admin panel.
                $sent = true;
            }

            $db->update('campaign_recipients', [
                'status' => $sent ? 'sent' : 'failed',
                'sent_at' => $sent ? now() : null,
            ], 'id = :id', ['id' => $recipientId]);

            $db->insert('notifications', [
                'user_id' => $userId,
                'type' => 'campaign',
                'channel' => $campaign['channel'],
                'subject' => $campaign['template_subject'],
                'body' => $body,
                'status' => $sent ? 'sent' : 'failed',
                'related_type' => 'campaign',
                'related_id' => $campaignId,
                'sent_at' => $sent ? now() : null,
                'created_at' => now(),
            ]);

            if ($sent) {
                $sentCount++;
            }
        }

        $db->update('campaigns', ['status' => 'sent', 'sent_at' => now()], 'id = :id', ['id' => $campaignId]);

        GrowthAction::log(
            'campaign_sent',
            "Sent campaign \"{$campaign['name']}\" to {$sentCount} customer(s) in the " . ($campaign['segment_id'] !== null ? 'targeted' : 'entire') . ' segment.',
            ['target_type' => 'campaign', 'target_id' => $campaignId, 'affected_count' => $sentCount],
        );

        return $sentCount;
    }

    /**
     * Builds the actual HTML sent: the template is plain text with a
     * {{name}} placeholder, so the customer's name is escaped before
     * substitution (it's user-supplied at registration) and the whole body
     * is nl2br'd rather than trusted as raw HTML.
     */
    private function renderTemplate(string $body, string $name, string $token): string
    {
        $body = str_replace('{{name}}', e($name), e($body));
        $html = nl2br($body)
            . '<p><a href="' . e(url('/c/' . $token)) . '">Shop now</a></p>'
            . '<img src="' . e(url('/o/' . $token)) . '" width="1" height="1" alt="" style="display:none">';

        return $html;
    }

    public function trackOpen(string $token): void
    {
        $recipient = \App\Models\CampaignRecipient::findByToken($token);
        if ($recipient !== null && $recipient['opened_at'] === null) {
            Database::instance()->update('campaign_recipients', ['opened_at' => now(), 'status' => 'opened'], 'id = :id', ['id' => $recipient['id']]);
        }
    }

    public function trackClick(string $token): void
    {
        $recipient = \App\Models\CampaignRecipient::findByToken($token);
        if ($recipient !== null && $recipient['clicked_at'] === null) {
            Database::instance()->update('campaign_recipients', ['clicked_at' => now(), 'status' => 'clicked'], 'id = :id', ['id' => $recipient['id']]);
        }
    }

    /**
     * Attributes revenue: any order a recipient places within the
     * conversion window after their campaign was sent counts as converted.
     */
    public function detectConversions(): int
    {
        $db = Database::instance();

        $pending = $db->select(
            "SELECT cr.id, cr.user_id, cr.sent_at FROM campaign_recipients cr
             WHERE cr.sent_at IS NOT NULL AND cr.converted_at IS NULL
               AND cr.sent_at >= DATE_SUB(NOW(), INTERVAL " . (self::CONVERSION_WINDOW_DAYS + 1) . " DAY)",
        );

        $converted = 0;

        foreach ($pending as $r) {
            $order = $db->selectOne(
                "SELECT id, total_paise FROM orders
                 WHERE user_id = :uid AND status != 'cancelled' AND placed_at BETWEEN :start AND DATE_ADD(:start2, INTERVAL " . self::CONVERSION_WINDOW_DAYS . " DAY)
                 ORDER BY placed_at ASC LIMIT 1",
                ['uid' => $r['user_id'], 'start' => $r['sent_at'], 'start2' => $r['sent_at']],
            );

            if ($order !== null) {
                $db->update('campaign_recipients', [
                    'converted_at' => now(),
                    'status' => 'converted',
                    'order_id' => $order['id'],
                    'revenue_attributed_paise' => $order['total_paise'],
                ], 'id = :id', ['id' => $r['id']]);
                $converted++;
            }
        }

        return $converted;
    }
}
