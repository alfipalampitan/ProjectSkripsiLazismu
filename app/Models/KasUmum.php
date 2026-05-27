<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasUmum extends Model
{
    use HasFactory;

    // Karena nama tabelnya tidak jamak (bukan kas_umums), wajib dideklarasikan manual
    protected $table = 'kas_umum';

    protected $fillable = [
        'kategori',
        'saldo',
    ];
}