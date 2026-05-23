<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilar_forms', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penyaluran'); // Contoh: Beasiswa Mentari, Peduli Guru, UMKM Bangkit
            $table->enum('pilar', ['pendidikan', 'ekonomi', 'kesehatan', 'sosial_dakwah', 'kemanusiaan', 'lingkungan']);
            
            // Tempat menyimpan rancangan field buatan admin dalam bentuk JSON
            // Contoh: [{"field_name": "Nama Usaha", "type": "text"}, {"field_name": "Fotocopy KTP", "type": "file"}]
            $table->json('skema_form')->nullable(); 
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilar_forms');
    }
};