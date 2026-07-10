<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'tanggal',
        'jumlah',
        'metode',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted()
    {
        static::saving(function ($payment) {
            $invoice = $payment->invoice;
            if ($invoice) {
                // Sum all other payments
                $existingPaymentsSum = $invoice->payments()
                    ->when($payment->id, fn ($query) => $query->where('id', '!=', $payment->id))
                    ->sum('jumlah');

                $maxAllowed = max(0, $invoice->grand_total - $existingPaymentsSum);

                // Check if payment exceeds remaining balance (allowing a tiny float threshold)
                if ($payment->jumlah > ($maxAllowed + 0.01)) {
                    throw new \Exception('Jumlah pembayaran melebihi sisa tagihan. Maksimum pembayaran: Rp ' . number_format($maxAllowed, 0, ',', '.'));
                }
            }
        });

        static::saved(function ($payment) {
            if ($payment->invoice) {
                $payment->invoice->updateStatusBasedOnPayments();
            }
        });

        static::deleted(function ($payment) {
            if ($payment->invoice) {
                $payment->invoice->updateStatusBasedOnPayments();
            }
        });
    }
}
