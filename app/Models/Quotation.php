<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nomor',
        'tanggal',
        'berlaku_hingga',
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
        'invoice_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'berlaku_hingga' => 'date',
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
        return $this->hasMany(QuotationItem::class)->orderBy('urutan');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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

    protected static function booted()
    {
        static::saving(function ($quotation) {
            // 1. Calculate totals on save if items relation is loaded
            if ($quotation->relationLoaded('items') && $quotation->items->isNotEmpty()) {
                $quotation->calculateTotals();
            }

            // 2. Auto-generate nomor quotation when status leaves 'draft' or is saved as non-draft, and nomor is empty
            if ($quotation->status !== 'draft' && empty($quotation->nomor)) {
                $quotation->nomor = static::generateNextQuotationNumber($quotation->tanggal ?? now());
            }
        });
    }

    /**
     * Generate the next quotation number for the given date.
     */
    public static function generateNextQuotationNumber($date): string
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $year = $carbonDate->year;
        $month = $carbonDate->month;

        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month] ?? 'I';

        // Find the last quotation with a number in the same year
        $lastQuotation = static::whereYear('tanggal', $year)
            ->whereNotNull('nomor')
            ->where('nomor', 'LIKE', 'QO/%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor, "/", -1) AS UNSIGNED) DESC')
            ->first();

        $sequence = 1;
        if ($lastQuotation) {
            $parts = explode('/', $lastQuotation->nomor);
            $lastSeq = intval(end($parts));
            $sequence = $lastSeq + 1;
        }

        $seqString = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        $number = "QO/{$year}/{$romanMonth}/{$seqString}";

        // Ensure uniqueness
        while (static::where('nomor', $number)->exists()) {
            $sequence++;
            $seqString = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            $number = "QO/{$year}/{$romanMonth}/{$seqString}";
        }

        return $number;
    }
}
