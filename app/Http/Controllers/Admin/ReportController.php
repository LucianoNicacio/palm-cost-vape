<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ReportsExport;
use App\Models\InStoreSale;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
        $data = $this->buildReportData($startDate, $endDate, $periodLabel);

        return Inertia::render('Admin/Reports/Index', [
            ...$data,
            'period' => $request->get('period', 'today'),
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);

        return Excel::download(
            new ReportsExport($startDate, $endDate, $periodLabel),
            'report-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
        $data = $this->buildReportData($startDate, $endDate, $periodLabel);

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('letter', 'landscape');

        return $pdf->download('report-' . date('Y-m-d') . '.pdf');
    }

    private function parsePeriod(Request $request): array
    {
        $period = $request->get('period', 'today');

        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Today';
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $periodLabel = 'This Week';
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $periodLabel = 'This Month';
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $periodLabel = 'This Year';
                break;
            case 'custom':
                if ($request->filled(['start_date', 'end_date'])) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $periodLabel = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
                } else {
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    $periodLabel = 'Today';
                }
                break;
            default:
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Today';
        }

        return [$startDate, $endDate, $periodLabel];
    }

    private function buildReportData(Carbon $startDate, Carbon $endDate, string $periodLabel): array
    {
        $reservationQuery = Reservation::completed()->whereBetween('created_at', [$startDate, $endDate]);
        $saleQuery = InStoreSale::whereBetween('created_at', [$startDate, $endDate]);

        $reservationRevenue = round((float) (clone $reservationQuery)->sum('total_price'), 2);
        $inStoreRevenue = round((float) (clone $saleQuery)->sum('total_amount'), 2);
        $reservationCount = (clone $reservationQuery)->count();
        $inStoreCount = (clone $saleQuery)->count();
        $totalOrders = $reservationCount + $inStoreCount;
        $totalRevenue = round($reservationRevenue + $inStoreRevenue, 2);
        $totalItemsSold = (int) (clone $reservationQuery)->sum('item_count') + (int) (clone $saleQuery)->sum('total_items');
        $averageOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $reservations = (clone $reservationQuery)
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'confirmation_number', 'customer_id', 'item_count', 'subtotal', 'tax_amount', 'reward_discount', 'total_price', 'created_at']);

        $inStoreSales = (clone $saleQuery)
            ->with('recorder:id,name')
            ->orderByDesc('created_at')
            ->get();

        $topProducts = DB::select("
            SELECT p.name,
                   SUM(combined.quantity) as total_quantity,
                   SUM(combined.revenue) as total_revenue
            FROM (
                SELECT ri.product_id, ri.quantity, ri.subtotal as revenue
                FROM reservation_items ri
                JOIN reservations r ON r.id = ri.reservation_id
                WHERE r.status = 'completed' AND r.created_at BETWEEN ? AND ?
                UNION ALL
                SELECT isi.product_id, isi.quantity, isi.subtotal as revenue
                FROM in_store_sale_items isi
                JOIN in_store_sales s ON s.id = isi.in_store_sale_id
                WHERE s.created_at BETWEEN ? AND ?
            ) as combined
            JOIN products p ON p.id = combined.product_id
            GROUP BY combined.product_id, p.name
            ORDER BY total_quantity DESC
            LIMIT 10
        ", [$startDate, $endDate, $startDate, $endDate]);

        return [
            'periodLabel' => $periodLabel,
            'stats' => [
                'total_revenue' => $totalRevenue,
                'reservation_revenue' => $reservationRevenue,
                'in_store_revenue' => $inStoreRevenue,
                'total_orders' => $totalOrders,
                'total_items_sold' => $totalItemsSold,
                'average_order_value' => $averageOrderValue,
            ],
            'reservations' => $reservations,
            'inStoreSales' => $inStoreSales,
            'topProducts' => $topProducts,
        ];
    }
}
