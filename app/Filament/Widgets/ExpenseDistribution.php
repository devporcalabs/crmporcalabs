<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ExpenseDistribution extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '275px';

    protected static ?string $heading = 'Distribusi Pengeluaran (Berdasarkan Kategori)';

    protected function getData(): array
    {
        $freelancerSum = (float) \App\Models\Expense::where('kategori', 'freelancer')->sum('nominal');
        $hardwareSum = (float) \App\Models\Expense::where('kategori', 'hardware')->sum('nominal');
        $softwareSum = (float) \App\Models\Expense::where('kategori', 'software')->sum('nominal');
        $operasionalSum = (float) \App\Models\Expense::where('kategori', 'operasional')->sum('nominal');
        $lainLainSum = (float) \App\Models\Expense::where('kategori', 'lain_lain')->sum('nominal');

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran (Rp)',
                    'data' => [
                        $freelancerSum,
                        $hardwareSum,
                        $softwareSum,
                        $operasionalSum,
                        $lainLainSum,
                    ],
                    'backgroundColor' => [
                        '#8b5cf6', // Jasa Freelancer (Purple)
                        '#f59e0b', // Alat / Hardware (Amber)
                        '#0ea5e9', // Software & Server (Sky)
                        '#64748b', // Operasional (Slate)
                        '#ef4444', // Lain-lain (Red)
                    ],
                ],
            ],
            'labels' => [
                'Jasa Freelancer',
                'Alat / Hardware',
                'Software & Server',
                'Operasional',
                'Lain-lain',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
