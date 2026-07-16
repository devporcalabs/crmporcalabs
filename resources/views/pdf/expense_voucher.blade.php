<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Voucher Pengeluaran — {{ $expense->nomor_pengeluaran }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-bottom: 2px solid #005691;
            padding-bottom: 10px;
        }
        .header-logo {
            width: 50%;
            vertical-align: middle;
        }
        .header-logo img {
            height: 38px;
        }
        .header-company {
            width: 50%;
            text-align: right;
            font-size: 10px;
            color: #555555;
            vertical-align: middle;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #005691;
            margin-bottom: 3px;
        }
        .title-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #005691;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 12px;
            color: #666666;
            margin: 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .details-table td.label {
            width: 25%;
            font-weight: bold;
            color: #555555;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .details-table td.value {
            width: 75%;
            border: 1px solid #e2e8f0;
        }
        .amount-box {
            background-color: #f0f7fc;
            border-left: 4px solid #005691;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .amount-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #555555;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .amount-value {
            font-size: 18px;
            font-weight: bold;
            color: #005691;
            margin-bottom: 5px;
        }
        .amount-terbilang {
            font-size: 11px;
            font-style: italic;
            color: #444444;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
        }
        .bottom-table td {
            vertical-align: top;
            border: none;
        }
        .signature-box {
            width: 40%;
            text-align: center;
            float: right;
        }
        .signature-title {
            font-size: 11px;
            color: #555555;
            margin-bottom: 10px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #111111;
        }
        .signature-role {
            font-size: 10px;
            color: #666666;
            margin-top: 2px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('images/Logo n Text Horizontal Blue.png')))
                    <img src="{{ public_path('images/Logo n Text Horizontal Blue.png') }}" alt="PorcaLabs Logo">
                @else
                    <span style="font-size: 20px; font-weight: bold; color: #005691;">{{ $companyName }}</span>
                @endif
            </td>
            <td class="header-company">
                <div class="company-name">{{ $companyName }}</div>
                @if(!empty($companyAddress)) <div>{{ $companyAddress }}</div> @endif
                <div>Telp: {{ $companyPhone }} | Email: {{ $companyEmail }}</div>
            </td>
        </tr>
    </table>

    <!-- Document Title -->
    <div class="title-container">
        <h1 class="doc-title">
            @if($expense->kategori === 'freelancer')
                Kuitansi Pembayaran Jasa Freelancer
            @else
                Voucher Pengeluaran Kas (Cash Out)
            @endif
        </h1>
        <p class="doc-number">No. Dokumen: <strong>{{ $expense->nomor_pengeluaran }}</strong></p>
    </div>

    <!-- Details -->
    <table class="details-table">
        <tr>
            <td class="label">Tanggal Transaksi</td>
            <td class="value">{{ $expense->tanggal ? $expense->tanggal->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kategori Pengeluaran</td>
            <td class="value">
                @switch($expense->kategori)
                    @case('freelancer') Jasa Freelancer @break
                    @case('hardware') Belanja Alat / Hardware @break
                    @case('software') Software & Lisensi Server @break
                    @case('operasional') Operasional & Transport @break
                    @default Lain-lain / Umum
                @endswitch
            </td>
        </tr>
        @if($expense->invoice)
            <tr>
                <td class="label">Alokasi Project</td>
                <td class="value">Invoice Klien: <strong>{{ $expense->invoice->nomor }}</strong> ({{ $expense->invoice->client->perusahaan ?? $expense->invoice->client->nama }})</td>
            </tr>
        @endif
        @if($expense->kategori === 'freelancer' && $expense->freelancer)
            <tr>
                <td class="label">Penerima (Freelancer)</td>
                <td class="value">
                    <strong>{{ $expense->freelancer->nama }}</strong><br>
                    WA: {{ $expense->freelancer->no_wa }}<br>
                    @if(!empty($expense->freelancer->rekening_bank))
                        Transfer ke Rekening:<br>
                        {!! nl2br(e($expense->freelancer->rekening_bank)) !!}
                    @endif
                </td>
            </tr>
        @endif
        <tr>
            <td class="label">Keperluan / Item</td>
            <td class="value">{{ $expense->keperluan }}</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td class="value">{{ $expense->metode_pembayaran }}</td>
        </tr>
        @if(!empty($expense->catatan))
            <tr>
                <td class="label">Catatan</td>
                <td class="value">{{ $expense->catatan }}</td>
            </tr>
        @endif
    </table>

    <!-- Amount Box -->
    <div class="amount-box">
        <div class="amount-label">Jumlah Pembayaran</div>
        <div class="amount-value">Rp {{ number_format($expense->nominal, 0, ',', '.') }}</div>
        <div class="amount-terbilang">Terbilang: <strong>{{ $terbilangText }}</strong></div>
    </div>

    <!-- Signatures -->
    <table class="bottom-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div style="font-size: 9px; color: #666666;">
                    * Dokumen ini diterbitkan secara sah melalui Sistem Manajemen Keuangan CRM PorcaLabs.<br>
                    * Tanda tangan elektronik disahkan melalui QR Code verifikasi transaksi internal.
                </div>
            </td>
            <td style="width: 40%;">
                <div class="signature-box">
                    <div class="signature-title">Disetujui Oleh,</div>
                    <div style="margin: 5px 0; text-align: center;">
                        @php
                            // Link points to the expense edit page in the admin panel
                            $adminUrl = url('/admin/expenses/' . $expense->id . '/edit');
                            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($adminUrl);
                        @endphp
                        <img src="{{ $qrCodeUrl }}" style="width: 60px; height: 60px; display: inline-block;">
                    </div>
                    <div class="signature-name">{{ $signatureName }}</div>
                    <div class="signature-role">{{ $signatureTitle }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Attachment Page (Page 2) if receipt image exists -->
    @if(!empty($expense->bukti_nota) && file_exists(public_path('storage/' . $expense->bukti_nota)))
        <div class="page-break"></div>
        <div style="text-align: center; padding-top: 10px;">
            <div style="font-size: 14px; font-weight: bold; border-bottom: 2px dashed #cccccc; padding-bottom: 8px; margin-bottom: 25px; color: #555555; text-transform: uppercase;">
                Lampiran Bukti Fisik / Nota Belanja
            </div>
            <div style="margin: 20px 0;">
                <img src="{{ public_path('storage/' . $expense->bukti_nota) }}" style="max-width: 100%; max-height: 580px; object-fit: contain; border: 1px solid #e2e8f0; padding: 5px; border-radius: 4px;">
            </div>
            <div style="font-size: 10px; color: #666666; margin-top: 15px;">
                Dokumen Lampiran: Bukti Pembayaran untuk No. Pengeluaran {{ $expense->nomor_pengeluaran }}
            </div>
        </div>
    @endif

</body>
</html>
