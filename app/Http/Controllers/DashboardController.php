<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Expense; // Asumsi nanti kamu buat model Expense
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // 1. FUNGSI INTERNAL: Mengumpulkan semua data agar bisa dipakai bersama
    private function getSharedDashboardData()
    {
        // Ambil Total Donasi per Kategori (Hanya yang Success)
        $donations = Donation::where('status', 'success')
            ->select('kategori', DB::raw('SUM(amount) as total'))
            ->groupBy('kategori')
            ->get()
            ->pluck('total', 'kategori');

        // Ambil Total Pengeluaran per Kategori
        $expenses = hasTable('expenses')
            ? Expense::select('kategori', DB::raw('SUM(amount) as total'))->groupBy('kategori')->get()->pluck('total', 'kategori')
            : collect([]);

        $categories = ['Zakat', 'Infaq', 'Pilar', 'Qurban', 'Wakaf'];
        $summary = [];
        $totalSemuaSaldo = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;

        foreach ($categories as $cat) {
            $masuk = $donations->get($cat, 0);
            $keluar = $expenses->get($cat, 0);
            $saldo = $masuk - $keluar;

            // Akumulasi total keseluruhan untuk kotak atas di transparansi
            $totalMasuk += $masuk;
            $totalKeluar += $keluar;

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

        $latestDonations = Donation::where('status', 'success')->latest()->take(5)->get();

        return [
            'stats' => $summary,
            'totalSemuaSaldo' => $totalSemuaSaldo,
            'totalMasuk' => $totalMasuk,   // <-- Kita kirim juga total masuk keseluruhan
            'totalKeluar' => $totalKeluar, // <-- Kita kirim juga total keluar keseluruhan
            'latestDonations' => $latestDonations,
        ];
    }

    // 2. METHOD UNTUK ADMIN: Tetap render ke halaman Admin Dashboard
    public function index()
    {
        $data = $this->getSharedDashboardData();
        return Inertia::render('Admin/Dashboard', $data);
    }

    // 3. METHOD BARU UNTUK TRANSPARANSI: Menggunakan data yang sama, render ke halaman Donatur
    public function transparansi()
    {
        $data = $this->getSharedDashboardData();
        return Inertia::render('Donasi/Transparansi', $data);
    }
}

// Fungsi pembantu untuk cek tabel
function hasTable($name)
{
    return \Illuminate\Support\Facades\Schema::hasTable($name);
}