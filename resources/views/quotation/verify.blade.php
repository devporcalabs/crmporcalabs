<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen — Quotation {{ $quotation->nomor ?? 'DRAFT' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #005691;
            --success: #2ec4b6;
            --success-light: #e6f9f7;
            --dark: #0f172a;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-600: #475569;
            --gray-800: #1e293b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(0, 86, 145, 0.04) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(46, 196, 182, 0.04) 0px, transparent 50%);
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.1);
            border: 1px solid var(--gray-200);
            max-width: 500px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .verify-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--success) 100%);
        }

        .success-badge {
            width: 72px;
            height: 72px;
            background-color: var(--success-light);
            color: var(--success);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px -6px rgba(46, 196, 182, 0.3);
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 13px;
            color: var(--success);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .details-box {
            background: var(--gray-100);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
            border: 1px solid rgba(226, 232, 240, 0.5);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-200);
            font-size: 14px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .detail-value {
            color: var(--dark);
            font-weight: 600;
            text-align: right;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-aktif {
            background: var(--success-light);
            color: #127267;
        }

        .status-expired {
            background: #ffe8ea;
            color: #e71d36;
        }

        .info-footer {
            font-size: 12px;
            color: var(--gray-600);
            line-height: 1.6;
        }

        .brand-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .brand-link:hover {
            color: var(--gray-800);
        }
    </style>
</head>
<body>

    <div class="verify-card">
        <div class="success-badge">✓</div>
        <h1 class="title">Dokumen Terverifikasi Asli</h1>
        <p class="subtitle">Tanda Tangan Elektronik Valid</p>

        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">Jenis Dokumen</span>
                <span class="detail-value">Quotation / Surat Penawaran</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Nomor Dokumen</span>
                <span class="detail-value">{{ $quotation->nomor ?? 'DRAFT' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Penerbit</span>
                <span class="detail-value">{{ $companyName }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ditujukan Kepada</span>
                <span class="detail-value">{{ $quotation->client->perusahaan ?? $quotation->client->nama }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Penawaran</span>
                <span class="detail-value">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status Dokumen</span>
                <span class="detail-value">
                    @if($quotation->berlaku_hingga && $quotation->berlaku_hingga->isPast())
                        <span class="status-pill status-expired">Kedaluwarsa</span>
                    @else
                        <span class="status-pill status-aktif">Aktif</span>
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Penandatangan Digital</span>
                <span class="detail-value">{{ $signatureName }}<br><span style="font-size: 11px; font-weight: normal; color: var(--gray-600);">{{ $signatureTitle }}</span></span>
            </div>
        </div>

        <p class="info-footer">
            Dokumen ini terdaftar secara sah di dalam sistem database CRM {{ $companyName }}. Data yang ditampilkan di atas adalah data resmi yang tidak dapat diubah secara sepihak.
        </p>

        <a href="https://porcalabs.com" class="brand-link" target="_blank">&larr; Kunjungi Website PorcaLabs</a>
    </div>

</body>
</html>
