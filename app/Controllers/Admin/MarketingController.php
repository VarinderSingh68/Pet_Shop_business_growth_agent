<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\GrowthAction;
use App\Models\NewsletterSubscriber;
use App\Models\Segment;
use App\Services\Growth\CampaignService;
use App\Services\Growth\CopilotService;

final class MarketingController extends Controller
{
    private const LIST_PER_PAGE = 40;

    public function __construct(
        private readonly CampaignService $campaignService = new CampaignService(),
        private readonly CopilotService $copilot = new CopilotService(),
    ) {
    }

    public function segments(Request $request): void
    {
        $this->view('admin/marketing/segments', [
            'title' => 'Segments',
            'segments' => Segment::allWithCounts(),
        ]);
    }

    public function segmentMembers(Request $request, string $id): void
    {
        $segment = Segment::find((int) $id);
        if ($segment === null) {
            abort(404);
        }

        $memberIds = Segment::memberIds((int) $id);
        $members = $memberIds === [] ? [] : \App\Core\Database::instance()->select(
            'SELECT id, name, email FROM users WHERE id IN (' . implode(',', array_fill(0, count($memberIds), '?')) . ')',
            $memberIds,
        );

        $this->view('admin/marketing/segment-members', [
            'title' => $segment['name'],
            'segment' => $segment,
            'members' => $members,
        ]);
    }

    public function campaigns(Request $request): void
    {
        $this->view('admin/marketing/campaigns', [
            'title' => 'Campaigns',
            'campaigns' => Campaign::withStats(),
            'segments' => Segment::allWithCounts(),
        ]);
    }

    public function storeCampaign(Request $request): void
    {
        $data = $request->only(['name', 'segment_id', 'channel', 'template_subject', 'template_body']);

        $validator = Validator::make($data, [
            'name' => 'required|max:150',
            'segment_id' => 'required|integer',
            'channel' => 'required|in:email,whatsapp,sms,banner',
            'template_body' => 'required|max:5000',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please fill in all campaign fields.');
            back();
        }

        $id = \App\Core\Database::instance()->insert('campaigns', [
            'name' => $data['name'],
            'segment_id' => (int) $data['segment_id'],
            'channel' => $data['channel'],
            'template_subject' => !empty($data['template_subject']) ? $data['template_subject'] : null,
            'template_body' => $data['template_body'],
            'status' => 'draft',
            'created_by_user_id' => App::auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        flash('success', 'Campaign created as a draft. Review and send when ready.');
        $this->redirect('/admin/marketing/campaigns/' . $id);
    }

    public function showCampaign(Request $request, string $id): void
    {
        $campaign = Campaign::withStatsById((int) $id);
        if ($campaign === null) {
            abort(404);
        }

        $this->view('admin/marketing/campaign-show', [
            'title' => $campaign['name'],
            'campaign' => $campaign,
        ]);
    }

    public function sendCampaign(Request $request, string $id): void
    {
        $sentCount = $this->campaignService->send((int) $id);
        flash('success', "Campaign sent to {$sentCount} customer(s).");
        $this->redirect('/admin/marketing/campaigns/' . $id);
    }

    public function copilotAction(Request $request, string $key): void
    {
        try {
            $sentCount = $this->copilot->execute($key, (int) App::auth()->id());
            flash('success', "Done — sent to {$sentCount} customer(s).");
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('/admin');
    }

    public function growthActions(Request $request): void
    {
        $this->view('admin/marketing/growth-actions', [
            'title' => 'Growth Agent activity',
            'actions' => GrowthAction::recent(100),
        ]);
    }

    public function coupons(Request $request): void
    {
        $this->view('admin/marketing/coupons', [
            'title' => 'Coupons',
            'coupons' => Coupon::all('created_at DESC'),
        ]);
    }

    public function storeCoupon(Request $request): void
    {
        $data = $request->only(['code', 'description', 'type', 'value', 'min_order', 'usage_limit', 'usage_limit_per_customer', 'expires_at']);

        $validator = Validator::make($data, [
            'code' => 'required|max:40|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the coupon details.');
            back();
        }

        Coupon::create([
            'code' => strtoupper($data['code']),
            'description' => !empty($data['description']) ? $data['description'] : null,
            'type' => $data['type'],
            'value' => $data['type'] === 'fixed' ? (int) round(((float) $data['value']) * 100) : (int) $data['value'],
            'min_order_paise' => !empty($data['min_order']) ? (int) round(((float) $data['min_order']) * 100) : null,
            'usage_limit' => !empty($data['usage_limit']) ? (int) $data['usage_limit'] : null,
            'usage_limit_per_customer' => !empty($data['usage_limit_per_customer']) ? (int) $data['usage_limit_per_customer'] : null,
            'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] . ' 23:59:59' : null,
            'is_active' => 1,
        ]);

        flash('success', 'Coupon created.');
        $this->redirect('/admin/marketing/coupons');
    }

    public function toggleCoupon(Request $request, string $id): void
    {
        $coupon = Coupon::find((int) $id);
        if ($coupon !== null) {
            Coupon::updateWhere((int) $id, ['is_active' => $coupon['is_active'] ? 0 : 1]);
        }
        $this->redirect('/admin/marketing/coupons');
    }

    // --- Newsletter ---------------------------------------------------------

    public function newsletter(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $total = NewsletterSubscriber::count();
        $subscribedCount = NewsletterSubscriber::count(['status' => 'subscribed']);

        $subscribers = Database::instance()->select(
            'SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT ' . self::LIST_PER_PAGE . ' OFFSET ' . (($page - 1) * self::LIST_PER_PAGE),
        );

        $this->view('admin/marketing/newsletter', [
            'title' => 'Newsletter subscribers',
            'subscribers' => $subscribers,
            'subscribedCount' => $subscribedCount,
            'total' => $total,
            'page' => $page,
            'perPage' => self::LIST_PER_PAGE,
        ]);
    }

    public function exportNewsletter(Request $request): void
    {
        $subscribers = NewsletterSubscriber::where(['status' => 'subscribed'], 'created_at DESC');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter-subscribers.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Email', 'Subscribed at']);
        foreach ($subscribers as $s) {
            fputcsv($out, [$s['email'], $s['created_at']]);
        }
        fclose($out);
        exit;
    }

    // --- Referrals ------------------------------------------------------------

    public function referrals(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));

        $stats = Database::instance()->selectOne(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = 'rewarded' THEN 1 ELSE 0 END) AS rewarded,
                    SUM(CASE WHEN status = 'rewarded' THEN reward_paise ELSE 0 END) AS reward_total_paise
             FROM referrals",
        );

        $referrals = Database::instance()->select(
            'SELECT rf.*, ru.name AS referrer_name, ru.email AS referrer_email, rd.name AS referred_name
             FROM referrals rf
             JOIN users ru ON ru.id = rf.referrer_user_id
             JOIN users rd ON rd.id = rf.referred_user_id
             ORDER BY rf.created_at DESC LIMIT ' . self::LIST_PER_PAGE . ' OFFSET ' . (($page - 1) * self::LIST_PER_PAGE),
        );

        $this->view('admin/marketing/referrals', [
            'title' => 'Referrals',
            'referrals' => $referrals,
            'stats' => $stats,
            'total' => (int) ($stats['total'] ?? 0),
            'page' => $page,
            'perPage' => self::LIST_PER_PAGE,
        ]);
    }

    // --- Gift cards ---------------------------------------------------------

    public function giftCards(Request $request): void
    {
        $this->view('admin/marketing/gift-cards', [
            'title' => 'Gift cards',
            'giftCards' => GiftCard::all('created_at DESC'),
        ]);
    }

    public function storeGiftCard(Request $request): void
    {
        $data = $request->only(['amount', 'recipient_name', 'recipient_email', 'note', 'expires_at']);

        $validator = Validator::make($data, ['amount' => 'required|numeric']);
        if ($validator->fails() || (float) $data['amount'] <= 0) {
            flash('error', 'Enter a valid amount.');
            back();
        }

        $balancePaise = (int) round(((float) $data['amount']) * 100);

        $id = GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_balance_paise' => $balancePaise,
            'balance_paise' => $balancePaise,
            'recipient_name' => !empty($data['recipient_name']) ? $data['recipient_name'] : null,
            'recipient_email' => !empty($data['recipient_email']) ? $data['recipient_email'] : null,
            'note' => !empty($data['note']) ? $data['note'] : null,
            'is_active' => 1,
            'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
            'issued_by_user_id' => App::auth()->id(),
        ]);

