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

        $programs = Program::when($kategori, function ($query) use ($kategori) {
            return $query->where('kategori', $kategori);
        }, function ($query) {
            return $query; // Tambahkan argumen ke-3 ini untuk memuaskan Intelephense
        })
            // Tambahkan ini: Menghitung jumlah 'amount' dari relasi 'donations' yang statusnya 'success'
            // Hasilnya akan tersimpan di field 'donations_sum_amount' atau kita beri alias 'terkumpul'
            ->withSum(['donations as terkumpul' => function ($query) {
                $query->where('status', 'success');
            }], 'amount')
            ->withCount(['donations as donatur_count' => function ($query) {
                $query->where('status', 'success');
            }])
            ->latest()
            ->get();

        return inertia('Donasi/PilihProgram', [
            'programs' => $programs,
            'kategoriAktif' => $kategori,
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
