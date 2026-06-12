<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use App\Models\Donation; // Pastikan nama model pengeluaranmu ini
use App\Models\Program;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua inputan filter dari frontend
        $type = $request->input('type', 'all');
        $program_id = $request->input('program_id', 'all');
        $sifat_pengeluaran = $request->input('sifat_pengeluaran', 'all');

        // Ambil data program untuk opsi di dropdown filter pemasukan
        $programs = Program::select('id', 'judul', 'kategori')->get();

        // ==========================================
        // 2. QUERY REKAP BULANAN PEMASUKAN (DONASI)
        // ==========================================
        $rekapBulanan = Donation::query()
            ->selectRaw("
                DATE_FORMAT(created_at, '%M %Y') as bulan,
                SUM(amount) as total_dana,
                COUNT(id) as total_donatur,
                MIN(created_at) as sorting_date
            ")
            ->where('status', 'success')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->addSelect('type')->where('type', $type);
            })
            // Tambahan Filter: Program ID
            ->when($program_id !== 'all', function ($query) use ($program_id) {
                return $query->where('program_id', $program_id);
            })
            ->when($type !== 'all', function ($query) {
                return $query->groupBy('bulan', 'type');
            }, function ($query) {
                return $query->groupBy('bulan');
            })
            ->orderBy('sorting_date', 'desc')
            ->get();

        // Statistik Kategori Pemasukan (Gaya Aslimu)
        $rekapKategori = Donation::select('kategori', DB::raw('SUM(amount) as total'))
            ->where('status', 'success')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($program_id !== 'all', function ($query) use ($program_id) {
                return $query->where('program_id', $program_id);
            })
            ->groupBy('kategori')
            ->get();

        // ==========================================
        // 3. QUERY REKAP BULANAN PENGELUARAN
        // ==========================================
        $rekapPengeluaran = Disbursement::query()
            ->selectRaw("
                DATE_FORMAT(created_at, '%M %Y') as bulan,
                SUM(amount) as total_dana,
                COUNT(id) as total_kasus,
                MIN(created_at) as sorting_date
            ")
            // Tambahan Filter: Sifat Pengeluaran
            ->when($sifat_pengeluaran !== 'all', function ($query) use ($sifat_pengeluaran) {
                return $query->where('sifat_pengeluaran', $sifat_pengeluaran);
            })
            ->groupBy('bulan')
            ->orderBy('sorting_date', 'desc')
            ->get();

        // ==========================================
        // 4. DETEKSI PANEL DINAMIS (ADMIN / STAFF)
        // ==========================================
        $routeName = $request->route()->getName();
        $componentPath = 'Admin/Laporan'; // Default ke Admin

        if (str_contains($routeName, 'staff.')) {
            $componentPath = 'Staff/Laporan'; // Belokkan ke Staff
        }

        return Inertia::render($componentPath, [
            'rekapBulanan' => $rekapBulanan,
            'rekapKategori' => $rekapKategori,
            'rekapPengeluaran' => $rekapPengeluaran,
            'programs' => $programs,
            'filters' => [
                'type' => $type,
                'program_id' => $program_id,
                'sifat_pengeluaran' => $sifat_pengeluaran,
            ],
        ]);
    }

    public function download(Request $request, $bulan)
    {
        $type = $request->query('type', 'all');
        $program_id = $request->query('program_id', 'all');

        $donasi = Donation::query()
            ->where('status', 'success')
            ->whereRaw("DATE_FORMAT(created_at, '%M %Y') = ?", [$bulan])
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($program_id !== 'all', function ($query) use ($program_id) {
                return $query->where('program_id', $program_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $total = $donasi->sum('amount');

        $pdf = Pdf::loadView('pdf.laporan_bulanan', [
            'donasi' => $donasi,
            'bulan' => $bulan,
            'total' => $total,
            'type' => $type,
        ]);

        return $pdf->stream("Laporan-Donasi-{$bulan}-{$type}.pdf");
    }

    public function downloadPengeluaran(Request $request, $bulan)
    {
        // 1. Tangkap parameter filter sifat dari URL
        $sifat_pengeluaran = $request->query('sifat_pengeluaran', 'all');

        // 2. Query data pengeluaran dengan filter bulan DAN sifat_pengeluaran
        $pengeluaran = Disbursement::with('applicant.pilarForm')->whereRaw("DATE_FORMAT(created_at, '%M %Y') = ?", [$bulan])
            ->when($sifat_pengeluaran !== 'all', function ($query) use ($sifat_pengeluaran) {
                return $query->where('sifat_pengeluaran', $sifat_pengeluaran);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Hitung total dana keluar
        $total = $pengeluaran->sum('amount');

        // 4. Render ke view blade khusus pengeluaran (kita buat setelah ini)
        $pdf = Pdf::loadView('pdf.laporan_pengeluaran', [
            'pengeluaran' => $pengeluaran,
            'bulan' => $bulan,
            'total' => $total,
            'sifat_pengeluaran' => $sifat_pengeluaran,
        ]);

        return $pdf->stream("Laporan-Pengeluaran-{$bulan}-{$sifat_pengeluaran}.pdf");
    }
}
