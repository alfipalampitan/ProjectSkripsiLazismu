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
                'saldo' => 21000000,
            ],
            [
                'kategori' => 'infaq_sodaqoh',
                'saldo' => 1000000,
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