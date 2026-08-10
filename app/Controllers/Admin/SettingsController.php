<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Setting;

final class SettingsController extends Controller
{
    private const KEYS = [
        'store_name' => ['group' => 'store', 'default' => 'Happy Tails Pet Store'],
        'store_address' => ['group' => 'store', 'default' => 'Bengaluru, Karnataka, India'],
        'store_phone' => ['group' => 'store', 'default' => '+91 00000 00000'],
        'store_email' => ['group' => 'store', 'default' => 'hello@happytails.test'],
        'tax_rate_percent' => ['group' => 'commerce', 'default' => '5'],
        'shipping_flat_rate' => ['group' => 'commerce', 'default' => '79'],
        'shipping_free_threshold' => ['group' => 'commerce', 'default' => '999'],
        'seo_default_title' => ['group' => 'seo', 'default' => 'Happy Tails Pet Store'],
        'seo_default_description' => ['group' => 'seo', 'default' => "Food, gear, grooming and vet care for the pet you're raising."],
        'social_instagram' => ['group' => 'social', 'default' => ''],
        'social_facebook' => ['group' => 'social', 'default' => ''],
        'theme_accent' => ['group' => 'theme', 'default' => 'leash'],
        'maintenance_mode' => ['group' => 'system', 'default' => '0'],
    ];

    public function index(Request $request): void
    {
        $values = [];
        foreach (self::KEYS as $key => $meta) {
            $values[$key] = Setting::get($key, $meta['default']);
        }

        $this->view('admin/settings/index', ['title' => 'Settings', 'values' => $values]);
    }

    public function update(Request $request): void
    {
        foreach (self::KEYS as $key => $meta) {
            if ($key === 'maintenance_mode') {
                continue; // handled by its own checkbox field, see below
            }
            $value = $request->input($key);
            if ($value !== null) {
                Setting::set($key, (string) $value, $meta['group']);
            }
        }

        Setting::set('maintenance_mode', $request->input('maintenance_mode') ? '1' : '0', 'system');

        flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }
}
