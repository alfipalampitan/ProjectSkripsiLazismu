<?php

namespace App\Http\Controllers;

use App\Models\Applicant; 
use App\Models\Disbursement; 
use App\Models\PilarForm;
use App\Models\Program; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Ditambahkan agar Str::random(4) tidak error

class PilarFormController extends Controller
{
    /**
     * Menampilkan daftar semua template form pilar yang ada
     */
    public function index()
    {
        $pilarForms = PilarForm::latest()->get();

        return inertia('Staff/PilarForm/Index', [
            'pilarForms' => $pilarForms,
        ]);
    }

    public function create()
    {
        return inertia('Staff/PilarForm/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_penyaluran' => 'required|string|max:255',
            'pilar' => 'required|string',
            'skema_form' => 'required|array', 
            'skema_form.*.field_name' => 'required|string', 
            'skema_form.*.type' => 'required|string', 
            'skema_form.*.required' => 'required|boolean', 
        ]);

        PilarForm::create([
            'nama_penyaluran' => $request->nama_penyaluran,
            'pilar' => $request->pilar,
            'skema_form' => $request->skema_form, 
            'is_active' => true,
        ]);

        return redirect()->route('pilar-form.index')->with('success', 'Template Form Berhasil Dibuat!');
    }

    public function edit($id)
    {
        $pilarForm = PilarForm::findOrFail($id);

        return inertia('Staff/PilarForm/Edit', [
            'pilarForm' => $pilarForm,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pilarForm = PilarForm::findOrFail($id);

        $request->validate([
            'nama_penyaluran' => 'required|string|max:255',
            'pilar' => 'required|string',
            'skema_form' => 'required|array',
            'skema_form.*.field_name' => 'required|string',
            'skema_form.*.type' => 'required|string',
            'skema_form.*.required' => 'required|boolean',
        ]);

        $pilarForm->update([
            'nama_penyaluran' => $request->nama_penyaluran,
            'pilar' => $request->pilar,
            'skema_form' => $request->skema_form, 
        ]);

        return redirect()->route('pilar-form.index')->with('success', 'Template Form Berhasil Diperbarui!');
    }

    /**
     * Menghapus template form jika sudah tidak digunakan
     */
    public function destroy($id)
    {
        $pilarForm = PilarForm::findOrFail($id);
        $pilarForm->delete();

        return redirect()->route('pilar-form.index')->with('success', 'Template Form Berhasil Dihapus!');
    }

    public function listApplicants()
    {
        $applicants = Applicant::with('pilarForm')->latest()->get();

        return inertia('Staff/Applicant/Index', [
            'applicants' => $applicants
        ]);
    }

    /**
     * Menampilkan detail data dasar & berkas dinamis permohonan tertentu
     */
    public function showApplicant($id)
    {
        $applicant = Applicant::with('pilarForm')->findOrFail($id);
        
        $programs = Program::select('id', 'judul', 'terkumpul')->get();

        return inertia('Staff/Applicant/Show', [
            'applicant' => $applicant,
            'programs'  => $programs
        ]);
    }
    /**
     * Proses Persetujuan (ACC) Keuangan + Pencairan Dana (Disbursement Dinamis)
     * Sinkron dengan tabel baru: kas_umum (Validasi Saldo & Potong Kas Otomatis)
     */
    public function disburseApplicant(Request $request, $id)
    {
        $applicant = Applicant::with('pilarForm')->findOrFail($id);

        // 1. Ambil nilai sifat pengeluaran dari form Vue (default: terikat)
        $sifatPengeluaran = $request->input('sifat_pengeluaran', 'terikat');

        // 2. Validasi inputan diperketat secara dinamis sesuai sifat_pengeluaran
        $request->validate([
            'sifat_pengeluaran'   => 'required|in:terikat,tidak_terikat',
            'program_id'          => $sifatPengeluaran === 'terikat' ? 'required|exists:programs,id' : 'nullable', 
            'kategori_dana_umum'  => $sifatPengeluaran === 'tidak_terikat' ? 'required|string' : 'nullable',
            'amount'              => 'required|numeric|min:1',     
            'judul_pengeluaran'   => 'required|string|max:255',
            'keterangan'          => 'nullable|string',
        ]);

        $programIdValue = null;
        $kasUmumModel = null; // Penampung model kas umum jika tidak terikat

        // 3. LOGIKA HITUNG & HADANG SALDO KHUSUS INTERNAL
        if ($sifatPengeluaran === 'terikat') {
            // --- VALIDASI SALDO DANA TERIKAT PROGRAM ---
            $program = Program::findOrFail($request->input('program_id'));
            $programIdValue = $program->id;
            
            // Hitung total pengeluaran program sebelumnya
            $totalDikeluarkan = Disbursement::where('program_id', $program->id)->sum('amount');
            
            // Rumus Saldo Riil / Efektif Internal
            $sisaSaldoKasEfektif = $program->terkumpul - $totalDikeluarkan;

            if ($request->input('amount') > $sisaSaldoKasEfektif) {
                return redirect()->back()->withErrors([
                    'amount' => 'Saldo kas tidak mencukupi! Sisa dana riil untuk program "' . $program->judul . '" adalah Rp ' . number_format($sisaSaldoKasEfektif, 0, ',', '.')
                ]);
            }

        } else if ($sifatPengeluaran === 'tidak_terikat') {
            // --- VALIDASI SALDO DANA TIDAK TERIKAT VIA TABEL KAS_UMUM ---
            $kategori = $request->input('kategori_dana_umum'); // e.g., 'zakat_maal', 'infaq_umum'

            // Ambil data saldo live dari tabel kas_umum
            $kasUmumModel = \App\Models\KasUmum::where('kategori', $kategori)->first();

            // Jika kategori belum di-input di database oleh admin
            if (!$kasUmumModel) {
                return redirect()->back()->withErrors([
                    'kategori_dana_umum' => 'Kategori kas ' . strtoupper(str_replace('_', ' ', $kategori)) . ' belum diinisialisasi di database!'
                ]);
            }

            // Validasi: Hadang jika saldo live di tabel kas_umum kurang dari nominal pencairan
            if ($request->input('amount') > $kasUmumModel->saldo) {
                $namaKategoriTeks = strtoupper(str_replace('_', ' ', $kategori));
                return redirect()->back()->withErrors([
                    'amount' => 'Gagal Otorisasi! Saldo untuk ' . $namaKategoriTeks . ' tidak mencukupi. Saldo live saat ini: Rp ' . number_format($kasUmumModel->saldo, 0, ',', '.')
                ]);
            }
        }

        // 4. Jalankan DB Transaction untuk mengunci akurasi data keuangan (ACID)
        DB::transaction(function () use ($request, $applicant, $programIdValue, $sifatPengeluaran, $kasUmumModel) {
            
            // Ubah status permohonan mustahik menjadi disetujui
            $applicant->update([
                'status_permohonan' => 'disetujui'
            ]);

            // Pembuatan kode unik pengeluaran otomatis
            $orderIdOut = 'OUT-LAZISMU-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));

            // Set prefix memo teks untuk memperjelas audit internal
            $kategoriDanaUmum = $request->input('kategori_dana_umum');
            $prefixMemo = $sifatPengeluaran === 'tidak_terikat' 
                ? "[UNRESTRICTED - KAS " . strtoupper(str_replace('_', ' ', $kategoriDanaUmum)) . "] " 
                : "[RESTRICTED - DANA PROGRAM] ";

            // POTONG SALDO OTOMATIS: Jika dana tidak terikat, potong saldo live di tabel kas_umum
            if ($sifatPengeluaran === 'tidak_terikat' && $kasUmumModel) {
                $kasUmumModel->decrement('saldo', $request->input('amount'));
            }

            // Insert data ke tabel disbursements
            Disbursement::create([
                'order_id_pengeluaran' => $orderIdOut,
                'judul_pengeluaran'    => $request->input('judul_pengeluaran'),
                'amount'               => $request->input('amount'),
                'sifat_pengeluaran'    => $sifatPengeluaran,
                'kategori_dana_umum'   => $sifatPengeluaran === 'tidak_terikat' ? $kategoriDanaUmum : null,
                'program_id'           => $programIdValue, 
                'applicant_id'         => $applicant->id,  
                'pilar_terkait'        => $applicant->pilarForm->pilar ?? 'Umum', 
                'keterangan'           => $prefixMemo . $request->input('keterangan'),
            ]);
        });

        return redirect()->route('staff.applicants.index')->with('success', 'Permohonan berhasil disetujui dan dana telah dicairkan!');
    }
}