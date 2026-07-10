<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Actions\Action;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Aplikasi';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Decode bank accounts JSON if present
        if (isset($settings['bank_accounts'])) {
            $settings['bank_accounts'] = json_decode($settings['bank_accounts'], true) ?? [];
        }

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Pengaturan')
                    ->tabs([
                        Tabs\Tab::make('Profil Perusahaan')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Nama Perusahaan')
                                    ->required(),

                                TextInput::make('company_npwp')
                                    ->label('NPWP Perusahaan'),

                                TextInput::make('company_phone')
                                    ->label('No. Telepon / WA')
                                    ->required(),

                                TextInput::make('company_email')
                                    ->label('Email Perusahaan')
                                    ->email()
                                    ->required(),

                                Textarea::make('company_address')
                                    ->label('Alamat Perusahaan')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tabs\Tab::make('Rekening Bank')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Repeater::make('bank_accounts')
                                    ->label('Daftar Rekening Bank / Metode Pembayaran')
                                    ->schema([
                                        TextInput::make('bank')
                                            ->label('Nama Bank / Metode')
                                            ->placeholder('Misal: Bank Mandiri, Midtrans, QRIS')
                                            ->required(),

                                        TextInput::make('number')
                                            ->label('Nomor Rekening (Opsional)')
                                            ->placeholder('Misal: 167-00-xxxxxx-x'),

                                        TextInput::make('holder')
                                            ->label('Atas Nama (Opsional)')
                                            ->placeholder('Misal: PT Porcalabs Digital Indonesia'),

                                        TextInput::make('payment_link')
                                            ->label('Link Pembayaran (Opsional)')
                                            ->placeholder('Misal: https://pay.midtrans.com/...')
                                            ->url()
                                            ->columnSpan(3),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->itemLabel(fn (array $state): ?string => ($state['bank'] ?? '') . ' — ' . ($state['number'] ?? ($state['payment_link'] ?? ''))),
                            ]),

                        Tabs\Tab::make('Default & Tanda Tangan')
                            ->icon('heroicon-o-document-check')
                            ->schema([
                                TextInput::make('invoice_prefix')
                                    ->label('Prefix Nomor Invoice')
                                    ->default('INV')
                                    ->required(),

                                TextInput::make('default_ppn')
                                    ->label('Default PPN (%)')
                                    ->numeric()
                                    ->default(11.00)
                                    ->required(),

                                TextInput::make('wa_confirmation_number')
                                    ->label('Nomor WA Konfirmasi (Format: 62...)')
                                    ->required()
                                    ->helperText('Nomor WhatsApp yang dituju klien saat konfirmasi pembayaran.'),

                                TextInput::make('digital_signature_name')
                                    ->label('Nama Penandatangan')
                                    ->required()
                                    ->helperText('Nama yang dicetak di bawah tanda tangan PDF.'),

                                TextInput::make('digital_signature_title')
                                    ->label('Jabatan Penandatangan')
                                    ->required()
                                    ->helperText('Jabatan penandatangan, misal: Direktur Utama.'),
                            ])->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Encode bank accounts list to JSON string
        if (isset($data['bank_accounts'])) {
            $data['bank_accounts'] = json_encode($data['bank_accounts']);
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        Notification::make()
            ->success()
            ->title('Pengaturan disimpan!')
            ->body('Detail profil perusahaan dan defaults berhasil diperbarui.')
            ->send();
    }
}