        $giftCard = GiftCard::find($id);
        flash('success', 'Gift card issued: ' . $giftCard['code']);
        $this->redirect('/admin/marketing/gift-cards');
    }

    public function toggleGiftCard(Request $request, string $id): void
    {
        $card = GiftCard::find((int) $id);
        if ($card !== null) {
            GiftCard::updateWhere((int) $id, ['is_active' => $card['is_active'] ? 0 : 1]);
        }
        $this->redirect('/admin/marketing/gift-cards');
    }

    /**
     * Records a redemption against a phone/in-store order. Gift cards aren't
     * wired into the online checkout flow yet — this is a manual ledger entry
     * so staff can track balance use without touching checkout/payment code.
     */
    public function redeemGiftCard(Request $request, string $id): void
    {
        $card = GiftCard::find((int) $id);
        if ($card === null) {
            abort(404);
        }

        $amount = (float) $request->input('amount', 0);
        $amountPaise = (int) round($amount * 100);

        if ($amountPaise <= 0 || $amountPaise > (int) $card['balance_paise']) {
            flash('error', 'Enter an amount that does not exceed the remaining balance.');
            back();
        }

        Database::instance()->transaction(function (Database $db) use ($id, $card, $amountPaise, $request) {
            $db->update('gift_cards', ['balance_paise' => (int) $card['balance_paise'] - $amountPaise], 'id = :id', ['id' => $id]);
            $db->insert('gift_card_redemptions', [
                'gift_card_id' => $id,
                'amount_paise' => $amountPaise,
                'order_number' => !empty($request->input('order_number')) ? $request->input('order_number') : null,
                'note' => !empty($request->input('note')) ? $request->input('note') : null,
                'redeemed_by_user_id' => App::auth()->id(),
                'created_at' => now(),
            ]);
        });

        flash('success', 'Redemption recorded.');
        $this->redirect('/admin/marketing/gift-cards');
    }
}
