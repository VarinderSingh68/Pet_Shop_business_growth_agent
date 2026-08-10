<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Dompdf\Dompdf;
use Dompdf\Options;

final class InvoiceService
{
    /**
     * A time-limited HMAC signature so a guest (no account) can still reach
     * their invoice from the order-confirmation/tracking page without auth.
     */
    public function signedUrl(array $order, int $ttlSeconds = 60 * 60 * 24 * 14): string
    {
        $expires = time() + $ttlSeconds;
        $signature = $this->sign((int) $order['id'], $expires);

        return url("account/orders/{$order['id']}/invoice?expires={$expires}&sig={$signature}");
    }

    public function verify(int $orderId, int $expires, string $signature): bool
    {
        if ($expires < time()) {
            return false;
        }

        return hash_equals($this->sign($orderId, $expires), $signature);
    }

    private function sign(int $orderId, int $expires): string
    {
        return hash_hmac('sha256', $orderId . '|' . $expires, (string) config('app.key'));
    }

    public function renderPdf(array $order): string
    {
        $items = Order::items((int) $order['id']);
        $html = view('site/orders/invoice-pdf', ['order' => $order, 'items' => $items]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
