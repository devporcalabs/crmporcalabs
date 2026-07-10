<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Invoice;
use App\Models\Quotation;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Quotation';

    protected static ?string $pluralModelLabel = 'Quotation';

    protected static ?string $modelLabel = 'Quotation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // Left column: Main Quotation Info
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Quotation')
                                    ->schema([
                                        Select::make('client_id')
                                            ->label('Klien')
                                            ->relationship('client', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(request()->query('client_id')),

                                        TextInput::make('nomor')
                                            ->label('Nomor Quotation')
                                            ->disabled()
                                            ->dehydrated()
                                            ->placeholder('Otomatis saat status Terkirim')
                                            ->maxLength(255),

                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Penawaran')
                                            ->required()
                                            ->default(now()),

                                        DatePicker::make('berlaku_hingga')
                                            ->label('Berlaku Hingga')
                                            ->required()
                                            ->default(now()->addDays(14)),
                                    ])->columns(2),

                                Section::make('Daftar Item Penawaran')
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
                                                    ->live(onBlur: true)
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
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set))
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(12)
                                            ->defaultItems(1)
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
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),

                                        TextInput::make('diskon_nilai')
                                            ->label('Nilai Diskon')
                                            ->numeric()
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),

                                        TextInput::make('ppn_persen')
                                            ->label('PPN (%)')
                                            ->numeric()
                                            ->default(11.00)
                                            ->live(onBlur: true)
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
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'terkirim' => 'Terkirim',
                                                'disetujui' => 'Disetujui',
                                                'ditolak' => 'Ditolak',
                                                'menjadi_invoice' => 'Menjadi Invoice',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->disabled(fn (?Model $record) => $record && $record->status === 'menjadi_invoice'),

                                        Textarea::make('catatan')
                                            ->label('Catatan Ketentuan')
                                            ->placeholder('Ketentuan penawaran harga dsb...')
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
                    ->label('Nomor Quotation')
                    ->searchable()
                    ->default('DRAFT')
                    ->badge(fn ($record) => $record->status === 'draft')
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('client.nama')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('berlaku_hingga')
                    ->label('Berlaku Hingga')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->berlaku_hingga && $record->berlaku_hingga->isPast() && $record->status !== 'menjadi_invoice' && $record->status !== 'disetujui' ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'terkirim' => 'info',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'menjadi_invoice' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'terkirim' => 'Terkirim',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'menjadi_invoice' => 'Menjadi Invoice',
                    })
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'terkirim' => 'Terkirim',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'menjadi_invoice' => 'Menjadi Invoice',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(fn ($record) => $record->status !== 'menjadi_invoice'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),

                // Convert to Invoice Action
                Tables\Actions\Action::make('convert_to_invoice')
                    ->label('Terbitkan Invoice')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn (Quotation $record) => $record->status !== 'menjadi_invoice' && $record->status !== 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Konversi Penawaran ke Invoice')
                    ->modalDescription('Apakah Anda yakin ingin menerbitkan Invoice dari penawaran ini? Aksi ini akan membuat Invoice baru dengan status Draft.')
                    ->action(function (Quotation $record) {
                        return DB::transaction(function () use ($record) {
                            // 1. Create Invoice
                            $invoice = Invoice::create([
                                'client_id' => $record->client_id,
                                'tanggal' => now(),
                                'jatuh_tempo' => now()->addDays(14),
                                'diskon_tipe' => $record->diskon_tipe,
                                'diskon_nilai' => $record->diskon_nilai,
                                'ppn_persen' => $record->ppn_persen,
                                'subtotal' => $record->subtotal,
                                'total_diskon' => $record->total_diskon,
                                'total_ppn' => $record->total_ppn,
                                'grand_total' => $record->grand_total,
                                'status' => 'draft',
                                'catatan' => $record->catatan,
                            ]);

                            // 2. Create Invoice Items
                            foreach ($record->items as $item) {
                                $invoice->items()->create([
                                    'deskripsi' => $item->deskripsi,
                                    'qty' => $item->qty,
                                    'satuan' => $item->satuan,
                                    'harga_satuan' => $item->harga_satuan,
                                    'urutan' => $item->urutan,
                                ]);
                            }

                            // 3. Update Quotation
                            $record->status = 'menjadi_invoice';
                            $record->invoice_id = $invoice->id;
                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Invoice Berhasil Diterbitkan!')
                                ->body('Invoice baru telah dibuat sebagai Draft.')
                                ->send();

                            return redirect()->to(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                        });
                    }),

                // Download PDF Action
                Tables\Actions\Action::make('download_pdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Quotation $record) => route('quotation.download-pdf', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'draft'),

                // Client Preview Action
                Tables\Actions\Action::make('client_preview')
                    ->label('Preview Klien')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->url(fn (Quotation $record) => URL::signedRoute('quotation.public-preview', ['quotation' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'draft'),

                // Send WA helper action
                Tables\Actions\Action::make('send_wa')
                    ->label('Kirim WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->url(function (Quotation $record) {
                        $client = $record->client;
                        $phone = preg_replace('/[^0-9]/', '', $client->no_wa);
                        if (!str_starts_with($phone, '62') && str_starts_with($phone, '0')) {
                            $phone = '62' . substr($phone, 1);
                        }

                        $amount = 'Rp ' . number_format($record->grand_total, 0, ',', '.');
                        $previewUrl = URL::signedRoute('quotation.public-preview', ['quotation' => $record->id]);
                        $text = "Halo {$client->nama},\n\nBerikut kami lampirkan dokumen Penawaran Harga *{$record->nomor}* sebesar *{$amount}*.\nAnda dapat melihat detail penawaran dan mengunduh PDF melalui tautan berikut:\n{$previewUrl}\n\nJika disetujui, mohon beri tahu kami agar dapat diterbitkan Invoice. Terima kasih.\n\n- PT Porcalabs Digital Indonesia";
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
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
