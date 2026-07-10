<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create quotations table
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('nomor')->nullable()->unique();
            $table->date('tanggal');
            $table->date('berlaku_hingga');
            $table->enum('diskon_tipe', ['persen', 'nominal'])->default('persen');
            $table->decimal('diskon_nilai', 15, 2)->default(0.00);
            $table->decimal('ppn_persen', 5, 2)->default(11.00);
            $table->enum('dp_tipe', ['persen', 'nominal'])->default('persen');
            $table->decimal('dp_nilai', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('total_diskon', 15, 2)->default(0.00);
            $table->decimal('total_ppn', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'terkirim', 'disetujui', 'ditolak', 'menjadi_invoice'])->default('draft');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Create quotation_items table
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('deskripsi');
            $table->decimal('qty', 10, 2);
            $table->string('satuan')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 3. Add dp_tipe and dp_nilai to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('dp_tipe', ['persen', 'nominal'])->default('persen')->after('dp_persen');
            $table->decimal('dp_nilai', 15, 2)->default(0.00)->after('dp_tipe');
        });

        // 4. Migrate old dp_persen data to new dp_tipe/dp_nilai fields
        DB::table('invoices')->whereNotNull('dp_persen')->update([
            'dp_tipe' => 'persen',
            'dp_nilai' => DB::raw('dp_persen')
        ]);

        // 5. Drop old dp_persen column from invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('dp_persen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore dp_persen column and migrate data back
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('dp_persen', 5, 2)->nullable()->after('ppn_persen');
        });

        DB::table('invoices')->where('dp_tipe', 'persen')->update([
            'dp_persen' => DB::raw('dp_nilai')
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['dp_tipe', 'dp_nilai']);
        });

        // 2. Drop tables
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
