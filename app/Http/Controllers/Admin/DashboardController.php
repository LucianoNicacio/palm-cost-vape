<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        $query = Reservation::query();

        // Apply date filter based on period
        switch ($period) {
            case 'today':
                $query->today();
                $periodLabel = 'Today';
                break;
            case 'week':
                $query->thisWeek();
                $periodLabel = 'This Week';
                break;
            case 'month':
                $query->thisMonth();
                $periodLabel = 'This Month';
                break;
            case 'year':
                $query->thisYear();
                $periodLabel = 'This Year';
                break;
            case 'custom':
                if ($request->filled(['start_date', 'end_date'])) {
                    $query->dateRange($request->start_date, $request->end_date);
                    $periodLabel = Carbon::parse($request->start_date)->format('M d')
                        . ' - '
                        . Carbon::parse($request->end_date)->format('M d, Y');
                } else {
                    $query->today();
                    $periodLabel = 'Today';
                }
                break;
            default:
                $query->today();
                $periodLabel = 'Today';
        }

        // Calculate stats
        $stats = [
            'total_reservations' => (clone $query)->count(),
            'completed_reservations' => (clone $query)->completed()->count(),
            'pending_reservations' => (clone $query)->status('pending')->count(),
            'total_revenue' => (clone $query)->completed()->sum('total_price'),
            'total_items_sold' => (clone $query)->completed()->sum('item_count'),
            'average_order_value' => (clone $query)->completed()->avg('total_price') ?? 0,
        ];

        // Revenue chart data (last 7 days)
        $dailyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyRevenue[] = [
                'date' => $date->format('M d'),
                'revenue' => round(
                    Reservation::forDate($date->toDateString())
                        ->completed()
                        ->sum('total_price'),
                    2
                ),
            ];
        }

        // Quick stats (all-time)
        $quickStats = [
            'total_customers' => Customer::count(),
            'new_customers_this_month' => Customer::newThisMonth()->count(),
            'low_stock_products' => Product::where('track_inventory', true)
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentReservations' => Reservation::with('customer')
                ->latest()
                ->take(10)
                ->get(),
            'statusCounts' => [
                'pending' => Reservation::status('pending')->count(),
                'ready' => Reservation::status('ready')->count(),
                'completed' => Reservation::status('completed')->count(),
                'cancelled' => Reservation::status('cancelled')->count(),
            ],
            'dailyRevenue' => $dailyRevenue,
            'quickStats' => $quickStats,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }
}