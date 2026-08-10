<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Growth\CampaignService;

final class CampaignTrackingController extends Controller
{
    public function __construct(private readonly CampaignService $campaigns = new CampaignService())
    {
    }

    /** 1x1 transparent GIF used as an email open-tracking pixel. */
    public function pixel(Request $request, string $token): void
    {
        $this->campaigns->trackOpen($token);

        header('Content-Type: image/gif');
        header('Cache-Control: no-store');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7');
        exit;
    }

    /** Click-through redirect used at the bottom of every campaign message. */
    public function click(Request $request, string $token): void
    {
        $this->campaigns->trackClick($token);
        $this->redirect('/shop');
    }
}
