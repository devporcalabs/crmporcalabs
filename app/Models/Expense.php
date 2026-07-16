<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_pengeluaran',
        'invoice_id',
        'freelancer_id',
        'kategori',
        'keperluan',
        'tanggal',
        'nominal',
        'metode_pembayaran',
        'bukti_nota',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(Freelancer::class);
    }

    protected static function booted()
    {
        static::creating(function ($expense) {
            if (empty($expense->nomor_pengeluaran)) {
                $expense->nomor_pengeluaran = static::generateNextExpenseNumber($expense->tanggal ?? now());
            }
        });
    }

    public static function generateNextExpenseNumber($date = null): string
    {
        $carbonDate = $date ? \Carbon\Carbon::parse($date) : now();
        $year = $carbonDate->year;
        $month = str_pad($carbonDate->month, 2, '0', STR_PAD_LEFT);

        $lastExpense = static::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $carbonDate->month)
            ->whereNotNull('nomor_pengeluaran')
            ->where('nomor_pengeluaran', 'LIKE', 'EXP/%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastExpense) {
            $parts = explode('/', $lastExpense->nomor_pengeluaran);
            $lastSeq = intval(end($parts));
            $sequence = $lastSeq + 1;
        }

        $seqString = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        return "EXP/{$year}/{$month}/{$seqString}";
    }
}
