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
            $table->string('order_id_pengeluaran')->unique(); // OUT-LAZISMU-1779517833
            $table->string('judul_pengeluaran'); // Contoh: "Penyaluran Sembako Korban Banjir" atau "Bantuan Beasiswa an. Ahmad"
            $table->unsignedBigInteger('amount'); // Nominal uang keluar
            
            // JALUR 1: Mengurangi kas Program Donasi (INI WAJIB, karena semua uang keluar harus jelas dari dompet mana)
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            
            // JALUR 2: Relasi ke Mustahik (KUNCI JAWABANNYA: Dibuat nullable)
            // - Jika diisi ID: Berarti pengeluaran via Jalur Permohonan Mustahik (Pilar)
            // - Jika NULL: Berarti pengeluaran langsung dari Program Internal Lazismu (Tanpa Permohonan)
            $table->foreignId('applicant_id')->nullable()->constrained('applicants')->onDelete('cascade');
            
            // Untuk kebutuhan pengelompokan laporan bulanan
            $table->string('pilar_terkait')->nullable(); // 'pendidikan', 'ekonomi', 'kemanusiaan', dll
            
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