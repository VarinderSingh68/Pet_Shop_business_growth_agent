<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Models\GrowthAction;
use App\Models\Segment;

/**
 * Rule-based ranked opportunities for the admin dashboard's Growth Copilot
 * card — no external AI, just segment sizes multiplied by each segment's
 * own average order value, ranked by estimated revenue. Each opportunity
 * maps to a real, immediately-executable action via execute().
 */
final class CopilotService
{
    /** @var array<string, array{name: string, template: string, channel: string}> */
    private const ACTIONS = [
        'subscription_candidate' => [
            'name' => 'Subscription offer for repeat food buyers',
            'template' => "Hi {{name}},\n\nYou've reordered food from us a few times now — set it on autopilot with a subscription and never run out again. Pause, skip, or cancel anytime.",
            'channel' => 'email',
        ],
        'at_risk' => [
            'name' => 'Check-in offer for at-risk customers',
            'template' => "Hi {{name}},\n\nIt's been a little while! Here's 10% off your next order — no rush, just wanted to say hi.",
            'channel' => 'email',
        ],
        'lapsed' => [
            'name' => 'Win-back offer for lapsed customers',
            'template' => "Hi {{name}},\n\nWe miss you at Happy Tails! Come back with 15% off your next order.",
            'channel' => 'email',
        ],
        'puppy_owner' => [
            'name' => 'Puppy/kitten bundle upsell',
            'template' => "Hi {{name}},\n\nYour little one is growing fast! Check out our puppy/kitten starter bundle for food, toys, and training treats.",
            'channel' => 'email',
        ],
        'discount_hunter' => [
            'name' => 'Loyalty programme invite',
            'template' => "Hi {{name}},\n\nYou shop smart! Did you know you earn loyalty points on every order, discount or not? Check your balance in your account.",
            'channel' => 'email',
        ],
    ];

    /** @return array<int, array{key: string, title: string, description: string, affected_count: int, estimated_impact_paise: int}> */
    public function rankedOpportunities(int $limit = 5): array
    {
        $db = Database::instance();
        $globalAvgOrder = (int) ($db->selectOne("SELECT COALESCE(AVG(total_paise), 30000) AS a FROM orders WHERE status != 'cancelled'")['a'] ?? 30000);

        $opportunities = [];

        foreach (self::ACTIONS as $segmentKey => $action) {
            $segment = Segment::findByKey($segmentKey);
            if ($segment === null || (int) $segment['member_count'] === 0) {
                continue;
            }

            $memberIds = Segment::memberIds((int) $segment['id']);
            $avgValue = $this->avgOrderValueFor($db, $memberIds) ?? $globalAvgOrder;
            $estimatedImpact = (int) round(count($memberIds) * $avgValue * 0.3); // 30% assumed uptake — conservative, stated explicitly in the UI

            $count = count($memberIds);
            $noun = $this->nameAlreadyDescribesPeople($segment['name']) ? '' : ($count === 1 ? ' customer' : ' customers');
            $opportunities[] = [
                'key' => $segmentKey,
                'title' => $count . ' ' . strtolower($segment['name']) . $noun,
                'description' => $action['name'],
                'affected_count' => count($memberIds),
                'estimated_impact_paise' => $estimatedImpact,
            ];
        }

        usort($opportunities, static fn (array $a, array $b) => $b['estimated_impact_paise'] <=> $a['estimated_impact_paise']);

        return array_slice($opportunities, 0, $limit);
    }

    /** True for names like "Puppy/Kitten Owner" or "Discount Hunter" that already read as a person, so " customers" would be redundant. */
    private function nameAlreadyDescribesPeople(string $segmentName): bool
    {
        return str_ends_with(strtolower($segmentName), 'owner') || str_ends_with(strtolower($segmentName), 'hunter');
    }

    private function avgOrderValueFor(Database $db, array $userIds): ?int
    {
        if ($userIds === []) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $row = $db->selectOne(
            "SELECT AVG(total_paise) AS a FROM orders WHERE user_id IN ({$placeholders}) AND status != 'cancelled'",
            $userIds,
        );

        return $row !== null && $row['a'] !== null ? (int) $row['a'] : null;
    }

    /**
     * Executes a Copilot suggestion immediately: creates a one-off campaign
     * for that segment using the built-in template and sends it now.
     */
    public function execute(string $actionKey, int $executedByUserId): int
    {
        $action = self::ACTIONS[$actionKey] ?? null;
        $segment = Segment::findByKey($actionKey);

        if ($action === null || $segment === null) {
            throw new \RuntimeException('Unknown Copilot action.');
        }

        $db = Database::instance();
        $campaignId = $db->insert('campaigns', [
            'name' => $action['name'] . ' (Copilot, ' . date('d M Y') . ')',
            'segment_id' => $segment['id'],
            'channel' => $action['channel'],
            'template_subject' => $action['name'],
            'template_body' => $action['template'],
            'status' => 'draft',
            'created_by_user_id' => $executedByUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sentCount = (new CampaignService())->send($campaignId);

        GrowthAction::log(
            'copilot_action',
            "Copilot suggestion executed: \"{$action['name']}\" sent to {$sentCount} customer(s) in the {$segment['name']} segment.",
            ['target_type' => 'campaign', 'target_id' => $campaignId, 'affected_count' => $sentCount],
        );

        return $sentCount;
    }
}
