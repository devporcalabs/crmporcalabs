<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyRevenue extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '275px';

    protected static ?string $heading = 'Grafik Pendapatan vs Pengeluaran (12 Bulan Terakhir)';

    protected function getData(): array
    {
        $revenueData = [];
        $expenseData = [];
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

            // Sum expenses logged in this month
            $expense = \App\Models\Expense::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            $revenueData[] = floatval($revenue);
            $expenseData[] = floatval($expense);
            $labels[] = $date->format('M Y'); // e.g., "Jul 2026"
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Diterima (Rp)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(0, 86, 145, 0.08)',
                    'borderColor' => '#005691',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Pengeluaran Kas (Rp)',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(231, 29, 54, 0.04)',
                    'borderColor' => '#e71d36',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
