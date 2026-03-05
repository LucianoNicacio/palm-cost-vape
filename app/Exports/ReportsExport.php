<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements WithMultipleSheets
{
    public function __construct(
        private $startDate,
        private $endDate,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        return [
            'Summary' => new ReportSummarySheet($this->startDate, $this->endDate, $this->periodLabel),
            'Reservations' => new ReportReservationsSheet($this->startDate, $this->endDate),
            'In-Store Sales' => new ReportInStoreSalesSheet($this->startDate, $this->endDate),
        ];
    }
}
