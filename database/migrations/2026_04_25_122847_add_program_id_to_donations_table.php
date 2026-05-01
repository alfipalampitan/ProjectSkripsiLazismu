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
        Schema::table('donations', function (Blueprint $table) {
            // Menambahkan kolom program_id setelah kolom ID
            // constrained() otomatis mencari tabel 'programs'
            // cascadeOnDelete() artinya jika program dihapus, riwayat donasi juga ikut terhapus (opsional)
            $table->foreignId('program_id')->after('id')->nullable()->constrained('programs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Menghapus foreign key dan kolomnya jika migration dibatalkan
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
