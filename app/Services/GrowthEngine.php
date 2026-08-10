<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CronRun;
use App\Services\Growth\AutomationService;
use App\Services\Growth\CampaignService;
use App\Services\Growth\LoyaltyService;
use App\Services\Growth\ScoringService;
use App\Services\Growth\SegmentService;

/**
 * Orchestrates every scheduled Growth Agent job. Each job is wrapped with a
 * cron_runs row (start/finish/duration/outcome) so failures are visible in
 * the admin panel instead of silently vanishing into a cron log nobody reads.
 */
final class GrowthEngine
{
    public function __construct(
        private readonly ScoringService $scoring = new ScoringService(),
        private readonly SegmentService $segments = new SegmentService(),
        private readonly AutomationService $automation = new AutomationService(),
        private readonly CampaignService $campaigns = new CampaignService(),
        private readonly LoyaltyService $loyalty = new LoyaltyService(),
    ) {
    }

    /** @return array<int, array{job: string, outcome: string, summary: string}> */
    public function runAll(): array
    {
        $results = [];

        $results[] = $this->run('score_customers', fn () => $this->scoring->scoreAll() . ' customer(s) scored');
        $results[] = $this->run('evaluate_segments', fn () => $this->summarizeSegments($this->segments->evaluateAll()));
        $results[] = $this->run('abandoned_carts', fn () => $this->automation->processAbandonedCarts());
        $results[] = $this->run('replenishment', fn () => $this->automation->processReplenishment());
        $results[] = $this->run('winback', fn () => $this->automation->processWinback());
        $results[] = $this->run('post_purchase', fn () => $this->automation->processPostPurchase());
        $results[] = $this->run('pet_lifecycle', fn () => $this->automation->processPetLifecycle());
        $results[] = $this->run('campaign_conversions', fn () => $this->campaigns->detectConversions() . ' conversion(s) detected');
        $results[] = $this->run('loyalty_expiry', fn () => $this->loyalty->sendExpiryWarnings() . ' expiry warning(s), ' . $this->loyalty->expirePastDue() . ' batch(es) expired');

        return $results;
    }

    /** @return array{job: string, outcome: string, summary: string} */
    private function run(string $jobName, \Closure $job): array
    {
        $runId = CronRun::start($jobName);

        try {
            $summary = $job();
            CronRun::finish($runId, 'success', $summary);
            return ['job' => $jobName, 'outcome' => 'success', 'summary' => $summary];
        } catch (\Throwable $e) {
            CronRun::finish($runId, 'failed', 'Failed: ' . $e->getMessage(), $e->getTraceAsString());
            logger_channel('growth-engine')->error("Job [{$jobName}] failed", ['error' => $e->getMessage()]);
            return ['job' => $jobName, 'outcome' => 'failed', 'summary' => $e->getMessage()];
        }
    }

    private function summarizeSegments(array $counts): string
    {
        $parts = [];
        foreach ($counts as $key => $count) {
            $parts[] = "{$key}: {$count}";
        }
        return implode(', ', $parts);
    }
}
