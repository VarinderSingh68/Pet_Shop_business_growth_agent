<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ReportService;
use Dompdf\Dompdf;
use Dompdf\Options;

final class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports = new ReportService())
    {
    }

    private function dateRange(Request $request): array
    {
        $to = (string) $request->query('to', date('Y-m-d'));
        $from = (string) $request->query('from', date('Y-m-d', strtotime('-30 days')));
        return [$from, $to];
    }

    public function sales(Request $request): void
    {
        [$from, $to] = $this->dateRange($request);

        $this->view('admin/reports/sales', [
            'title' => 'Sales report',
            'from' => $from,
            'to' => $to,
            'summary' => $this->reports->financialSummary($from, $to),
            'byDay' => $this->reports->salesByDay($from, $to),
            'byProduct' => array_slice($this->reports->salesByProduct($from, $to), 0, 15),
            'byCategory' => $this->reports->salesByCategory($from, $to),
        ]);
    }

    public function salesExport(Request $request, string $format): void
    {
        [$from, $to] = $this->dateRange($request);
        $rows = $this->reports->salesByDay($from, $to);

        if ($format === 'csv') {
            $this->downloadCsv('sales-' . $from . '-to-' . $to . '.csv', $rows);
            return;
        }

        if ($format === 'pdf') {
            $this->downloadPdf('sales-' . $from . '-to-' . $to . '.pdf', view('admin/reports/sales-pdf', [
                'from' => $from, 'to' => $to,
                'summary' => $this->reports->financialSummary($from, $to),
                'byDay' => $rows,
            ]));
            return;
        }

        abort(404);
    }

    public function coupons(Request $request): void
    {
        [$from, $to] = $this->dateRange($request);
        $this->view('admin/reports/coupons', [
            'title' => 'Coupon cost',
            'from' => $from, 'to' => $to,
            'coupons' => $this->reports->couponCost($from, $to),
        ]);
    }

    public function couponsExport(Request $request): void
    {
        [$from, $to] = $this->dateRange($request);
        $this->downloadCsv('coupon-cost-' . $from . '-to-' . $to . '.csv', $this->reports->couponCost($from, $to));
    }

    public function cohorts(Request $request): void
    {
        $this->view('admin/reports/cohorts', [
            'title' => 'Customer cohorts & retention',
            'cohorts' => $this->reports->customerCohortsWithRetention(),
        ]);
    }

    public function cohortsExport(Request $request): void
    {
        $this->downloadCsv('customer-cohorts.csv', $this->reports->customerCohortsWithRetention());
    }

    public function services(Request $request): void
    {
        [$from, $to] = $this->dateRange($request);
        $this->view('admin/reports/services', [
            'title' => 'Service utilisation',
            'from' => $from, 'to' => $to,
            'utilization' => $this->reports->serviceUtilization($from, $to),
        ]);
    }

    public function servicesExport(Request $request): void
    {
        [$from, $to] = $this->dateRange($request);
        $this->downloadCsv('service-utilisation-' . $from . '-to-' . $to . '.csv', $this->reports->serviceUtilization($from, $to));
    }

    public function inventory(Request $request): void
    {
        $this->view('admin/reports/inventory', [
            'title' => 'Inventory turnover',
            'turnover' => $this->reports->inventoryTurnover(),
        ]);
    }

    public function inventoryExport(Request $request): void
    {
        $this->downloadCsv('inventory-turnover.csv', $this->reports->inventoryTurnover());
    }

    private function downloadCsv(string $filename, array $rows): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $this->reports->toCsv($rows);
        exit;
    }

    private function downloadPdf(string $filename, string $html): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }
}
