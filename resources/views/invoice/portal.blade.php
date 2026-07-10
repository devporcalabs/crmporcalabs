<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->nomor ?? 'DRAFT' }} — Portal Pembayaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #005691;
            --primary-hover: #004473;
            --primary-light: #e6f0f7;
            --success: #2ec4b6;
            --success-light: #e6f9f7;
            --warning: #ff9f1c;
            --warning-light: #fff5e6;
            --danger: #e71d36;
            --danger-light: #ffe8ea;
            --dark: #0f172a;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
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
            background-color: #f1f5f9;
            background-image: radial-gradient(at 0% 0%, rgba(0, 86, 145, 0.05) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(46, 196, 182, 0.05) 0px, transparent 50%);
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--gray-200);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-img {
            height: 38px;
        }

        main {
            margin: 40px 0;
            flex-grow: 1;
        }

        .portal-grid {
            display: grid;
            grid-template-columns: 2.2fr 1.3fr;
            gap: 30px;
        }

        @media (max-width: 850px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 30px;
            margin-bottom: 25px;
            overflow: hidden;
            position: relative;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-lunas {
            background-color: var(--success-light);
            color: #127267;
            border: 1px solid rgba(46, 196, 182, 0.2);
        }

        .badge-sebagian {
            background-color: var(--warning-light);
            color: #a05e00;
            border: 1px solid rgba(255, 159, 28, 0.2);
        }

        .badge-terkirim {
            background-color: var(--primary-light);
            color: var(--primary);
            border: 1px solid rgba(0, 86, 145, 0.2);
        }

        .invoice-details-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .invoice-title-block h1 {
            font-size: 22px;
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .invoice-date-block {
            font-size: 13px;
            color: var(--gray-600);
            text-align: right;
        }

        @media (max-width: 550px) {
            .invoice-date-block {
                text-align: left;
            }
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background: var(--gray-100);
            padding: 20px;
            border-radius: 12px;
        }

        @media (max-width: 550px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-section h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray-600);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .info-section p {
            font-size: 14px;
            color: var(--dark);
            line-height: 1.5;
        }

        .info-section .name {
            font-weight: 600;
            font-size: 15px;
        }

        .items-table-wrapper {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .items-table th {
            padding: 12px 16px;
            background: var(--gray-100);
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            border-bottom: 2px solid var(--gray-200);
            text-transform: uppercase;
        }

        .items-table td {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            font-size: 14px;
        }

        .items-table tr:hover {
            background-color: rgba(248, 250, 252, 0.5);
        }

        .payment-summary-block {
            background: linear-gradient(135deg, var(--dark) 0%, var(--gray-800) 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }

        .payment-summary-block .label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--gray-300);
            margin-bottom: 8px;
        }

        .payment-summary-block .amount {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .payment-summary-block .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.15);
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
            color: var(--gray-300);
        }

        .summary-row.total {
            color: #ffffff;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            gap: 10px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 86, 145, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(46, 196, 182, 0.3);
        }

        .btn-success:hover {
            background-color: #25a89c;
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        .btn-outline:hover {
            background-color: var(--gray-100);
            color: var(--dark);
        }

        .btn:active {
            transform: translateY(0);
        }

        .payment-history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid var(--gray-200);
            font-size: 14px;
        }

        .payment-history-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .payment-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .payment-info .date {
            font-size: 12px;
            color: var(--gray-600);
        }

        .payment-info .desc {
            font-weight: 500;
            color: var(--dark);
        }

        .payment-value {
            font-weight: 600;
            color: #127267;
        }

        .footer {
            background-color: var(--dark);
            color: var(--gray-300);
            padding: 25px 0;
            text-align: center;
            font-size: 13px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Success Overlay */
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .success-modal {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes scaleUp {
            to { transform: scale(1); }
        }

        .success-icon {
            width: 70px;
            height: 70px;
            background: var(--success-light);
            color: var(--success);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 25px;
        }

        .success-modal h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .success-modal p {
            font-size: 15px;
            color: var(--gray-600);
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: var(--danger);
        }
    </style>

    <!-- Midtrans Snap JS -->
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
</head>
<body>

    <!-- Header / Nav -->
    <header>
        <div class="container header-content">
            <img src="{{ asset('images/Logo n Text Horizontal Blue.png') }}" alt="PorcaLabs Logo" class="logo-img">
            <div>
                @if($invoice->status === 'lunas')
                    <span class="status-badge badge-lunas">Lunas</span>
                @elseif($invoice->status === 'dibayar_sebagian')
                    <span class="status-badge badge-sebagian">Bayar Sebagian</span>
                @else
                    <span class="status-badge badge-terkirim">Belum Lunas</span>
                @endif
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="container">
        <div class="portal-grid">
            
            <!-- Left Panel: Details -->
            <div>
                <div class="card">
                    <div class="invoice-details-header">
                        <div class="invoice-title-block">
                            <h1>Tagihan Invoice</h1>
                            <p style="color: var(--gray-600); font-weight: 500;">No. {{ $invoice->nomor ?? 'DRAFT' }}</p>
                        </div>
                        <div class="invoice-date-block">
                            <p>Tanggal Terbit: <strong>{{ $invoice->tanggal ? $invoice->tanggal->format('d M Y') : '-' }}</strong></p>
                            <p style="margin-top: 5px;">Jatuh Tempo: <strong class="{{ $invoice->is_overdue ? 'text-danger' : '' }}">{{ $invoice->jatuh_tempo ? $invoice->jatuh_tempo->format('d M Y') : '-' }}</strong></p>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-section">
                            <h3>Ditagihkan Oleh:</h3>
                            <p class="name">{{ $companyName }}</p>
                            <p>{{ $companyAddress }}</p>
                            <p>Telp: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                        </div>
                        <div class="info-section">
                            <h3>Ditagihkan Kepada:</h3>
                            <p class="name">{{ $invoice->client->perusahaan ?? $invoice->client->nama }}</p>
                            <p>Attn: {{ $invoice->client->nama }}</p>
                            <p>{{ $invoice->client->alamat ?? 'Alamat tidak dicantumkan.' }}</p>
                            <p>WA: {{ $invoice->client->no_wa }}</p>
                        </div>
                    </div>

                    <div class="card-title">
                        Rincian Pekerjaan
                    </div>
                    
                    <div class="items-table-wrapper">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%">Deskripsi Pekerjaan</th>
                                    <th style="width: 15%; text-align: center;">Qty</th>
                                    <th style="width: 15%; text-align: right;">Harga</th>
                                    <th style="width: 20%; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->deskripsi }}</td>
                                        <td style="text-align: center;">{{ number_format($item->qty, 0, ',', '.') }} {{ $item->satuan ?? '' }}</td>
                                        <td style="text-align: right;">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                        <td style="text-align: right;">Rp {{ number_format($item->qty * $item->harga_satuan, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($invoice->payments()->count() > 0)
                    <div class="card">
                        <div class="card-title">
                            Riwayat Pembayaran
                        </div>
                        <div>
                            @foreach($invoice->payments as $payment)
                                <div class="payment-history-item">
                                    <div class="payment-info">
                                        <span class="desc">Pembayaran via {{ $payment->metode === 'transfer' ? 'Transfer Bank' : ($payment->metode === 'tunai' ? 'Tunai / Cash' : 'Online Payment') }}</span>
                                        <span class="date">{{ $payment->tanggal ? $payment->tanggal->format('d M Y') : '-' }} @if($payment->keterangan) — {{ $payment->keterangan }} @endif</span>
                                    </div>
                                    <span class="payment-value">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Payment Box -->
            <div>
                <div class="payment-summary-block">
                    <div class="label">Sisa Tagihan</div>
                    <div class="amount">Rp {{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}</div>
                    
                    <div class="divider"></div>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($invoice->total_diskon > 0)
                        <div class="summary-row">
                            <span>Diskon</span>
                            <span>-Rp {{ number_format($invoice->total_diskon, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($invoice->total_ppn > 0)
                        <div class="summary-row">
                            <span>PPN ({{ number_format($invoice->ppn_persen, 0) }}%)</span>
                            <span>Rp {{ number_format($invoice->total_ppn, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="summary-row total">
                        <span>Grand Total</span>
                        <span>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
                    </div>
                    @if($invoice->total_paid > 0)
                        <div class="summary-row">
                            <span>Total Terbayar</span>
                            <span style="color: var(--success);">Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
                    @if($invoice->status !== 'lunas' && $invoice->sisa_tagihan > 0)
                        <button id="pay-button" class="btn btn-success">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Bayar Online Sekarang
                        </button>
                    @endif

                    <a href="{{ URL::signedRoute('invoice.public-pdf', ['invoice' => $invoice->id]) }}" class="btn btn-primary" target="_blank">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Unduh File PDF
                    </a>
                </div>

                <!-- Info Bank Instructions if paying offline -->
                @if($invoice->status !== 'lunas')
                    <div class="card" style="margin-top: 25px; padding: 20px;">
                        <div style="font-size: 14px; font-weight: 600; color: var(--dark); margin-bottom: 12px;">
                            Instruksi Pembayaran Manual:
                        </div>
                        <div style="font-size: 12px; color: var(--gray-600); line-height: 1.6;">
                            Anda juga dapat melakukan transfer ke rekening bank resmi berikut:
                            <div style="margin-top: 10px;">
                                @forelse($bankAccounts as $bank)
                                    <div style="border-left: 2px solid var(--primary); padding-left: 8px; margin-bottom: 12px;">
                                        <strong style="color: var(--primary);">{{ $bank['bank'] }}</strong><br>
                                        @if(!empty($bank['number']))
                                            No. Rek: <strong>{{ $bank['number'] }}</strong><br>
                                            A.N. {{ $bank['holder'] }}<br>
                                        @endif
                                        @if(!empty($bank['payment_link']))
                                            Tautan Bayar: <a href="{{ $bank['payment_link'] }}" target="_blank" style="color: var(--primary); text-decoration: underline;">Klik di sini</a>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-danger">Rekening bank belum diatur.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $companyName }}. Seluruh hak cipta dilindungi undang-undang.</p>
        </div>
    </footer>

    <!-- Success Modal Overlay -->
    <div class="success-overlay" id="success-overlay">
        <div class="success-modal">
            <div class="success-icon">✓</div>
            <h2>Pembayaran Sukses!</h2>
            <p>Terima kasih. Pembayaran Anda telah kami terima secara online dan status tagihan invoice Anda telah diperbarui menjadi lunas.</p>
            <button class="btn btn-primary" onclick="window.location.reload();">Tutup</button>
        </div>
    </div>

    <!-- Script Snap Logic -->
    <script>
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', function () {
                // Change button state to loading
                const originalText = payButton.innerHTML;
                payButton.disabled = true;
                payButton.innerHTML = '<svg style="animation: spin 1s linear infinite; width: 20px; height: 20px; margin-right: 8px;" fill="none" viewBox="0 0 24 24"><circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';

                // Fetch Snap Token from server
                fetch("{{ URL::signedRoute('invoice.pay-token', ['invoice' => $invoice->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mendapatkan token pembayaran dari server.');
                    }
                    return response.json();
                })
                .then(data => {
                    payButton.disabled = false;
                    payButton.innerHTML = originalText;

                    if (data.snap_token) {
                        // Open Midtrans Snap Popup
                        window.snap.pay(data.snap_token, {
                            onSuccess: function (result) {
                                console.log('success', result);
                                document.getElementById('success-overlay').style.display = 'flex';
                            },
                            onPending: function (result) {
                                console.log('pending', result);
                                alert('Pembayaran Anda sedang diproses. Mohon selesaikan pembayaran.');
                            },
                            onError: function (result) {
                                console.log('error', result);
                                alert('Pembayaran gagal. Silakan coba kembali.');
                            },
                            onClose: function () {
                                console.log('customer closed the popup without finishing the payment');
                            }
                        });
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat memproses pembayaran.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    payButton.disabled = false;
                    payButton.innerHTML = originalText;
                    alert('Gagal menghubungi server pembayaran. Silakan coba lagi nanti.');
                });
            });
        }
    </script>

    <!-- Keyframe animation for spinner -->
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
