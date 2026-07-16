<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nomor',
        'tanggal',
        'jatuh_tempo',
        'diskon_tipe',
        'diskon_nilai',
        'ppn_persen',
        'dp_tipe',
        'dp_nilai',
        'subtotal',
        'total_diskon',
        'total_ppn',
        'grand_total',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jatuh_tempo' => 'date',
        'diskon_nilai' => 'decimal:2',
        'ppn_persen' => 'decimal:2',
        'dp_nilai' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'total_ppn' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('urutan');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the total amount paid for this invoice.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('jumlah');
    }

    /**
     * Get the remaining balance for this invoice.
     */
    public function getSisaTagihanAttribute()
    {
        return max(0, $this->grand_total - $this->total_paid);
    }

    /**
     * Check if the invoice is overdue.
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'lunas' || $this->status === 'dibatalkan') {
            return false;
        }

        return $this->jatuh_tempo && $this->jatuh_tempo->isPast();
    }

    /**
     * Recalculate totals based on items, diskon, and ppn.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->qty * $item->harga_satuan;
        }

        $this->subtotal = $subtotal;

        if ($this->diskon_tipe === 'persen') {
            $this->total_diskon = ($subtotal * $this->diskon_nilai) / 100;
        } else {
            $this->total_diskon = $this->diskon_nilai;
        }

        // DPP = Subtotal - Diskon
        $dpp = max(0, $subtotal - $this->total_diskon);

        $this->total_ppn = ($dpp * $this->ppn_persen) / 100;
        $this->grand_total = $dpp + $this->total_ppn;
    }

    /**
     * Update the invoice status based on recorded payments.
     */
    public function updateStatusBasedOnPayments(): void
    {
        // Don't modify status if the invoice is draft or cancelled
        if (in_array($this->status, ['draft', 'dibatalkan'])) {
            return;
        }

        $sisaTagihan = $this->sisa_tagihan;
        $totalPaid = $this->total_paid;

        if ($sisaTagihan <= 0) {
            $this->status = 'lunas';
        } elseif ($totalPaid > 0) {
            $this->status = 'dibayar_sebagian';
        } else {
            $this->status = 'terkirim';
        }

        $this->saveQuietly();
    }

    protected static function booted()
    {
        static::saving(function ($invoice) {
            // 1. Calculate totals on save if items relation is loaded
            if ($invoice->relationLoaded('items') && $invoice->items->isNotEmpty()) {
                $invoice->calculateTotals();
            }

            // 2. Auto-generate nomor invoice when status leaves 'draft' or is saved as non-draft, and nomor is empty
            if ($invoice->status !== 'draft' && empty($invoice->nomor)) {
                $invoice->nomor = static::generateNextInvoiceNumber($invoice->tanggal ?? now());
            }
        });
    }

    /**
     * Generate the next invoice number for the given date.
     */
    public static function generateNextInvoiceNumber($date): string
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $year = $carbonDate->year;
        $month = $carbonDate->month;

        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month] ?? 'I';

        // Find the last invoice with a number in the same year
        $lastInvoice = static::whereYear('tanggal', $year)
            ->whereNotNull('nomor')
            ->where('nomor', 'LIKE', 'INV/%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor, "/", -1) AS UNSIGNED) DESC')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor);
            $lastSeq = intval(end($parts));
            $sequence = $lastSeq + 1;
        }

        $seqString = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        $number = "INV/{$year}/{$romanMonth}/{$seqString}";

        // Ensure uniqueness (loop until unique in case of manual overrides)
        while (static::where('nomor', $number)->exists()) {
            $sequence++;
            $seqString = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            $number = "INV/{$year}/{$romanMonth}/{$seqString}";
        }

        return $number;
    }
}
