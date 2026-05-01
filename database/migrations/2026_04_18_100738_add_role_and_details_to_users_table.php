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
        Schema::table('users', function (Blueprint $table) {
            // Mengubah role menjadi ENUM dengan pilihan yang fix
            // Gunakan ->change() jika kolom role sudah ada sebelumnya
            // Atau hapus ->change() jika ini migration baru untuk penambahan kolom
            $table->enum('role', ['admin', 'staff'])->default('staff')->after('email');

            $table->string('jabatan')->nullable()->after('role');
            $table->text('alamat')->nullable()->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ini untuk jaga-jaga kalau mau rollback (menghapus kembali kolomnya)
            $table->dropColumn(['role', 'jabatan', 'alamat']);
        });
    }
};
