<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'deskripsi',
        'gambar',
        'target_dana',
        'terkumpul',
        'is_active',
    ];

    public function donations()
    {
        // Satu Program memiliki banyak (hasMany) Donasi
        // Laravel akan mencari 'program_id' di tabel donations secara otomatis
        return $this->hasMany(Donation::class);
    }
}
