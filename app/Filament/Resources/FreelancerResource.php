<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FreelancerResource\Pages;
use App\Models\Freelancer;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FreelancerResource extends Resource
{
    protected static ?string $model = Freelancer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Freelancer & Project Cost';

    protected static ?string $navigationLabel = 'Freelancer';

    protected static ?string $pluralModelLabel = 'Freelancer';

    protected static ?string $modelLabel = 'Freelancer';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Freelancer')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('no_wa')
                            ->label('No. WhatsApp (Format: 62...)')
                            ->required()
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('keahlian')
                            ->label('Keahlian / Skill')
                            ->placeholder('Contoh: Backend Developer, UI/UX Designer')
                            ->maxLength(255),

                        Textarea::make('rekening_bank')
                            ->label('Informasi Rekening Bank')
                            ->placeholder("Contoh:\nBank Mandiri\nNo. Rek: 1560024461123\nA.N. MUHAMMAD MITFAH")
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('no_wa')
                    ->label('No. WhatsApp')
                    ->searchable(),

                TextColumn::make('keahlian')
                    ->label('Keahlian')
                    ->searchable(),

                TextColumn::make('rekening_bank')
                    ->label('Rekening Bank')
                    ->limit(40)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
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
            'index' => Pages\ListFreelancers::route('/'),
            'create' => Pages\CreateFreelancer::route('/create'),
            'edit' => Pages\EditFreelancer::route('/{record}/edit'),
        ];
    }
}
