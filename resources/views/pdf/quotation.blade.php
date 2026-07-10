<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotation->nomor ?? 'DRAFT' }}</title>
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
            border-top: 1px solid #005691;
            border-bottom: 2px double #005691;
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
                <h1 class="invoice-title">QUOTATION</h1>
                <div>
                    @php
                        $badgeClass = 'status-belum-lunas';
                        $statusLabel = 'DRAFT';
                        if ($quotation->status === 'menjadi_invoice') {
                            $badgeClass = 'status-lunas';
                            $statusLabel = 'TERBIT INVOICE';
                        } elseif ($quotation->status === 'disetujui') {
                            $badgeClass = 'status-lunas';
                            $statusLabel = 'DISETUJUI';
                        } elseif ($quotation->status === 'ditolak') {
                            $badgeClass = 'status-batal';
                            $statusLabel = 'DITOLAK';
                        } elseif ($quotation->status === 'terkirim') {
                            $badgeClass = 'status-belum-lunas';
                            $statusLabel = 'TERKIRIM';
                        }
                    @endphp
                    <span class="status-badge {{ $badgeClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details: Bill to & Quotation Info -->
    <table class="meta-table">
        <tr>
            <td style="width: 55%;">
                <div class="bill-to-title">Ditujukan Kepada:</div>
                <div class="bill-to-name">{{ $quotation->client->perusahaan ?? $quotation->client->nama }}</div>
                <div class="bill-to-details">
                    Attn: {{ $quotation->client->nama }}<br>
                    {{ $quotation->client->alamat ?? 'Alamat tidak dicantumkan.' }}<br>
                    @if($quotation->client->email) Email: {{ $quotation->client->email }} | @endif WA: {{ $quotation->client->no_wa }}
                    @if($quotation->client->npwp)<br>NPWP: {{ $quotation->client->npwp }} @endif
                </div>
            </td>
            <td style="width: 45%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label">No. Penawaran</td>
                        <td class="info-value">: {{ $quotation->nomor ?? 'DRAFT' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Terbit</td>
                        <td class="info-value">: {{ $quotation->tanggal ? $quotation->tanggal->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Berlaku Hingga</td>
                        <td class="info-value">: {{ $quotation->berlaku_hingga ? $quotation->berlaku_hingga->format('d M Y') : '-' }}</td>
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
                <th style="width: 50%;">Deskripsi Penawaran</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Harga Satuan</th>
                <th style="width: 20%; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
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
                        <td class="summary-value">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($quotation->total_diskon > 0)
                        <tr>
                            <td class="summary-label">Diskon @if($quotation->diskon_tipe === 'persen')({{ number_format($quotation->diskon_nilai, 0) }}%)@endif</td>
                            <td class="summary-value" style="color: #dc3545;">-Rp {{ number_format($quotation->total_diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="summary-label">Dasar Pengenaan Pajak (DPP)</td>
                        <td class="summary-value">Rp {{ number_format(max(0, $quotation->subtotal - $quotation->total_diskon), 0, ',', '.') }}</td>
                    </tr>
                    @if($quotation->total_ppn > 0)
                        <tr>
                            <td class="summary-label">PPN ({{ number_format($quotation->ppn_persen, 0) }}%)</td>
                            <td class="summary-value">Rp {{ number_format($quotation->total_ppn, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="summary-grand-total">
                        <td class="summary-label">Grand Total</td>
                        <td class="summary-value">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</td>
                    </tr>
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
                <div style="font-weight: bold; margin-bottom: 8px; font-size: 11px;">Ketentuan Penawaran:</div>
                <div style="font-size: 10px; color: #555555; line-height: 1.5;">
                    1. Penawaran harga ini berlaku hingga tanggal <strong>{{ $quotation->berlaku_hingga ? $quotation->berlaku_hingga->format('d M Y') : '-' }}</strong>.<br>
                    2. Harga yang tercantum di atas sudah termasuk PPN (11%) kecuali disebutkan berbeda.<br>
                    3. Pekerjaan akan mulai diproses setelah dokumen penawaran disetujui dan pembayaran DP diterima.<br>
                    4. Pembayaran dapat ditransfer ke rekening bank resmi perusahaan yang tertera di dokumen tagihan resmi kelak.
                </div>
            </td>
            <td class="signature-box">
                <div class="signature-title">Hormat Kami,</div>
                <div style="margin: 8px 0; text-align: center;">
                    @php
                        $verifyUrl = URL::signedRoute('quotation.verify', ['quotation' => $quotation->id]);
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($verifyUrl);
                    @endphp
                    <img src="{{ $qrCodeUrl }}" style="width: 70px; height: 70px; display: inline-block;">
                    <div style="font-size: 7px; color: #666666; margin-top: 4px;">Scan untuk verifikasi</div>
                </div>
                <div class="signature-name">{{ $signatureName }}</div>
                <div class="signature-role">{{ $signatureTitle }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
