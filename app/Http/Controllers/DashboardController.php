<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Disbursement;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // Fungsi internal pembuat data anak saldo (Milik Bersama)
    private function getSharedDashboardData()
    {
        $donationsPerKategori = Donation::where('status', 'success')
            ->select('kategori', DB::raw('SUM(amount) as total'))
            ->groupBy('kategori')->get()->pluck('total', 'kategori');

        $pemasukanPerProgram = Program::select('id', 'judul')
            ->withSum(['donations' => function($query) { $query->where('status', 'success'); }], 'amount')->get();

        $disbursementsPerKategori = collect([]);
        $pengeluaranPerPilar = collect([]);

        if (Schema::hasTable('disbursements')) {
            $disbursementsPerKategori = Disbursement::join('programs', 'disbursements.program_id', '=', 'programs.id')
                ->select('programs.kategori', DB::raw('SUM(disbursements.amount) as total'))
                ->groupBy('programs.kategori')->get()->pluck('total', 'kategori');

            $pengeluaranPerPilar = Disbursement::select('pilar_terkait', DB::raw('SUM(amount) as total'))
                ->groupBy('pilar_terkait')->get();
        }

        $categories = ['Zakat', 'Infaq', 'Pilar', 'Qurban', 'Wakaf'];
        $summaryKategori = [];
        $totalSemuaSaldo = 0; $totalMasukKolektif = 0; $totalKeluarKolektif = 0;

        foreach ($categories as $cat) {
            $masuk = $donationsPerKategori->get($cat, 0);
            $keluar = $disbursementsPerKategori->get($cat, 0);
            $saldo = $masuk - $keluar;

            $totalMasukKolektif += $masuk;
            $totalKeluarKolektif += $keluar;
            $totalSemuaSaldo += $saldo;

            $config = match ($cat) {
                'Qurban' => ['icon' => 'fa-cow', 'color' => 'orange'],
                'Zakat'  => ['icon' => 'fa-percentage', 'color' => 'orange'],
                'Infaq'  => ['icon' => 'fa-hand-holding-heart', 'color' => 'orange'],
                'Wakaf'  => ['icon' => 'fa-handshake-angle', 'color' => 'orange'],
                'Pilar'  => ['icon' => 'fa-clover', 'color' => 'orange'],
                default  => ['icon' => 'fa-wallet', 'color' => 'orange'],
            };

            $summaryKategori[] = [
                'name' => $cat, 'amount' => (int) $saldo, 'icon' => $config['icon'], 'color' => $config['color'],
            ];
        }

        $latestDonations = Donation::where('status', 'success')->latest()->take(5)->get();

        return [
            'totalMasuk'        => (int) $totalMasukKolektif,
            'totalKeluar'       => (int) $totalKeluarKolektif,
            'totalSemuaSaldo'   => (int) $totalSemuaSaldo,
            'stats_kategori'    => $summaryKategori,
            'pemasukan_program' => $pemasukanPerProgram,
            'pengeluaran_pilar' => $pengeluaranPerPilar,
            'latestDonations'   => $latestDonations,
        ];
    }

    // 1. ROLE PUBLIC: Render ke halaman Donatur (Tanpa bungkus layout admin)
    public function transparansi()
    {
        $data = $this->getSharedDashboardData();
        return Inertia::render('Donasi/Transparansi', $data);
    }

    // 2. ROLE STAFF: Render ke dashboard internal Staff/Petugas Lapangan
    public function staffIndex()
    {
        $data = $this->getSharedDashboardData();
        // Diarahkan ke file Vue khusus staff (misal pake StaffLayout)
        return Inertia::render('Staff/Dashboard', $data);
    }

    // 3. ROLE ADMIN: Render ke dashboard super admin (Akses penuh keuangan & user)
    public function adminIndex()
    {
        $data = $this->getSharedDashboardData();
        // Diarahkan ke file Vue khusus admin (misal ada grafik konfigurasi sistem tambahan)
        return Inertia::render('Admin/Dashboard', $data);
    }
}