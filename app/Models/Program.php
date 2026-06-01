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
    protected static function booted()
    {
        // Setiap kali ada update status di tabel donations (misal dari pending jadi success di callback)
        static::updated(function ($donation) {
            // Pastikan donasi ini terikat dengan suatu program
            if ($donation->program) {
                // Hitung total donasi yang sukses khusus untuk program ini
                $totalTerkumpul = static::where('program_id', $donation->program_id)
                                        ->where('status', 'success')
                                        ->sum('amount');

                // Update kolom 'terkumpul' di database program secara fisik
                // Ini otomatis bekerja baik untuk program pakai target maupun tanpa target dana
                $donation->program()->update([
                    'terkumpul' => (int) $totalTerkumpul
                ]);
            }
        });
    }
}
