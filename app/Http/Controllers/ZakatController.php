<?php

namespace App\Http\Controllers;

use App\Models\Program; 
use App\Models\Setting; // <-- Pastikan model Setting di-import
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZakatController extends Controller
{
    // Menampilkan halaman kalkulator
    public function index()
    {
        $programs = Program::where('kategori', 'Zakat')->get();

        // Ambil harga emas pakai helper model kamu, default 1410000 jika kosong di DB
        $hargaEmas = (int) Setting::getVal('harga_emas', 1410000); 

        // Hitung Nishob Tahunan (Harga Emas x 85 gram)
        $nishobTahunan = $hargaEmas * 85;

        return Inertia::render('Donasi/Kalkulator', [
            'programs' => $programs,
            'system' => [
                'nishob_tahunan' => $nishobTahunan // Dilempar ke Vue kalkulator
            ]
        ]);
    }

    // Memproses transaksi zakat
    public function checkout(Request $request)
    {
        $request->validate([
            'jenis_zakat' => 'required|string',
            'total_harta' => 'required|numeric',
            'jumlah_zakat' => 'required|numeric|gt:0',
        ]);

        return redirect()->back()->with('message', 'Transaksi berhasil dibuat!');
    }
}