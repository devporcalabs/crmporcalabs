<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyRevenue extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Grafik Pendapatan Bulanan (12 Bulan Terakhir)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Loop through the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            // Sum payments received in this month
            $revenue = Payment::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            $data[] = floatval($revenue);
            $labels[] = $date->format('M Y'); // e.g., "Jul 2026"
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Diterima (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(0, 86, 145, 0.2)',
                    'borderColor' => 'rgba(0, 86, 145, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // A line chart looks beautiful for revenue trends
    }
}
