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
            // 1. Tambahkan kolom 'type' untuk membedakan Cash/Tunai & Transfer
            // Kita kasih default 'transfer' supaya data lama otomatis terisi 'transfer'
            $table->enum('type', ['cash', 'transfer'])->default('transfer')->after('amount');

            // 2. Tambahkan kolom 'penerima' (Opsional) 
            // Untuk mencatat siapa staff yang menginputkan donasi tunai tersebut
            $table->string('penerima')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['type', 'penerima', 'keterangan']);
        });
    }
};