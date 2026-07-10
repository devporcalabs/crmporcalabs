<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Client;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoice';

    protected static ?string $modelLabel = 'Invoice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // Left column: Main Invoice Info
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Invoice')
                                    ->schema([
                                        Select::make('client_id')
                                            ->label('Klien')
                                            ->relationship('client', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(fn (?Model $record) => self::isLocked($record))
                                            ->default(request()->query('client_id')),

                                        TextInput::make('nomor')
                                            ->label('Nomor Invoice')
                                            ->disabled() // Nomor is auto-generated or manually overridden
                                            ->dehydrated()
                                            ->placeholder('Otomatis saat status Terkirim')
                                            ->maxLength(255),

                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Terbit')
                                            ->required()
                                            ->default(now())
                                            ->disabled(fn (?Model $record) => self::isLocked($record)),

                                        DatePicker::make('jatuh_tempo')
                                            ->label('Tanggal Jatuh Tempo')
                                            ->required()
                                            ->default(now()->addDays(14))
                                            ->disabled(fn (?Model $record) => self::isLocked($record)),
                                    ])->columns(2),

                                Section::make('Daftar Item Tagihan')
                                    ->schema([
                                        Repeater::make('items')
                                            ->relationship('items')
                                            ->schema([
                                                TextInput::make('deskripsi')
                                                    ->label('Deskripsi Item')
                                                    ->required()
                                                    ->columnSpan(5),

                                                TextInput::make('qty')
                                                    ->label('Qty')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set))
                                                    ->columnSpan(2),

                                                TextInput::make('satuan')
                                                    ->label('Satuan')
                                                    ->placeholder('pcs/bulan')
                                                    ->columnSpan(2),

                                                TextInput::make('harga_satuan')
                                                    ->label('Harga Satuan')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->default(0)
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set))
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(12)
                                            ->defaultItems(1)
                                            ->disabled(fn (?Model $record) => self::isLocked($record))
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set))
                                            ->orderColumn('urutan')
                                            ->itemLabel(fn (array $state): ?string => $state['deskripsi'] ?? null),
                                    ]),
                            ])->columnSpan(2),

                        // Right column: Calculations & Status
                        Grid::make(1)
                            ->schema([
                                Section::make('Perhitungan & Diskon')
                                    ->schema([
                                        Select::make('diskon_tipe')
                                            ->label('Tipe Diskon')
                                            ->options([
                                                'persen' => 'Persentase (%)',
                                                'nominal' => 'Nominal (Rp)',
                                            ])
                                            ->default('persen')
                                            ->live()
                                            ->disabled(fn (?Model $record) => self::isLocked($record))
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),

                                        TextInput::make('diskon_nilai')
                                            ->label('Nilai Diskon')
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->disabled(fn (?Model $record) => self::isLocked($record))
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),

                                        TextInput::make('ppn_persen')
                                            ->label('PPN (%)')
                                            ->numeric()
                                            ->default(11.00)
                                            ->live()
                                            ->disabled(fn (?Model $record) => self::isLocked($record))
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),

                                    ])->columns(1),

                                Section::make('Ringkasan Tagihan')
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->prefix('Rp'),

                                        TextInput::make('total_diskon')
                                            ->label('Total Diskon')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->prefix('Rp'),

                                        TextInput::make('total_ppn')
                                            ->label('Total PPN')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->prefix('Rp'),

                                        TextInput::make('grand_total')
                                            ->label('Grand Total')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->prefix('Rp')
                                            ->extraInputAttributes(['class' => 'font-bold text-lg']),
                                    ])->columns(1),

                                Section::make('Status & Catatan')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status Invoice')
                                            ->options(function (?Model $record) {
                                                // If invoice has payments, we cannot set it manually back to draft/cancelled
                                                if ($record && $record->payments()->exists()) {
                                                    return [
                                                        'dibayar_sebagian' => 'Dibayar Sebagian',
                                                        'lunas' => 'Lunas',
                                                    ];
                                                }
                                                return [
                                                    'draft' => 'Draft',
                                                    'terkirim' => 'Terkirim',
                                                    'dibatalkan' => 'Dibatalkan',
                                                ];
                                            })
                                            ->default('draft')
                                            ->required()
                                            ->disabled(fn (?Model $record) => $record && $record->payments()->exists()),

                                        Textarea::make('catatan')
                                            ->label('Catatan Pembayaran')
                                            ->placeholder('Syarat & ketentuan transfer bank dsb...')
                                            ->rows(2),
                                    ])->columns(1),
                            ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->default('DRAFT')
                    ->badge(fn ($record) => $record->status === 'draft')
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('client.nama')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.perusahaan')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    })
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_paid')
                    ->label('Terbayar')
                    ->money('IDR')
                    ->state(fn ($record) => $record->total_paid)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->state(fn ($record) => $record->sisa_tagihan)
                    ->color(fn ($record) => $record->sisa_tagihan > 0 ? 'warning' : 'success')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'terkirim' => 'Terkirim',
                        'dibayar_sebagian' => 'Bayar Sebagian',
                        'lunas' => 'Lunas',
                        'dibatalkan' => 'Batal',
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->label('Lewat Jatuh Tempo')
                    ->query(fn (Builder $query) => $query->where('jatuh_tempo', '<', now())->whereNotIn('status', ['lunas', 'dibatalkan'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
                
                // Custom duplicate action
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function (Invoice $record) {
                        $newInvoice = $record->replicate([
                            'nomor', 'status', 'subtotal', 'total_diskon', 'total_ppn', 'grand_total'
                        ]);
                        $newInvoice->status = 'draft';
                        $newInvoice->nomor = null;
                        $newInvoice->tanggal = now();
                        $newInvoice->jatuh_tempo = now()->addDays(14);
                        $newInvoice->save();

                        foreach ($record->items as $item) {
                            $newItem = $item->replicate(['invoice_id']);
                            $newInvoice->items()->save($newItem);
                        }

                        $newInvoice->calculateTotals();
                        $newInvoice->save();

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Invoice berhasil diduplikasi!')
                            ->body('Invoice baru dibuat sebagai Draft.')
                            ->send();
                    }),

                // Download PDF Action
                Tables\Actions\Action::make('download_pdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Invoice $record) => route('invoice.download-pdf', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'draft'),

                // Client Preview Action
                Tables\Actions\Action::make('client_preview')
                    ->label('Preview Klien')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->url(fn (Invoice $record) => URL::signedRoute('invoice.public-preview', ['invoice' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'draft'),

                // Send WA helper action
                Tables\Actions\Action::make('send_wa')
                    ->label('Kirim WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->url(function (Invoice $record) {
                        $client = $record->client;
                        $phone = preg_replace('/[^0-9]/', '', $client->no_wa);
                        if (!str_starts_with($phone, '62') && str_starts_with($phone, '0')) {
                            $phone = '62' . substr($phone, 1);
                        }

                        $amount = 'Rp ' . number_format($record->sisa_tagihan, 0, ',', '.');
                        $previewUrl = URL::signedRoute('invoice.public-preview', ['invoice' => $record->id]);
                        $text = "Halo {$client->nama},\n\nBerikut kami lampirkan tagihan Invoice *{$record->nomor}* dengan sisa tagihan sebesar *{$amount}*.\nAnda dapat melihat detail tagihan dan mengunduh PDF melalui tautan berikut:\n{$previewUrl}\n\nMohon lakukan pembayaran sesuai detail rekening transfer yang tertera pada dokumen. Terima kasih.\n\n- PT Porcalabs Digital Indonesia";
                        return "https://wa.me/{$phone}?text=" . urlencode($text);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'draft'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    /**
     * Check if fields should be locked (disabled).
     */
    protected static function isLocked(?Model $record): bool
    {
        return false;
    }

    /**
     * Compute totals dynamically during form editing.
     */
    public static function updateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $qty = floatval($item['qty'] ?? 0);
            $hargaSatuan = floatval($item['harga_satuan'] ?? 0);
            $subtotal += $qty * $hargaSatuan;
        }

        $set('subtotal', $subtotal);

        $diskonTipe = $get('diskon_tipe') ?? 'persen';
        $diskonNilai = floatval($get('diskon_nilai') ?? 0);
        $totalDiskon = 0;

        if ($diskonTipe === 'persen') {
            $totalDiskon = ($subtotal * $diskonNilai) / 100;
        } else {
            $totalDiskon = $diskonNilai;
        }
        $set('total_diskon', $totalDiskon);

        $dpp = max(0, $subtotal - $totalDiskon);
        $ppnPersen = floatval($get('ppn_persen') ?? 11.00);
        $totalPpn = ($dpp * $ppnPersen) / 100;
        $set('total_ppn', $totalPpn);

        $grandTotal = $dpp + $totalPpn;
        $set('grand_total', $grandTotal);
    }
}
