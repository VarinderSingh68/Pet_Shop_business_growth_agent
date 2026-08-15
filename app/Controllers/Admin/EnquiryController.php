<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\Enquiry;

final class EnquiryController extends Controller
{
    public function index(Request $request): void
    {
        $status = (string) $request->query('status', 'open');
        $where = in_array($status, ['open', 'in_progress', 'resolved'], true) ? 'WHERE status = :status' : '';

        $enquiries = Database::instance()->select(
            "SELECT * FROM enquiries {$where} ORDER BY created_at DESC LIMIT 200",
            $where !== '' ? ['status' => $status] : [],
        );

        $counts = Database::instance()->select('SELECT status, COUNT(*) AS c FROM enquiries GROUP BY status');
        $countsByStatus = array_column($counts, 'c', 'status');

        $this->view('admin/support/index', [
            'title' => 'Support enquiries',
            'enquiries' => $enquiries,
            'status' => $status,
            'countsByStatus' => $countsByStatus,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $enquiry = Enquiry::find((int) $id);
        if ($enquiry === null) {
            abort(404);
        }

        $this->view('admin/support/show', [
            'title' => 'Enquiry — ' . $enquiry['subject'],
            'enquiry' => $enquiry,
        ]);
    }

    public function reply(Request $request, string $id): void
    {
        $enquiry = Enquiry::find((int) $id);
        if ($enquiry === null) {
            abort(404);
        }

        $reply = trim((string) $request->input('staff_reply', ''));
        if ($reply === '') {
            flash('error', 'Write a reply before sending.');
            back();
        }

        Enquiry::updateWhere((int) $id, [
            'staff_reply' => $reply,
            'replied_at' => now(),
            'status' => 'resolved',
        ]);

        Mailer::send(
            $enquiry['email'],
            $enquiry['name'],
            'Re: ' . $enquiry['subject'],
            nl2br(e($reply)) . '<hr><p style="color:#777">Your original message:</p><blockquote>' . nl2br(e($enquiry['message'])) . '</blockquote>',
            $reply,
        );

        flash('success', 'Reply sent and enquiry marked resolved.');
        $this->redirect('/admin/support/' . $id);
    }

    public function updateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        if (!in_array($status, ['open', 'in_progress', 'resolved'], true)) {
            back();
        }

        Enquiry::updateWhere((int) $id, ['status' => $status]);
        flash('success', 'Status updated.');
        back();
    }
}
