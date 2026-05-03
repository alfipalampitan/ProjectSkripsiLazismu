<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia; // Import di bagian atas

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');

        $rekapBulanan = Donation::query()
            ->selectRaw("
            DATE_FORMAT(created_at, '%M %Y') as bulan,
            SUM(amount) as total_dana,
            COUNT(id) as total_donatur,
            MIN(created_at) as sorting_date
        ")
            ->where('status', 'success')
            ->when($type !== 'all', function ($query) use ($type) {
                // Jika bukan 'all', tambahkan kolom type ke select dan filter where
                return $query->addSelect('type')->where('type', $type);
            })
            ->when($type !== 'all', function ($query) {
                // Jika bukan 'all', group berdasarkan bulan DAN type
                return $query->groupBy('bulan', 'type');
            }, function ($query) {
                // Jika 'all', group berdasarkan bulan SAJA (menggabungkan cash & transfer)
                return $query->groupBy('bulan');
            })
            ->orderBy('sorting_date', 'desc')
            ->get();

        // 2. Statistik Kategori (tetap pakai filter type)
        $rekapKategori = Donation::select('kategori', DB::raw('SUM(amount) as total'))
            ->where('status', 'success')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->groupBy('kategori')
            ->get();

        return Inertia::render('Admin/Laporan', [
            'rekapBulanan' => $rekapBulanan,
            'rekapKategori' => $rekapKategori,
            'filters' => ['type' => $type],
        ]);
    }

    public function download(Request $request, $bulan)
    {
        // 1. Tangkap parameter type dari URL (all, cash, atau transfer)
        $type = $request->query('type', 'all');

        // 2. Query data donasi dengan filter bulan DAN type
        $donasi = Donation::query()
            ->where('status', 'success')
            // Filter berdasarkan bulan (format: "May 2024")
            ->whereRaw("DATE_FORMAT(created_at, '%M %Y') = ?", [$bulan])
            // Filter berdasarkan TYPE (hanya jika bukan 'all')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Hitung total dana berdasarkan hasil filter di atas
        $total = $donasi->sum('amount');

        // 4. Render ke PDF
        $pdf = Pdf::loadView('pdf.laporan_bulanan', [
            'donasi' => $donasi,
            'bulan' => $bulan,
            'total' => $total,
            'type' => $type, // Tambahkan ini agar bisa tampil di judul PDF
        ]);

        return $pdf->stream("Laporan-Donasi-{$bulan}-{$type}.pdf");
    }
}
