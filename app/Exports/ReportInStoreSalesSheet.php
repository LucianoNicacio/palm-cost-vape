<?php

namespace App\Exports;

use App\Models\InStoreSale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportInStoreSalesSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private $startDate,
        private $endDate,
    ) {}

    public function title(): string
    {
        return 'In-Store Sales';
    }

    public function headings(): array
    {
        return ['Date', 'Sale #', 'Recorded By', 'Items', 'Total'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return InStoreSale::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with('recorder:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    public function map($sale): array
    {
        return [
            $sale->created_at->format('M d, Y g:ia'),
            '#' . $sale->id,
            $sale->recorder->name ?? 'Unknown',
            $sale->total_items,
            '$' . number_format($sale->total_amount, 2),
        ];
    }
}
