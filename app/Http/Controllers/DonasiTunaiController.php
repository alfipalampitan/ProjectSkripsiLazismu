<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DonasiTunaiController extends Controller
{
    // App/Http/Controllers/Finance/OfflineDonationController.php
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'user_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1000',
            'kategori' => 'required|string',
            'nomor_hp' => 'nullable|string',
            'email' => 'nullable|email',
            'keterangan' => 'nullable|string',
            'program_id' => 'nullable|exists:programs,id', // Jika ada relasi ke tabel program
        ]);

        try {
            // 2. Simpan Data Donasi Tunai
            Donation::create([
                // Kita generate order_id unik untuk pencatatan internal
                'order_id' => 'CASH-'.strtoupper(Str::random(10)),
                'user_name' => $request->user_name,
                'amount' => $request->amount,
                'status' => 'success',   // Tunai langsung sukses karena uang sudah diterima
                'type' => 'cash',      // Penanda donasi offline
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'keterangan' => $request->keterangan,
                'program_id' => $request->program_id,
                'kategori' => $request->kategori,
                'penerima' => Auth::user()->name, // Staff yang sedang login
                'snap_token' => null,        // Tidak butuh token Midtrans untuk tunai
            ]);

            return redirect()->back()->with('success', 'Alhamdulillah, donasi tunai berhasil dicatat.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat donasi: '.$e->getMessage());
        }
    }
}
