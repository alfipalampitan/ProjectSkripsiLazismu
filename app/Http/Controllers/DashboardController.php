<?php

// App/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Expense; // Asumsi nanti kamu buat model Expense
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil Total Donasi per Kategori (Hanya yang Success)
        $donations = Donation::where('status', 'success')
            ->select('kategori', DB::raw('SUM(amount) as total'))
            ->groupBy('kategori')
            ->get()
            ->pluck('total', 'kategori');

        // 2. Ambil Total Pengeluaran per Kategori (Nanti kita buat fiturnya)
        // Untuk sementara kita set 0 dulu agar tidak error
        $expenses = hasTable('expenses')
            ? Expense::select('kategori', DB::raw('SUM(amount) as total'))->groupBy('kategori')->get()->pluck('total', 'kategori')
            : collect([]);

        // 3. Hitung Saldo Bersih (Donasi - Pengeluaran)
        $categories = ['Zakat', 'Infaq', 'Pilar', 'Qurban', 'Wakaf'];
        $summary = [];
        $totalSemuaSaldo = 0;

        foreach ($categories as $cat) {
            $masuk = $donations->get($cat, 0);
            $keluar = $expenses->get($cat, 0);
            $saldo = $masuk - $keluar;

            $config = match ($cat) {
                'Qurban' => ['icon' => 'fa-cow', 'color' => 'orange'],
                'Zakat' => ['icon' => 'fa-percentage', 'color' => 'orange'],
                'Infaq' => ['icon' => 'fa-hand-holding-heart', 'color' => 'orange'],
                'Wakaf' => ['icon' => 'fa-handshake-angle', 'color' => 'orange'],
                'Pilar' => ['icon' => 'fa-clover', 'color' => 'orange'],
                default => ['icon' => 'fa-wallet', 'color' => 'orange'],
            };

            $summary[] = [
                'name' => $cat,
                'amount' => $saldo,
                'icon' => $config['icon'],
                'color' => $config['color'],
            ];
            $totalSemuaSaldo += $saldo;
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $summary,
            'totalSemuaSaldo' => $totalSemuaSaldo,
            'latestDonations' => Donation::where('status', 'success')->latest()->take(5)->get(),
        ]);
    }
}

// Fungsi pembantu untuk cek tabel (opsional)
function hasTable($name)
{
    return \Illuminate\Support\Facades\Schema::hasTable($name);
}
