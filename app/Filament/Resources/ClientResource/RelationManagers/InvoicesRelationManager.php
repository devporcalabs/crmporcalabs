<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\InvoiceResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Daftar Invoice';

    public function form(Form $form): Form
    {
        // We handle invoice creation and editing through the main InvoiceResource
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor')
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->default('DRAFT')
                    ->badge(fn ($record) => $record->status === 'draft')
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : null)
                    ->weight(fn ($record) => $record->is_overdue ? 'bold' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'terkirim' => 'info',
                        'dibayar_sebagian' => 'warning',
                        'lunas' => 'success',
                        'dibatalkan' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'terkirim' => 'Terkirim',
                        'dibayar_sebagian' => 'Bayar Sebagian',
                        'lunas' => 'Lunas',
                        'dibatalkan' => 'Batal',
                    }),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_paid')
                    ->label('Terbayar')
                    ->money('IDR')
                    ->state(fn ($record) => $record->total_paid),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->state(fn ($record) => $record->sisa_tagihan)
                    ->weight('bold')
                    ->color(fn ($record) => $record->sisa_tagihan > 0 ? 'warning' : 'success'),
            ])
            ->filters([])
            ->headerActions([
                // Button to create invoice redirecting to the main InvoiceResource create page
                Tables\Actions\Action::make('buat_invoice')
                    ->label('Buat Invoice Baru')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => InvoiceResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id])),
            ])
            ->actions([
                Tables\Actions\Action::make('buka_invoice')
                    ->label('Buka')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
