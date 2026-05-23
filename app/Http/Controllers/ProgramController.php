<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    // ProgramController.php
    public function index(Request $request)
    {
        $kategori = $request->query('kategori');

        // Base Query agar kita tidak menulis ulang logika withSum dan withCount
        $baseQuery = Program::when($kategori, function ($query) use ($kategori) {
            return $query->where('kategori', $kategori);
        })
        ->withSum(['donations as terkumpul' => function ($query) {
            $query->where('status', 'success');
        }], 'amount')
        ->withCount(['donations as donatur_count' => function ($query) {
            $query->where('status', 'success');
        }])
        ->latest();

        // 1. Ambil data untuk Desktop (Tetap dibatasi 4 data per halaman)
        // Kita gunakan clone agar query utamanya tidak rusak untuk query mobile bawahnya
        $programs = (clone $baseQuery)->paginate(4)->withQueryString();

        // 2. Ambil data untuk Mobile Slider (Ambil SEMUA data tanpa di-paginate)
        $semuaProgramMobile = $baseQuery->get();

        // 3. Mengikuti logika finansial global bawaan Anda
        $totalMasuk = Donation::where('status', 'success')->sum('amount');
        $totalKeluar = 0;
        $totalDonaturGlobal = Donation::where('status', 'success')->count();

        return inertia('Donasi/PilihProgram', [
            'programs' => $programs,
            'semuaProgramMobile' => $semuaProgramMobile, // <--- Data baru terkirim ke Vue!
            'kategoriAktif' => $kategori,

            // Oper data gabungan ke view frontend
            'totalMasuk' => (int) $totalMasuk,
            'totalKeluar' => (int) $totalKeluar,
            'totalDonaturGlobal' => (int) $totalDonaturGlobal,
        ]);
    }

    // ProgramController.php
    public function show(string $slug)
    {
        $program = Program::where('slug', $slug)
            ->withSum(['donations as terkumpul' => function ($query) {
                $query->where('status', 'success');
            }], 'amount')
            ->firstOrFail();

        $donatur = Donation::where('program_id', $program->id)
            ->where('status', 'success')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_name' => $item->user_name ?? 'Hamba Allah',
                    // Ambil dari kolom 'amount'
                    'jumlah' => (int) $item->amount,
                    'created_at' => $item->created_at,
                ];
            });

        return inertia('Donasi/DetailProgram', [
            'program' => $program,
            'donatur' => $donatur,
        ]);
    }
    
}

