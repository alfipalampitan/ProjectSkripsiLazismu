<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Disbursement;
use App\Models\KasUmum;
use App\Models\Program; // Pastikan model KasUmum di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DisbursementController extends Controller
{
    public function create()
    {
        // 1. Ambil data permohonan/mustahik yang butuh verifikasi (status pending)
        $applicants = Applicant::with('pilarForm')
            ->where('status_permohonan', 'pending')
            ->latest()
            ->get();

        // 2. Hitung statistik makro finansial atas berdasarkan data riil
        $totalSaldoGlobal = Program::sum('terkumpul') + KasUmum::sum('saldo');
        $totalTerikat = Disbursement::where('sifat_pengeluaran', 'terikat')->sum('amount');
        $totalBebas = Disbursement::where('sifat_pengeluaran', 'tidak_terikat')->sum('amount');

        $stats = [
            'total_saldo' => $totalSaldoGlobal,
            'total_terikat' => $totalTerikat,
            'total_bebas' => $totalBebas,
        ];

        // 3. Hitung akumulasi pengeluaran per pilar
        $pilarStatsRaw = Disbursement::select('pilar_terkait', DB::raw('SUM(amount) as total'))
            ->groupBy('pilar_terkait')
            ->pluck('total', 'pilar_terkait')
            ->toArray();

        $pilarStats = [
            'Pendidikan' => $pilarStatsRaw['Pendidikan'] ?? 0,
            'Ekonomi' => $pilarStatsRaw['Ekonomi'] ?? 0,
            'Kesehatan' => $pilarStatsRaw['Kesehatan'] ?? 0,
            'Kemanusiaan' => $pilarStatsRaw['Kemanusiaan'] ?? 0,
            'Dakwah' => $pilarStatsRaw['Dakwah'] ?? 0,
            'Lingkungan' => $pilarStatsRaw['Lingkungan'] ?? 0,
        ];

        // 4. Kirim data ke Inertia
        return Inertia::render('Staff/InputPengeluaran', [
            'applicants' => $applicants,
            'programs' => Program::select('id', 'judul', 'terkumpul')->get(),
            'stats' => $stats,
            'pilarStats' => $pilarStats,
            'kasUmumList' => KasUmum::select('kategori', 'saldo')->get(), // Opsi tambahan untuk select option di Vue
        ]);
    }

    public function store(Request $request)
    {
        // 1. HAPUS ATAU KOMENTARI LINE DD SEBELUMNYA
        // dd($request->all());

        $sifatPengeluaran = $request->input('sifat_pengeluaran', 'tidak_terikat');

        // Pengaman ekstra: jika program_id datang dalam bentuk string kosong, paksa jadi null
        if ($request->input('program_id') === '') {
            $request->merge(['program_id' => null]);
        }

        // 2. VALIDASI DIKONDISIKAN SECARA DINAMIS
        $request->validate([
            'sifat_pengeluaran' => 'required|in:terikat,tidak_terikat',
            'judul_pengeluaran' => 'required|string|max:255',
            'program_id' => $sifatPengeluaran === 'terikat' ? 'required|exists:programs,id' : 'nullable',
            'kategori_dana_umum' => $sifatPengeluaran === 'tidak_terikat' ? 'required|string' : 'nullable',
            'amount' => 'required|numeric|min:1000',
            'pilar_terkait' => 'required|string',
            'keterangan' => 'nullable|string',
            'applicant_id' => 'nullable|exists:applicants,id',
        ]);

        $programIdValue = null;
        $kasUmumModel = null;

        // 3. CEK HADANG SALDO SEBELUM SAVE
        if ($sifatPengeluaran === 'terikat') {
            $program = Program::findOrFail($request->input('program_id'));
            $programIdValue = $program->id;

            $totalDikeluarkan = Disbursement::where('program_id', $program->id)->sum('amount');
            $sisaSaldoKasEfektif = $program->terkumpul - $totalDikeluarkan;

            if ($request->input('amount') > $sisaSaldoKasEfektif) {
                return redirect()->back()->withErrors([
                    'amount' => 'Saldo tidak mencukupi! Sisa dana riil program "'.$program->judul.'" adalah Rp '.number_format($sisaSaldoKasEfektif, 0, ',', '.'),
                ]);
            }

        } elseif ($sifatPengeluaran === 'tidak_terikat') {
            $kategori = $request->input('kategori_dana_umum');
            $kasUmumModel = KasUmum::where('kategori', $kategori)->first();

            if (! $kasUmumModel) {
                return redirect()->back()->withErrors([
                    'kategori_dana_umum' => 'Kategori kas '.strtoupper(str_replace('_', ' ', $kategori)).' belum diinisialisasi!',
                ]);
            }

            if ($request->input('amount') > $kasUmumModel->saldo) {
                $namaKategoriTeks = strtoupper(str_replace('_', ' ', $kategori));

                return redirect()->back()->withErrors([
                    'amount' => 'Gagal Otorisasi! Saldo untuk '.$namaKategoriTeks.' tidak mencukupi. Saldo live saat ini: Rp '.number_format($kasUmumModel->saldo, 0, ',', '.'),
                ]);
            }
        }

        // 4. DATABASE TRANSACTION & AUTO DECREMENT
        DB::transaction(function () use ($request, $sifatPengeluaran, $programIdValue, $kasUmumModel) {

            // Generate Order ID Unik untuk Pengeluaran
            $orderIdOut = 'OUT-LAZISMU-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $kategoriDanaUmum = $request->input('kategori_dana_umum');
            $prefixMemo = $sifatPengeluaran === 'tidak_terikat'
                ? '[UNRESTRICTED - KAS '.strtoupper(str_replace('_', ' ', $kategoriDanaUmum)).'] '
                : '[RESTRICTED - DANA PROGRAM] ';

            // KAS UMUM OTOMATIS POTONG
            if ($sifatPengeluaran === 'tidak_terikat' && $kasUmumModel) {
                $kasUmumModel->decrement('saldo', $request->input('amount'));
            }

            // Simpan ke database disbursement
            Disbursement::create([
                'order_id_pengeluaran' => $orderIdOut,
                'judul_pengeluaran' => $request->input('judul_pengeluaran'),
                'amount' => $request->input('amount'),
                'sifat_pengeluaran' => $sifatPengeluaran,
                'kategori_dana_umum' => $sifatPengeluaran === 'tidak_terikat' ? $kategoriDanaUmum : null,
                'program_id' => $programIdValue,
                'applicant_id' => $request->input('applicant_id'),
                'pilar_terkait' => $request->input('pilar_terkait'),
                'keterangan' => $prefixMemo.$request->input('keterangan'),
            ]);

            // Jika pengeluaran ini sekaligus mengubah status pemohon (mustahik)
            if ($request->input('applicant_id')) {
                Applicant::where('id', $request->input('applicant_id'))->update([
                    'status_permohonan' => 'disetujui',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pengeluaran kas berhasil dibukukan dan saldo otomatis terpotong.');
    }
}
