<?php

namespace App\Http\Controllers;

use App\Models\Program; // <--- 1. WAJIB IMPORT MODEL PROGRAM DI SINI
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZakatController extends Controller
{
    // Menampilkan halaman kalkulator
    public function index()
    {
        // 2. Ambil data program yang kategori-nya 'Zakat' (Sesuai isi DB di HeidiSQL-mu)
        $programs = Program::where('kategori', 'Zakat')->get();

        // 3. Kirim data $programs ke dalam Vue lewat properti array
        return Inertia::render('Donasi/Kalkulator', [
            'programs' => $programs
        ]);
    }

    // Memproses transaksi zakat
    public function checkout(Request $request)
    {
        // 1. Validasi input dari frontend
        $request->validate([
            'jenis_zakat' => 'required|string',
            'total_harta' => 'required|numeric',
            'jumlah_zakat' => 'required|numeric|gt:0',
        ]);

        return redirect()->back()->with('message', 'Transaksi berhasil dibuat!');
    }
}