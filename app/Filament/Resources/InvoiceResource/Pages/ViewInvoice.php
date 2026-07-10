<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),

            Actions\Action::make('client_preview')
                ->label('Preview Klien')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->url(fn () => URL::signedRoute('invoice.public-preview', ['invoice' => $this->record]))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status !== 'draft'),
            
            Actions\Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('invoice.download-pdf', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status !== 'draft'),

            Actions\Action::make('send_wa')
                ->label('Kirim via WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->url(function () {
                    $record = $this->record;
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
                ->visible(fn () => $this->record->status !== 'draft'),
        ];
    }
}
