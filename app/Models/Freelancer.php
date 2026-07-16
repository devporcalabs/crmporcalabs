<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freelancer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'email',
        'no_wa',
        'keahlian',
        'rekening_bank',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
