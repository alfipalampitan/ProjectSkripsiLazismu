<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\KasUmum;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // Fungsi internal pembuat data anak saldo (Milik Bersama)
    private function getSharedDashboardData()
    {

        // $totalAkumulasiMasuk = Donation::where('status', 'success')->sum('amount');
        $saldoLiveProgram = Program::sum('saldo_live');
        $saldoKasUmum = KasUmum::sum('saldo');
        $totalSaldoLiveSekarang = $saldoLiveProgram + $saldoKasUmum;

        // 2. Total Saldo Terkumpul pada Program yang MEMILIKI Target Dana (Target > 0)
        $totalSaldoDenganTarget = Program::where('target_dana', '>', 0)->sum('terkumpul');

        // 3. Total Saldo Terkumpul pada Program yang TANPA Target Dana (Target <= 0)
        $totalSaldoTanpaTarget = Program::where('target_dana', '<=', 0)->sum('terkumpul');

        $totalAkumulasiMasuk = $totalSaldoDenganTarget + $totalSaldoTanpaTarget;
        // 1. Total Pengeluaran Terikat (Sifat Pengeluaran = 'terikat')
        $pengeluaranTerikat = Disbursement::where('sifat_pengeluaran', 'terikat')->sum('amount');

        // 2. Total Pengeluaran Tidak Terikat (Sifat Pengeluaran = 'tidak_terikat')
        $pengeluaranTidakTerikat = Disbursement::where('sifat_pengeluaran', 'tidak_terikat')->sum('amount');
      
        // 3. Total Seluruh Pengeluaran (Akumulasi gabungan keduanya)
        $totalKeluar = Disbursement::sum('amount');

        $donationsPerKategori = Donation::where('status', 'success')
            ->select('kategori', DB::raw('SUM(amount) as total'))
            ->groupBy('kategori')->get()->pluck('total', 'kategori');
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
        $totalSemuaSaldo = 0;
        $totalMasukKolektif = 0;
        $totalKeluarKolektif = 0;

        foreach ($categories as $cat) {
            $masuk = $donationsPerKategori->get($cat, 0);
            $keluar = $disbursementsPerKategori->get($cat, 0);
            $saldo = $masuk - $keluar;

            $totalMasukKolektif += $masuk;
            $totalKeluarKolektif += $keluar;
            $totalSemuaSaldo += $saldo;

            $config = match ($cat) {
                'qurban' => ['icon' => 'fa-cow', 'color' => 'orange'],
                'zakat' => ['icon' => 'fa-percentage', 'color' => 'orange'],
                'infaq_sodaqoh' => ['icon' => 'fa-hand-holding-heart', 'color' => 'orange'],
                'wakaf' => ['icon' => 'fa-handshake-angle', 'color' => 'orange'],
                'pilar' => ['icon' => 'fa-clover', 'color' => 'orange'],
                default => ['icon' => 'fa-wallet', 'color' => 'orange'],
            };

            $summaryKategori[] = [
                'name' => $cat, 'amount' => (int) $saldo, 'icon' => $config['icon'], 'color' => $config['color'],
            ];
        }

        $latestDonations = Donation::where('status', 'success')->latest()->take(5)->get();

        return [
            'totalMasuk' => (int) $totalMasukKolektif,
            'totalKeluar' => (int) $totalKeluarKolektif,
            'totalSemuaSaldo' => (int) $totalSemuaSaldo,
            'pengeluaran_pilar' => $pengeluaranPerPilar,
            'latestDonations' => $latestDonations,
            'totalseluruhdonasi' => (int) $totalAkumulasiMasuk,
            'saldoLiveProgram' => (int) $saldoLiveProgram,
            'saldoLiveKasUmum' => (int) $saldoKasUmum,
            'totalSaldoLiveSekarang' => (int) $totalSaldoLiveSekarang,
            'totalSaldoDenganTarget' => (int) $totalSaldoDenganTarget,
            'totalSaldoTanpaTarget' => (int) $totalSaldoTanpaTarget,
            'pengeluaranTerikat' => (int) $pengeluaranTerikat,
            'pengeluaranTidakTerikat' => (int) $pengeluaranTidakTerikat,
            'totalPengeluaran' => (int) $totalKeluar,
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
