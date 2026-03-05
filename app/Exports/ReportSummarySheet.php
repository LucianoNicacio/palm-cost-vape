<?php

namespace App\Exports;

use App\Models\InStoreSale;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSummarySheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private $startDate,
        private $endDate,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true]],
        ];
    }

    public function array(): array
    {
        $reservationRevenue = (float) Reservation::completed()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('total_price');

        $inStoreRevenue = (float) InStoreSale::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('total_amount');

        $reservationCount = Reservation::completed()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        $inStoreCount = InStoreSale::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        $totalOrders = $reservationCount + $inStoreCount;
        $totalRevenue = $reservationRevenue + $inStoreRevenue;

        $totalItemsSold = (int) Reservation::completed()
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->sum('item_count')
            + (int) InStoreSale::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->sum('total_items');

        $rows = [
            ['Sales Report - ' . $this->periodLabel],
            [],
            ['Total Revenue', '$' . number_format($totalRevenue, 2)],
            ['Reservation Revenue', '$' . number_format($reservationRevenue, 2)],
            ['In-Store Sales Revenue', '$' . number_format($inStoreRevenue, 2)],
            ['Total Orders', $totalOrders],
            ['Total Items Sold', $totalItemsSold],
            ['Average Order Value', '$' . number_format($totalOrders > 0 ? $totalRevenue / $totalOrders : 0, 2)],
            [],
            [],
            ['Top Products', 'Qty Sold', 'Revenue'],
        ];

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
        ", [$this->startDate, $this->endDate, $this->startDate, $this->endDate]);

        foreach ($topProducts as $product) {
            $rows[] = [$product->name, $product->total_quantity, '$' . number_format($product->total_revenue, 2)];
        }

        return $rows;
    }
}
