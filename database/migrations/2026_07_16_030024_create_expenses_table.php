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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengeluaran')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('freelancer_id')->nullable()->constrained('freelancers')->onDelete('set null');
            $table->string('kategori'); // freelancer, hardware, software, operasional, lain_lain
            $table->string('keperluan');
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2);
            $table->string('metode_pembayaran');
            $table->string('bukti_nota')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
