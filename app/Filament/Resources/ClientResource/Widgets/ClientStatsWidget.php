<?php

namespace App\Filament\Resources\ClientResource\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class ClientStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record instanceof Client) {
            return [];
        }

        // Calculate totals for active invoices (exclude cancelled 'dibatalkan')
        $invoices = $this->record->invoices()->where('status', '!=', 'dibatalkan')->get();
        
        $totalInvoiced = $invoices->sum('grand_total');
        $totalPaid = $invoices->sum(fn ($i) => $i->total_paid);
        $outstanding = max(0, $totalInvoiced - $totalPaid);

        return [
            Stat::make('Total Tagihan', 'Rp ' . number_format($totalInvoiced, 0, ',', '.'))
                ->description('Total tagihan aktif'),
            Stat::make('Total Dibayar', 'Rp ' . number_format($totalPaid, 0, ',', '.'))
                ->description('Dana yang telah diterima')
                ->color('success'),
            Stat::make('Outstanding (Sisa Piutang)', 'Rp ' . number_format($outstanding, 0, ',', '.'))
                ->description('Sisa tagihan belum lunas')
                ->color($outstanding > 0 ? 'warning' : 'success'),
        ];
    }
}
