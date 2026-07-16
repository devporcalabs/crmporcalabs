<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Freelancer & Project Cost';

    protected static ?string $navigationLabel = 'Pengaturan Pengeluaran';

    protected static ?string $pluralModelLabel = 'Pengeluaran';

    protected static ?string $modelLabel = 'Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pengeluaran Kas')
                    ->schema([
                        TextInput::make('nomor_pengeluaran')
                            ->label('Nomor Pengeluaran')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Otomatis saat disimpan')
                            ->maxLength(255),

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

                        Select::make('invoice_id')
                            ->label('Project Klien (Invoice)')
                            ->relationship('invoice', 'nomor')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih project klien (opsional)'),

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
                            ->maxLength(255)
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
                            ->placeholder('Contoh: Transfer BSI, Cash, CC')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('bukti_nota')
                            ->label('Foto Nota / Struk / Bukti Transfer')
                            ->image()
                            ->maxSize(2048)
                            ->directory('expense_receipts'),

                        Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    })
                    ->sortable(),

                TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('invoice.nomor')
                    ->label('Invoice Klien')
                    ->searchable()
                    ->placeholder('Umum / Non-Proyek'),

                TextColumn::make('freelancer.nama')
                    ->label('Freelancer')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'freelancer' => 'Jasa Freelancer',
                        'hardware' => 'Alat / Hardware',
                        'software' => 'Software & Server',
                        'operasional' => 'Operasional',
                        'lain_lain' => 'Lain-lain',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
