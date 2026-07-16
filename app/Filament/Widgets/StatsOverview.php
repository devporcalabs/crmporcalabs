<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Calculate Total Outstanding (Receivables)
        $totalInvoiced = Invoice::where('status', '!=', 'dibatalkan')->sum('grand_total');
        $totalPaid = Payment::sum('jumlah');
        $totalOutstanding = max(0, $totalInvoiced - $totalPaid);

        // 2. Calculate Revenue received this month
        $monthlyRevenue = Payment::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('jumlah');

        // 3. Calculate Expenses and Net Profit
        $totalExpenses = \App\Models\Expense::sum('nominal');
        $netProfit = $totalPaid - $totalExpenses;

        return [
            Stat::make('Total Piutang (Outstanding)', 'Rp ' . number_format($totalOutstanding, 0, ',', '.'))
                ->description('Total tagihan aktif yang belum dibayar')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($totalOutstanding > 0 ? 'warning' : 'success'),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Pembayaran diterima bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Pengeluaran Kas', 'Rp ' . number_format($totalExpenses, 0, ',', '.'))
                ->description('Total pengeluaran tercatat di CRM')
                ->descriptionIcon('heroicon-m-document-minus')
                ->color($totalExpenses > 0 ? 'warning' : 'success'),

            Stat::make('Estimasi Laba Bersih', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                ->description('Total Pendapatan - Pengeluaran')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
