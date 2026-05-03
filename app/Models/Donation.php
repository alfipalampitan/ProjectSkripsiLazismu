<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    // Tambahkan baris ini
    protected $fillable = [
        'order_id',
        'user_name',
        'amount',
        'status',
        'snap_token',
        'nomor_hp',   // Tambahkan kolom nomor_hp
        'email',      // Tambahkan kolom email
        'keterangan', // Tambahkan kolom keterangan
        'program_id', // Tambahkan kolom program_id
        'kategori' ,   // Tambahkan kolom kategori
        'type',       // Tambahkan kolom type (cash/transfer)
        'penerima'    // Tambahkan kolom penerima (opsional)
    ];
}