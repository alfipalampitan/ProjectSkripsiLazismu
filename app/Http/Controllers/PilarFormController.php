<?php

namespace App\Http\Controllers;

use App\Models\PilarForm;
use Illuminate\Http\Request;
use App\Models\Applicant; // <-- Tambahkan ini
use App\Models\Disbursement; // <-- Tambahkan ini
use App\Models\Program; // <-- Tambahkan ini

class PilarFormController extends Controller
{
    /**
     * Menampilkan daftar semua template form pilar yang ada
     */
    public function index()
    {
        $pilarForms = PilarForm::latest()->get();

        // Ubah dari Admin/PilarForm/Index menjadi Staff/PilarForm/Index
        return inertia('Staff/PilarForm/Index', [
            'pilarForms' => $pilarForms,
        ]);
    }
    public function create()
    {
        // Ubah ke folder Staff
        return inertia('Staff/PilarForm/Create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_penyaluran' => 'required|string|max:255',
            'pilar' => 'required|string',
            'skema_form' => 'required|array', // Harus berupa array list inputan
            'skema_form.*.field_name' => 'required|string', // Nama inputan (misal: Jurusan)
            'skema_form.*.type' => 'required|string', // Tipe inputan (text, number, file)
            'skema_form.*.required' => 'required|boolean', // Wajib diisi atau tidak
        ]);

        PilarForm::create([
            'nama_penyaluran' => $request->nama_penyaluran,
            'pilar' => $request->pilar,
            'skema_form' => $request->skema_form, // Otomatis tersimpan jadi JSON oleh model Casts
            'is_active' => true,
        ]);

        return redirect()->route('pilar-form.index')->with('success', 'Template Form Berhasil Dibuat!');
    }
    public function edit($id)
    {
        $pilarForm = PilarForm::findOrFail($id);

        // Ubah ke folder Staff
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
            'skema_form' => $request->skema_form, // Struktur JSON baru akan menimpa yang lama
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
        // Ambil data permohonan beserta informasi program pilarnya
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
        
        // Ambil semua daftar program donasi aktif sebagai pilihan sumber dana di dropdown pencairan
        $programs = Program::select('id', 'judul', 'terkumpul')->get();

        return inertia('Staff/Applicant/Show', [
            'applicant' => $applicant,
            'programs'  => $programs
        ]);
    }

    /**
     * Proses Persetujuan (ACC) Keuangan + Pencairan Dana (Disbursement Dinamis)
     */
    public function disburseApplicant(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        // Validasi inputan pencairan dana dari staff keuangan
        $request->validate([
            'program_id'        => 'required|exists:programs,id', // Program donasi yang akan dipotong
            'amount'            => 'required|numeric|min:1',     // Nominal uang keluar
            'judul_pengeluaran' => 'required|string|max:255',
            'keterangan'        => 'nullable|string',
        ]);

        // Cek sisa saldo program donasi terpilih agar tidak minus
        $program = Program::findOrFail($request->program_id);
        
        // Hitung pengeluaran yang pernah menggunakan dana program ini sebelumnya
        $totalDikeluarkan = Disbursement::where('program_id', $program->id)->sum('amount');
        $sisaSaldoKas = $program->terkumpul - $totalDikeluarkan;

        if ($request->amount > $sisaSaldoKas) {
            return redirect()->back()->withErrors([
                'amount' => 'Saldo tidak mencukupi! Sisa dana kas untuk program "' . $program->judul . '" hanya Rp ' . number_with_commas($sisaSaldoKas)
            ]);
        }

        // Gunakan DB Transaction agar jika salah satu proses gagal, database otomatis dibatalkan (aman)
        DB::transaction(function () use ($request, $applicant, $program) {
            
            // 1. Update status permohonan menjadi disetujui
            $applicant->update([
                'status_permohonan' => 'disetujui'
            ]);

            // 2. Generate Order ID Pengeluaran unik
            $orderIdOut = 'OUT-LAZISMU-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // 3. Masukkan data ke tabel disbursements (Menggunakan struktur fillable model kamu!)
            Disbursement::create([
                'order_id_pengeluaran' => $orderIdOut,
                'judul_pengeluaran'    => $request->judul_pengeluaran,
                'amount'               => $request->amount,
                'jenis_pengeluaran'    => 'pilar', // Berdasarkan pilar
                'program_id'           => $program->id, // ID Program Donasi yang dipotong saldo kasnya
                'pilar'                => $applicant->pilarForm->pilar, // Nama pilar otomatis sinkron dari master form
                'keterangan'           => $request->keterangan,
            ]);
        });

        return redirect()->route('staff.applicants.index')->with('success', 'Permohonan berhasil disetujui dan dana telah dicairkan!');
    }
}
