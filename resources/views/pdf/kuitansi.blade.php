<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran {{ $payment->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .receipt-container {
            border: 2px solid #005691;
            padding: 20px;
            background-color: #ffffff;
            position: relative;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #005691;
            padding-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
            border: none;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #005691;
        }
        .company-details {
            font-size: 9px;
            color: #555555;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            color: #005691;
            text-align: right;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .receipt-number {
            font-size: 11px;
            font-weight: bold;
            color: #111111;
            text-align: right;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .content-table td {
            padding: 10px 5px;
            vertical-align: top;
        }
        .field-label {
            width: 25%;
            font-size: 12px;
            color: #555555;
            font-weight: bold;
        }
        .field-separator {
            width: 2%;
            font-size: 12px;
            color: #111111;
        }
        .field-value {
            width: 73%;
            font-size: 12px;
            color: #111111;
            border-bottom: 1px dotted #bbbbbb;
        }
        .amount-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .amount-box-table td {
            vertical-align: middle;
            border: none;
        }
        .amount-display {
            background-color: #f0f7fc;
            border: 1px solid #005691;
            color: #005691;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 4px;
        }
        .terbilang-value {
            font-style: italic;
            font-weight: bold;
            background-color: #f9f9f9;
            padding: 8px 10px;
            border-radius: 3px;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bottom-table td {
            vertical-align: top;
            border: none;
        }
        .summary-notes {
            width: 60%;
            font-size: 11px;
            color: #555555;
        }
        .signature-box {
            width: 40%;
            text-align: center;
        }
        .signature-date {
            font-size: 11px;
            margin-bottom: 5px;
        }
        .signature-title {
            margin-bottom: 50px;
            font-size: 11px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 12px;
        }
        .signature-role {
            font-size: 11px;
            color: #555555;
        }
        .remaining-balance {
            font-size: 11px;
            margin-top: 10px;
            padding: 5px 8px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 3px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <div style="margin-bottom: 8px;">
                        <img src="{{ public_path('images/Logo n Text Horizontal Blue.png') }}" alt="Logo" style="height: 32px;">
                    </div>
                    <div class="company-details">
                        {{ $companyAddress }}<br>
                        Email: {{ $companyEmail }} | Telp: {{ $companyPhone }}<br>
                        NPWP: {{ $companyNpwp }}
                    </div>
                </td>
                <td>
                    <h1 class="receipt-title">Kuitansi Pembayaran</h1>
                    <div class="receipt-number">Nomor: KUI/{{ $payment->id }}/{{ str_replace('/', '_', $invoice->nomor ?? 'DRAFT') }}</div>
                </td>
            </tr>
        </table>

        <!-- Receipt Form Fields -->
        <table class="content-table">
            <tr>
                <td class="field-label">Telah Diterima Dari</td>
                <td class="field-separator">:</td>
                <td class="field-value" style="font-weight: bold;">{{ $invoice->client->perusahaan ?? $invoice->client->nama }}</td>
            </tr>
            <tr>
                <td class="field-label">Uang Sejumlah</td>
                <td class="field-separator">:</td>
                <td class="field-value terbilang-value">{{ $terbilangText }}</td>
            </tr>
            <tr>
                <td class="field-label">Untuk Pembayaran</td>
                <td class="field-separator">:</td>
                <td class="field-value">
                    Pembayaran {{ $payment->keterangan ?? 'angsuran/pelunasan' }} untuk tagihan 
                    <strong>Invoice {{ $invoice->nomor ?? 'DRAFT' }}</strong> 
                    (Tanggal: {{ $invoice->tanggal ? $invoice->tanggal->format('d M Y') : '-' }})
                </td>
            </tr>
        </table>

        <!-- Amount Display Row -->
        <table class="amount-box-table">
            <tr>
                <td style="width: 50%;">
                    <div class="amount-display">
                        Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                    </div>
                    
                    @if($sisaTagihan > 0)
                        <br>
                        <div class="remaining-balance">
                            Sisa Tagihan: <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                        </div>
                    @else
                        <br>
                        <div class="remaining-balance" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                            Status Tagihan: <strong>LUNAS</strong>
                        </div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right; font-size: 11px; color: #555555; font-style: italic;">
                    Pembayaran dicatat via: {{ $payment->metode === 'transfer' ? 'Transfer Bank' : ($payment->metode === 'tunai' ? 'Tunai / Cash' : 'Lainnya') }}
                </td>
            </tr>
        </table>

        <!-- Bottom Sign & Date -->
        <table class="bottom-table">
            <tr>
                <td class="summary-notes">
                    <strong>Catatan:</strong><br>
                    * Kuitansi ini merupakan bukti pembayaran yang sah setelah dana berhasil diterima di rekening kami.<br>
                    * Terima kasih atas kepercayaan Anda bekerja sama dengan {{ $companyName }}.
                </td>
                <td class="signature-box">
                    <div class="signature-date">Bekasi, {{ $payment->tanggal ? $payment->tanggal->format('d M Y') : now()->format('d M Y') }}</div>
                    <div class="signature-title">Penerima,</div>
                    <div class="signature-name">{{ $signatureName }}</div>
                    <div class="signature-role">{{ $signatureTitle }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
