<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Pengeluaran Proyek';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('nomor_pengeluaran')
                            ->label('Nomor Pengeluaran')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Otomatis saat disimpan'),

                        Select::make('kategori')
                            ->label('Kategori Pengeluaran')
                            ->options([
                                'freelancer' => 'Jasa Freelancer',
                                'hardware' => 'Alat / Hardware',
                                'software' => 'Software & Lisensi Server',
                                'operasional' => 'Operasional & Transport',
                                'lain_lain' => 'Lain-lain',
                            ])
                            ->required()
                            ->live(),

                        Select::make('freelancer_id')
                            ->label('Penerima Freelancer')
                            ->relationship('freelancer', 'nama')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih freelancer')
                            ->visible(fn (Forms\Get $get) => $get('kategori') === 'freelancer')
                            ->required(fn (Forms\Get $get) => $get('kategori') === 'freelancer'),

                        TextInput::make('keperluan')
                            ->label('Keperluan / Deskripsi')
                            ->required()
                            ->placeholder('Contoh: Pembelian 10 Pcs RFID Reader MFRC522')
                            ->columnSpanFull(),

                        DatePicker::make('tanggal')
                            ->label('Tanggal Pengeluaran')
                            ->required()
                            ->default(now()),

                        TextInput::make('nominal')
                            ->label('Jumlah Uang')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(0),

                        TextInput::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->placeholder('Contoh: Transfer BSI, Cash')
                            ->required(),

                        FileUpload::make('bukti_nota')
                            ->label('Foto Nota / Struk')
                            ->image()
                            ->maxSize(2048)
                            ->directory('expense_receipts'),

                        Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('keperluan')
            ->description(function () {
                $invoice = $this->getOwnerRecord();
                $totalInvoiced = (float) $invoice->grand_total;
                $totalExpense = (float) $invoice->expenses()->sum('nominal');
                $netProfit = $totalInvoiced - $totalExpense;

                return 'Nilai Tagihan Klien: Rp ' . number_format($totalInvoiced, 0, ',', '.') . 
                       ' | Total Beban Pengeluaran: Rp ' . number_format($totalExpense, 0, ',', '.') . 
                       ' | Margin Laba Bersih Proyek: Rp ' . number_format($netProfit, 0, ',', '.');
            })
            ->columns([
                TextColumn::make('nomor_pengeluaran')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'freelancer' => 'purple',
                        'hardware' => 'warning',
                        'software' => 'info',
                        'operasional' => 'gray',
                        'lain_lain' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'freelancer' => 'Jasa Freelancer',
                        'hardware' => 'Alat / Hardware',
                        'software' => 'Software & Server',
                        'operasional' => 'Operasional',
                        'lain_lain' => 'Lain-lain',
                    }),

                TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),

                TextColumn::make('freelancer.nama')
                    ->label('Freelancer')
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pengeluaran Proyek'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                
                Tables\Actions\Action::make('download_pdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Expense $record) => route('expense.download-pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
