<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Quotation;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status !== 'menjadi_invoice'),
            Actions\DeleteAction::make(),

            // Convert to Invoice Action
            Actions\Action::make('convert_to_invoice')
                ->label('Terbitkan Invoice')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'menjadi_invoice' && $this->record->status !== 'draft')
                ->requiresConfirmation()
                ->modalHeading('Konversi Penawaran ke Invoice')
                ->modalDescription('Apakah Anda yakin ingin menerbitkan Invoice dari penawaran ini? Aksi ini akan membuat Invoice baru dengan status Draft.')
                ->action(function () {
                    $record = $this->record;
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
            Actions\Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('quotation.download-pdf', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status !== 'draft'),

            // Client Preview Action
            Actions\Action::make('client_preview')
                ->label('Preview Klien')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->url(fn () => URL::signedRoute('quotation.public-preview', ['quotation' => $this->record]))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status !== 'draft'),

            // Send WA Action
            Actions\Action::make('send_wa')
                ->label('Kirim WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->url(function () {
                    $record = $this->record;
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
                ->visible(fn () => $this->record->status !== 'draft'),
        ];
    }
}
