<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasUmum;

class KategoriDanaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'kategori' => 'zakat',
                'saldo' => 0,
            ],
            [
                'kategori' => 'infaq_sodaqoh',
                'saldo' => 0,
            ],
        ];

        foreach ($categories as $category) {
            KasUmum::updateOrCreate(
                ['kategori' => $category['kategori']],
                ['saldo' => $category['saldo']]
            );
        }
    }
}