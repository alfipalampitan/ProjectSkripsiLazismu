<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia; // Import di bagian atas

class LaporanController extends Controller
{
    public function index()
    {
        // 1. Rekap Bulanan (Total Uang & Jumlah Orang)
        $rekapBulanan = Donation::select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as bulan'),
            DB::raw('SUM(amount) as total_dana'),
            DB::raw('COUNT(id) as total_donatur')
        )
            ->where('status', 'success') // Hanya yang sudah bayar
            ->groupBy('bulan')
            ->orderBy(DB::raw('MIN(created_at)'), 'desc')
            ->get();

        // 2. Statistik Kategori (Zakat, Infaq, dll)
        $rekapKategori = Donation::select('kategori', DB::raw('SUM(amount) as total'))
            ->where('status', 'success')
            ->groupBy('kategori')
            ->get();

        return Inertia::render('Admin/Laporan', [
            'rekapBulanan' => $rekapBulanan,
            'rekapKategori' => $rekapKategori,
        ]);
    }

    public function downloadPDF($bulan)
    {
        // Ambil data berdasarkan bulan dan tahun (Format: "January 2024")
        $donasi = Donation::where(DB::raw('DATE_FORMAT(created_at, "%M %Y")'), $bulan)
            ->where('status', 'success')
            ->get();

        $total = $donasi->sum('amount');

        // Load view khusus untuk PDF
        $pdf = Pdf::loadView('pdf.laporan_bulanan', [
            'donasi' => $donasi,
            'bulan' => $bulan,
            'total' => $total,
        ]);

        // Download filenya
        return $pdf->download("Laporan-Donasi-$bulan.pdf");
    }
}
