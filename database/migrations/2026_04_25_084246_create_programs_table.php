<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique(); // Untuk URL (misal: zakat-maal)
            $table->enum('kategori', ['Zakat', 'Infaq', 'Qurban', 'Wakaf', 'Pilar']);
            $table->text('deskripsi');
            $table->string('gambar');
            $table->decimal('target_dana', 15, 2)->nullable();
            $table->decimal('terkumpul', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
