<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Setting;

final class ContactController extends Controller
{
    public function show(Request $request): void
    {
        $this->view('site/contact', [
            'title' => 'Contact us',
            'storeAddress' => Setting::get('store_address', 'Bengaluru, Karnataka, India'),
            'storePhone' => Setting::get('store_phone', '+91 00000 00000'),
            'storeEmail' => Setting::get('store_email', 'hello@happytails.test'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['name', 'email', 'phone', 'subject', 'message']);

        $validator = Validator::make($data, [
            'name' => 'required|max:150',
            'email' => 'required|email',
            'subject' => 'required|max:200',
            'message' => 'required|max:2000',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please fill in all required fields.');
            back();
        }

        Database::instance()->insert('enquiries', [
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        flash('success', "Thanks — we've got your message and will reply by email soon.");
        back();
    }
}
