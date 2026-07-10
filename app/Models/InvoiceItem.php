<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'deskripsi',
        'qty',
        'satuan',
        'harga_satuan',
        'urutan',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the subtotal for this item.
     */
    public function getSubtotalAttribute()
    {
        return $this->qty * $this->harga_satuan;
    }
}
