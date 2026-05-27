public function run(): void
    {
        $categories = [
            [
                'kategori' => 'zakat_maal', // 👈 Ubah jadi huruf kecil & pakai underscore
                'saldo' => 5000000,
            ],
            [
                'kategori' => 'infaq',      // 👈 Samakan formatnya
                'saldo' => 2500000,
            ],
            [
                'kategori' => 'sedekah',    // 👈 Samakan formatnya
                'saldo' => 1000000,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\KasUmum::updateOrCreate(
                ['kategori' => $category['kategori']],
                ['saldo' => $category['saldo']]
            );
        }
    }