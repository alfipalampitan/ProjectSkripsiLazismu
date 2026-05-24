<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permohonan')->unique(); // Contoh: REQ-2026-0001
            
            // Hubungkan ke master form pilar di atas
            $table->foreignId('pilar_form_id')->constrained('pilar_forms')->onDelete('cascade');
            
            // Status permohonan default (Pasti ada di semua pilar)
            $table->enum('status_permohonan', ['pending', 'disetujui', 'ditolak'])->default('pending');
            
            // Menyimpan value inputan teks & berkas file dinamis buatan admin
            $table->json('biodata_dinamis')->nullable(); // Isi teks seperti Nama, NIK, NBM, Alamat, dll
            $table->json('berkas_dinamis')->nullable();  // Isi nama file upload-an dokumen
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};