<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportReservationsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private $startDate,
        private $endDate,
    ) {}

    public function title(): string
    {
        return 'Reservations';
    }

    public function headings(): array
    {
        return ['Date', 'Confirmation #', 'Customer', 'Items', 'Subtotal', 'Tax', 'Discount', 'Total'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return Reservation::completed()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    public function map($reservation): array
    {
        return [
            $reservation->created_at->format('M d, Y g:ia'),
            $reservation->confirmation_number,
            $reservation->customer->name ?? 'Unknown',
            $reservation->item_count,
            '$' . number_format($reservation->subtotal, 2),
            '$' . number_format($reservation->tax_amount, 2),
            '$' . number_format($reservation->reward_discount, 2),
            '$' . number_format($reservation->total_price, 2),
        ];
    }
}
