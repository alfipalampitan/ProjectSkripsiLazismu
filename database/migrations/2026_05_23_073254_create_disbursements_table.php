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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->string('order_id_pengeluaran')->unique(); 
            $table->string('judul_pengeluaran'); 
            $table->unsignedBigInteger('amount'); 
            
            // 🌟 PERUBAHAN 1: Sifat Pengeluaran (Terikat atau Tidak Terikat)
            // Ini yang menjadi kompas utama sistem untuk membagi card laporan donatur
            $table->enum('sifat_pengeluaran', ['terikat', 'tidak_terikat'])->default('terikat');

            // 🌟 PERUBAHAN 2: Kategori Dana Tidak Terikat (Nullable)
            // Diisi jika sifat_pengeluaran = 'tidak_terikat' (Contoh: zakat_maal, infaq, amil)
            $table->string('kategori_dana_umum')->nullable();

            // 🌟 PERUBAHAN 3: Dibuat nullable()
            // - Jika Terikat: Wajib diisi ID program untuk kalkulasi saldo khusus (internal)
            // - Jika Tidak Terikat: Diisi NULL karena mengambil dari dana umum/zakat
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('cascade');
            
            // JALUR 2: Relasi ke Mustahik (Tetap nullable sesuai konsepmu)
            // - Jika ada ID: Lewat permohonan pilar
            // - Jika NULL: Pengeluaran internal langsung
            $table->foreignId('applicant_id')->nullable()->constrained('applicants')->onDelete('cascade');
            
            // Kebutuhan pengelompokan laporan bulanan / pilar
            $table->string('pilar_terkait')->nullable(); 
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};