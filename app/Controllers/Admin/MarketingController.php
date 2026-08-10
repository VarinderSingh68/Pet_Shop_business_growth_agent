<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\GrowthAction;
use App\Models\Segment;
use App\Services\Growth\CampaignService;
use App\Services\Growth\CopilotService;

final class MarketingController extends Controller
{
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
}
