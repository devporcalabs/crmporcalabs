<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueInvoices extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Invoice Mendekati / Lewat Jatuh Tempo';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereNotIn('status', ['lunas', 'dibatalkan'])
                    ->orderBy('jatuh_tempo', 'asc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor Invoice')
                    ->default('DRAFT')
                    ->badge(fn ($record) => $record->status === 'draft')
                    ->color('gray'),

                TextColumn::make('client.nama')
                    ->label('Klien')
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y'),

                TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : 'warning')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'terkirim' => 'info',
                        'dibayar_sebagian' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'terkirim' => 'Terkirim',
                        'dibayar_sebagian' => 'Bayar Sebagian',
                    }),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR'),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->state(fn ($record) => $record->sisa_tagihan)
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
