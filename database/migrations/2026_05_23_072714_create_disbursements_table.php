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
            $table->string('order_id_pengeluaran')->unique(); // Contoh: OUT-LAZISMU-1779517833
            $table->string('judul_pengeluaran'); // Contoh: Penyaluran Sembako Kebakaran
            $table->bigInteger('amount'); // Nominal uang keluar
            
            // Kolom penentu jenis alokasi dana
            $table->enum('jenis_pengeluaran', ['program', 'pilar']); 
            
            // Jika jenisnya 'program', kolom ini wajib diisi ID programnya
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('cascade');
            
            // Jika jenisnya 'pilar', kolom ini diisi nama pilarnya (pendidikan, ekonomi, dll)
            $table->string('pilar')->nullable(); 
            
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