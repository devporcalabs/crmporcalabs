<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('nomor')->nullable()->unique();
            $table->date('tanggal');
            $table->date('jatuh_tempo');
            $table->enum('diskon_tipe', ['persen', 'nominal'])->default('persen');
            $table->decimal('diskon_nilai', 15, 2)->default(0.00);
            $table->decimal('ppn_persen', 5, 2)->default(11.00);
            $table->decimal('dp_persen', 5, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('total_diskon', 15, 2)->default(0.00);
            $table->decimal('total_ppn', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'terkirim', 'dibayar_sebagian', 'lunas', 'dibatalkan'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
