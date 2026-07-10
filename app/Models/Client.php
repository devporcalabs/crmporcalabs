<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'perusahaan',
        'no_wa',
        'email',
        'npwp',
        'alamat',
        'catatan',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted()
    {
        static::deleting(function ($client) {
            // Check if there are any invoices (including drafts, cancelled, etc.)
            if ($client->invoices()->exists()) {
                throw new \Exception('Klien tidak dapat diarsipkan karena memiliki data invoice.');
            }
        });
    }
}
