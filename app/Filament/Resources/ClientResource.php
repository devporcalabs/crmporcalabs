<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\Widgets;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Klien';
    
    protected static ?string $pluralModelLabel = 'Klien';
    
    protected static ?string $modelLabel = 'Klien';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Utama Klien')
                    ->description('Detail kontak utama dan informasi perusahaan klien.')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Kontak')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama kontak utama...'),

                        TextInput::make('perusahaan')
                            ->label('Nama Perusahaan / Instansi')
                            ->maxLength(255)
                            ->placeholder('Masukkan nama perusahaan (opsional)...'),

                        TextInput::make('no_wa')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->maxLength(20)
                            ->tel()
                            ->placeholder('Format: 628xxxxxxxxxx')
                            ->helperText('Gunakan format kode negara, misal: 628123456789'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Masukkan email...'),

                        TextInput::make('npwp')
                            ->label('NPWP (Opsional)')
                            ->maxLength(50)
                            ->placeholder('Format: 00.000.000.0-000.000'),
                    ])->columns(2),

                Section::make('Alamat & Catatan')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->placeholder('Alamat pengiriman invoice / kantor klien...'),

                        Textarea::make('catatan')
                            ->label('Catatan Internal')
                            ->rows(3)
                            ->placeholder('Catatan khusus untuk klien ini...'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kontak')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('perusahaan')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('no_wa')
                    ->label('WhatsApp')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('invoices_count')
                    ->label('Jumlah Invoice')
                    ->counts('invoices')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Arsip'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Arsip')
                    ->modalHeading('Arsipkan Klien')
                    ->before(function (Tables\Actions\DeleteAction $action, Client $record) {
                        if ($record->invoices()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Tidak dapat mengarsipkan klien')
                                ->body('Klien ini sudah memiliki data invoice.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotificationTitle('Klien berhasil diarsipkan.'),
                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan')
                    ->successNotificationTitle('Klien berhasil dipulihkan.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ClientStatsWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
