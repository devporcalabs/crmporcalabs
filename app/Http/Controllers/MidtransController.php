<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Configure Midtrans settings
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token for the invoice payment.
     */
    public function getSnapToken(Invoice $invoice)
    {
        if ($invoice->status === 'lunas' || $invoice->sisa_tagihan <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan invoice ini sudah lunas.',
            ], 400);
        }

        try {
            $client = $invoice->client;
            
            // Set transaction parameters
            $transactionDetails = [
                'order_id' => 'INV-' . $invoice->id . '-' . time(), // Unique order ID
                'gross_amount' => (int) $invoice->sisa_tagihan,
            ];

            $customerDetails = [
                'first_name' => $client->nama,
                'email' => $client->email ?? 'admin@porcalabs.com', // fallback
                'phone' => $client->no_wa,
            ];

            // Item details
            $itemDetails = [
                [
                    'id' => 'INV-' . $invoice->id,
                    'price' => (int) $invoice->sisa_tagihan,
                    'quantity' => 1,
                    'name' => 'Sisa Tagihan Invoice ' . ($invoice->nomor ?? 'DRAFT'),
                ]
            ];

            $params = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
            ];

            // Get snap token
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses pembayaran online.',
            ], 500);
        }
    }

    /**
     * Handle Midtrans Webhook (Notification).
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $notification = json_decode($payload, true);

            if (!$notification) {
                return response()->json(['message' => 'Invalid payload'], 400);
            }

            Log::info('Midtrans Webhook Received: ', $notification);

            $serverKey = config('midtrans.server_key');
            
            // Verify signature key: SHA512(order_id + status_code + gross_amount + server_key)
            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'];
            $grossAmount = $notification['gross_amount'];
            $signatureKey = $notification['signature_key'];
            
            $computedSignature = hash("sha512", $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $computedSignature) {
                Log::warning('Midtrans Webhook Invalid Signature.');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Extract invoice ID from order_id (Format: INV-[invoice_id]-[timestamp])
            $parts = explode('-', $orderId);
            if (count($parts) < 2 || $parts[0] !== 'INV') {
                return response()->json(['message' => 'Invalid order ID format'], 400);
            }
            
            $invoiceId = $parts[1];
            $invoice = Invoice::find($invoiceId);

            if (!$invoice) {
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            $transactionStatus = $notification['transaction_status'];
            $paymentType = $notification['payment_type'];

            // Log payment if status is capture (for credit card) or settlement (for QRIS, Bank Transfer, E-wallet)
            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && isset($notification['fraud_status']) && $notification['fraud_status'] === 'accept')) {
                // Prevent duplicate payments (check if this order_id is already registered)
                $paymentExists = Payment::where('keterangan', 'LIKE', '%' . $orderId . '%')->exists();
                
                if (!$paymentExists) {
                    // Create payment record
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'tanggal' => now(),
                        'jumlah' => $grossAmount,
                        'metode' => 'online', // online payment
                        'keterangan' => 'Pembayaran online Midtrans (' . $paymentType . ') - Ref: ' . $orderId,
                    ]);
                    
                    Log::info("Payment for Invoice ID {$invoice->id} registered successfully via Midtrans Webhook.");
                }
            }

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
