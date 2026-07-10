<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Riwayat Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')
                    ->label('Tanggal Pembayaran')
                    ->default(now())
                    ->required(),

                TextInput::make('jumlah')
                    ->label('Jumlah Pembayaran')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->rules([
                        function (Forms\Get $get, ?Model $record) {
                            return function (string $attribute, $value, \Closure $fail) use ($record) {
                                /** @var Invoice $invoice */
                                $invoice = $this->getOwnerRecord();
                                
                                // Sum all other payments except the one being edited
                                $existingPaymentsSum = $invoice->payments()
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->sum('jumlah');

                                $maxAllowed = max(0, $invoice->grand_total - $existingPaymentsSum);

                                if ($value > $maxAllowed) {
                                    $fail('Jumlah pembayaran melebihi sisa tagihan. Maksimum pembayaran: Rp ' . number_format($maxAllowed, 0, ',', '.'));
                                }
                            };
                        }
                    ]),

                Select::make('metode')
                    ->label('Metode Pembayaran')
                    ->options([
                        'transfer' => 'Transfer Bank',
                        'tunai' => 'Tunai / Cash',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('transfer')
                    ->required(),

                TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Misal: DP 50%, Pelunasan, dll.')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal')
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('metode')
                    ->label('Metode')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'transfer' => 'Transfer Bank',
                        'tunai' => 'Tunai / Cash',
                        'lainnya' => 'Lainnya',
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->default('-')
                    ->searchable(),
            ])
            ->filters([])
            ->headerActions([
                // Prevent adding payment if invoice is draft or cancelled or already lunas
                Tables\Actions\CreateAction::make()
                    ->label('Catat Pembayaran')
                    ->visible(fn () => in_array($this->getOwnerRecord()->status, ['terkirim', 'dibayar_sebagian']) && $this->getOwnerRecord()->sisa_tagihan > 0)
                    ->successNotificationTitle('Pembayaran berhasil dicatat.'),
            ])
            ->actions([
                Tables\Actions\Action::make('download_kuitansi')
                    ->label('Kuitansi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (\App\Models\Payment $record) => route('payment.download-kuitansi', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->successNotificationTitle('Pembayaran berhasil diubah.'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->successNotificationTitle('Pembayaran berhasil dihapus.'),
            ])
            ->bulkActions([]);
    }
}
