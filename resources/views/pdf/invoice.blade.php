<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->nomor ?? 'DRAFT' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
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
            font-size: 18px;
            font-weight: bold;
            color: #005691;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 10px;
            color: #555555;
        }
        .invoice-title-td {
            text-align: right;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #005691;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            border: none;
        }
        .bill-to-title {
            font-size: 11px;
            font-weight: bold;
            color: #777777;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .bill-to-name {
            font-size: 13px;
            font-weight: bold;
            color: #111111;
        }
        .bill-to-details {
            font-size: 11px;
            color: #444444;
        }
        .info-label {
            font-size: 11px;
            color: #555555;
            padding-right: 10px;
        }
        .info-value {
            font-size: 11px;
            font-weight: bold;
            color: #111111;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .status-lunas {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-belum-lunas {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status-batal {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #005691;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #005691;
        }
        .items-table td {
            padding: 8px 10px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table td {
            border: none;
            padding: 4px 10px;
            vertical-align: top;
        }
        .summary-label {
            text-align: right;
            font-size: 11px;
            color: #555555;
        }
        .summary-value {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            width: 120px;
        }
        .summary-grand-total {
            background-color: #f0f7fc;
            border-top: 1px solid #005691 !class;
            border-bottom: 2px double #005691 !class;
        }
        .summary-grand-total td {
            padding: 8px 10px;
        }
        .summary-grand-total .summary-label {
            font-size: 12px;
            font-weight: bold;
            color: #005691;
        }
        .summary-grand-total .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #005691;
        }
        .terbilang-box {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-style: italic;
            font-size: 11px;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .bottom-table td {
            vertical-align: top;
            border: none;
        }
        .payment-instructions {
            width: 60%;
        }
        .signature-box {
            width: 40%;
            text-align: center;
        }
        .payment-bank-item {
            margin-bottom: 8px;
            font-size: 11px;
        }
        .payment-bank-name {
            font-weight: bold;
            color: #005691;
        }
        .payment-confirmation {
            margin-top: 10px;
            font-size: 10px;
            color: #555555;
        }
        .signature-title {
            margin-bottom: 60px;
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
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header / Kop -->
    <table class="header-table">
        <tr>
            <td>
                <div style="margin-bottom: 8px;">
                    <img src="{{ public_path('images/Logo n Text Horizontal Blue.png') }}" alt="Logo" style="height: 35px;">
                </div>
                <div class="company-details">
                    {{ $companyAddress }}<br>
                    Email: {{ $companyEmail }} | Telp: {{ $companyPhone }}<br>
                    NPWP: {{ $companyNpwp }}
                </div>
            </td>
            <td class="invoice-title-td">
                <h1 class="invoice-title">INVOICE</h1>
                <div>
                    <span class="status-badge {{ $invoice->status === 'lunas' ? 'status-lunas' : ($invoice->status === 'dibatalkan' ? 'status-batal' : 'status-belum-lunas') }}">
                        {{ $invoice->status === 'lunas' ? 'LUNAS' : ($invoice->status === 'dibatalkan' ? 'BATAL' : 'BELUM LUNAS') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details: Bill to & Invoice Info -->
    <table class="meta-table">
        <tr>
            <td style="width: 55%;">
                <div class="bill-to-title">Ditagihkan Kepada:</div>
                <div class="bill-to-name">{{ $invoice->client->perusahaan ?? $invoice->client->nama }}</div>
                <div class="bill-to-details">
                    Attn: {{ $invoice->client->nama }}<br>
                    {{ $invoice->client->alamat ?? 'Alamat tidak dicantumkan.' }}<br>
                    @if($invoice->client->email) Email: {{ $invoice->client->email }} | @endif WA: {{ $invoice->client->no_wa }}
                    @if($invoice->client->npwp)<br>NPWP: {{ $invoice->client->npwp }} @endif
                </div>
            </td>
            <td style="width: 45%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label">No. Invoice</td>
                        <td class="info-value">: {{ $invoice->nomor ?? 'DRAFT' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Terbit</td>
                        <td class="info-value">: {{ $invoice->tanggal ? $invoice->tanggal->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jatuh Tempo</td>
                        <td class="info-value" style="color: {{ $invoice->is_overdue ? '#dc3545' : 'inherit' }};">: {{ $invoice->jatuh_tempo ? $invoice->jatuh_tempo->format('d M Y') : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 50%;">Deskripsi Tagihan</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Harga Satuan</th>
                <th style="width: 20%; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td style="text-align: center;">{{ number_format($item->qty, 0, ',', '.') }} {{ $item->satuan ?? '' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->qty * $item->harga_satuan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Calculations summary block -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Left side space or info -->
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                <!-- Space for notes / instructions -->
            </td>
            <!-- Right side summary totals -->
            <td style="width: 50%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Subtotal</td>
                        <td class="summary-value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->total_diskon > 0)
                        <tr>
                            <td class="summary-label">Diskon @if($invoice->diskon_tipe === 'persen')({{ number_format($invoice->diskon_nilai, 0) }}%)@endif</td>
                            <td class="summary-value" style="color: #dc3545;">-Rp {{ number_format($invoice->total_diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="summary-label">Dasar Pengenaan Pajak (DPP)</td>
                        <td class="summary-value">Rp {{ number_format(max(0, $invoice->subtotal - $invoice->total_diskon), 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->total_ppn > 0)
                        <tr>
                            <td class="summary-label">PPN ({{ number_format($invoice->ppn_persen, 0) }}%)</td>
                            <td class="summary-value">Rp {{ number_format($invoice->total_ppn, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="summary-grand-total">
                        <td class="summary-label">Grand Total</td>
                        <td class="summary-value">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->payments()->count() > 0)
                        <tr>
                            <td class="summary-label">Total Dibayar</td>
                            <td class="summary-value" style="color: #28a745;">-Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #333;">
                            <td class="summary-label" style="font-weight: bold; color: #111111;">Sisa Tagihan</td>
                            <td class="summary-value" style="font-weight: bold; font-size: 12px; color: #ffc107;">Rp {{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Terbilang Box -->
    <div class="terbilang-box">
        <strong>Terbilang:</strong> {{ $terbilangText }}
    </div>

    <!-- Bottom section: bank accounts & signature -->
    <table class="bottom-table">
        <tr>
            <td class="payment-instructions">
                <div style="font-weight: bold; margin-bottom: 8px; font-size: 11px;">Instruksi Pembayaran:</div>
                <div style="font-size: 11px; margin-bottom: 10px; color: #555555;">Mohon lakukan transfer ke salah satu rekening berikut:</div>
                
                @forelse($bankAccounts as $bank)
                    <div class="payment-bank-item" style="margin-bottom: 10px;">
                        <span class="payment-bank-name">{{ $bank['bank'] }}</span><br>
                        @if(!empty($bank['number']))
                            No. Rek: <strong>{{ $bank['number'] }}</strong><br>
                            A.N. {{ $bank['holder'] }}<br>
                        @endif
                        @if(!empty($bank['payment_link']))
                            Link: <a href="{{ $bank['payment_link'] }}" style="color: #005691; text-decoration: underline;">{{ $bank['payment_link'] }}</a>
                        @endif
                    </div>
                @empty
                    <div class="payment-bank-item" style="color: #dc3545;">
                        Rekening bank belum diatur. Silakan periksa halaman Pengaturan.
                    </div>
                @endforelse

                <div class="payment-confirmation">
                    * Mohon konfirmasi bukti transfer melalui WhatsApp ke nomor: <strong>+{{ $waConfirmationNumber }}</strong>
                </div>
            </td>
            <td class="signature-box">
                <div class="signature-title">Hormat Kami,</div>
                <div style="height: 50px;">
                    <!-- Placeholder space for digital signature / stamp -->
                </div>
                <div class="signature-name">{{ $signatureName }}</div>
                <div class="signature-role">{{ $signatureTitle }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
