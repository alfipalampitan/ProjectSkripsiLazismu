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
                'kategori' => 'zakat_maal',
                'saldo' => 5000000,
            ],
            [
                'kategori' => 'infaq',
                'saldo' => 2500000,
            ],
            [
                'kategori' => 'sedekah',
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