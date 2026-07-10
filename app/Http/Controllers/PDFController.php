<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    /**
     * Download the invoice PDF.
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        
        // Fetch company profile settings
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');
        
        // Fetch bank accounts
        $bankAccountsJson = Setting::get('bank_accounts', '[]');
        $bankAccounts = json_decode($bankAccountsJson, true) ?? [];

        // Fetch signature info
        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        // Convert grand total to Indonesian words
        $terbilangText = ucwords($this->terbilang($invoice->grand_total)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.invoice', compact(
            'invoice',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'bankAccounts',
            'signatureName',
            'signatureTitle',
            'terbilangText'
        ));

        // Format filename: Invoice_INV_2026_VII_0042.pdf
        $cleanNumber = str_replace('/', '_', $invoice->nomor ?? 'DRAFT');
        return $pdf->download("Invoice_{$cleanNumber}.pdf");
    }

    /**
     * Preview the invoice in the HTML client portal.
     */
    public function previewInvoice(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');
        
        $bankAccountsJson = Setting::get('bank_accounts', '[]');
        $bankAccounts = json_decode($bankAccountsJson, true) ?? [];

        return view('invoice.portal', compact(
            'invoice',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'bankAccounts'
        ));
    }

    /**
     * Stream the invoice PDF inline.
     */
    public function streamPDF(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');
        
        $bankAccountsJson = Setting::get('bank_accounts', '[]');
        $bankAccounts = json_decode($bankAccountsJson, true) ?? [];

        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        $terbilangText = ucwords($this->terbilang($invoice->grand_total)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.invoice', compact(
            'invoice',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'bankAccounts',
            'signatureName',
            'signatureTitle',
            'terbilangText'
        ));

        $cleanNumber = str_replace('/', '_', $invoice->nomor ?? 'DRAFT');
        return $pdf->stream("Invoice_{$cleanNumber}.pdf");
    }

    /**
     * Download the quotation PDF.
     */
    public function downloadQuotation(Quotation $quotation)
    {
        $quotation->load(['client', 'items']);
        
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');
        
        $bankAccountsJson = Setting::get('bank_accounts', '[]');
        $bankAccounts = json_decode($bankAccountsJson, true) ?? [];

        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        $terbilangText = ucwords($this->terbilang($quotation->grand_total)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.quotation', compact(
            'quotation',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'bankAccounts',
            'signatureName',
            'signatureTitle',
            'terbilangText'
        ));

        $cleanNumber = str_replace('/', '_', $quotation->nomor ?? 'DRAFT');
        return $pdf->download("Quotation_{$cleanNumber}.pdf");
    }

    /**
     * Preview the quotation PDF in the browser inline.
     */
    public function previewQuotation(Quotation $quotation)
    {
        $quotation->load(['client', 'items']);
        
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');
        
        $bankAccountsJson = Setting::get('bank_accounts', '[]');
        $bankAccounts = json_decode($bankAccountsJson, true) ?? [];

        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        $terbilangText = ucwords($this->terbilang($quotation->grand_total)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.quotation', compact(
            'quotation',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'bankAccounts',
            'signatureName',
            'signatureTitle',
            'terbilangText'
        ));

        $cleanNumber = str_replace('/', '_', $quotation->nomor ?? 'DRAFT');
        return $pdf->stream("Quotation_{$cleanNumber}.pdf");
    }

    /**
     * Download the payment receipt (Kuitansi) PDF.
     */
    public function downloadKuitansi(Payment $payment)
    {
        $payment->load(['invoice.client']);
        $invoice = $payment->invoice;

        // Fetch company profile settings
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyNpwp = Setting::get('company_npwp', '');
        $waConfirmationNumber = Setting::get('wa_confirmation_number', '');

        // Fetch signature info
        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        // Convert payment amount to Indonesian words
        $terbilangText = ucwords($this->terbilang($payment->jumlah)) . ' Rupiah';

        // Calculate sisa tagihan at the moment of this payment (sum of payments up to this payment date)
        $previousPaymentsSum = $invoice->payments()
            ->where('tanggal', '<=', $payment->tanggal)
            ->where('id', '<=', $payment->id)
            ->sum('jumlah');
            
        $sisaTagihan = max(0, $invoice->grand_total - $previousPaymentsSum);

        $pdf = Pdf::loadView('pdf.kuitansi', compact(
            'payment',
            'invoice',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyNpwp',
            'waConfirmationNumber',
            'signatureName',
            'signatureTitle',
            'terbilangText',
            'sisaTagihan'
        ));

        $cleanNumber = str_replace('/', '_', $invoice->nomor ?? 'DRAFT');
        return $pdf->download("Kuitansi_{$payment->id}_{$cleanNumber}.pdf");
    }

    /**
     * Verify the authenticity of an invoice.
     */
    public function verifyInvoice(Invoice $invoice)
    {
        $invoice->load(['client', 'items']);
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        return view('invoice.verify', compact(
            'invoice',
            'companyName',
            'signatureName',
            'signatureTitle'
        ));
    }

    /**
     * Verify the authenticity of a quotation.
     */
    public function verifyQuotation(\App\Models\Quotation $quotation)
    {
        $quotation->load(['client', 'items']);
        $companyName = Setting::get('company_name', 'PT Porcalabs Digital Indonesia');
        $signatureName = Setting::get('digital_signature_name', 'Muhammad Fachry');
        $signatureTitle = Setting::get('digital_signature_title', 'Direktur Utama');

        return view('quotation.verify', compact(
            'quotation',
            'companyName',
            'signatureName',
            'signatureTitle'
        ));
    }

    /**
     * Helper method to convert numbers to Indonesian words (terbilang).
     */
    private function terbilang($angka)
    {
        $angka = abs((float) $angka);
        $baca = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp = "";
        
        if ($angka < 12) {
            $temp = " " . $baca[$angka];
        } else if ($angka < 20) {
            $temp = $this->terbilang($angka - 10) . " belas";
        } else if ($angka < 100) {
            $temp = $this->terbilang(floor($angka / 10)) . " puluh " . $this->terbilang($angka % 10);
        } else if ($angka < 200) {
            $temp = " seratus " . $this->terbilang($angka - 100);
        } else if ($angka < 1000) {
            $ratusan = floor($angka / 100);
            if ($ratusan == 1) {
                $temp = " seratus " . $this->terbilang($angka % 100);
            } else {
                $temp = $this->terbilang($ratusan) . " ratus " . $this->terbilang($angka % 100);
            }
        } else if ($angka < 2000) {
            $temp = " seribu " . $this->terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $ribuan = floor($angka / 1000);
            if ($ribuan == 1) {
                $temp = " seribu " . $this->terbilang($angka % 1000);
            } else {
                $temp = $this->terbilang($ribuan) . " ribu " . $this->terbilang($angka % 1000);
            }
        } else if ($angka < 1000000000) {
            $temp = $this->terbilang(floor($angka / 1000000)) . " juta " . $this->terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            $temp = $this->terbilang(floor($angka / 1000000000)) . " milyar " . $this->terbilang(fmod($angka, 1000000000));
        } else if ($angka < 1000000000000000) {
            $temp = $this->terbilang(floor($angka / 1000000000000)) . " trilyun " . $this->terbilang(fmod($angka, 1000000000000));
        }
        
        return trim(preg_replace('/\s+/', ' ', $temp));
    }
}
