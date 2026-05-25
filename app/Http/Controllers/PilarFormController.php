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
     * Mengikuti konsep Dana Terikat & Tidak Terikat tanpa merubah struktur asli controller
     */
    public function disburseApplicant(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        // 1. Validasi inputan dengan tambahan aturan bersyarat (required_if) untuk sifat dana
        $request->validate([
            'sifat_pengeluaran' => 'required|in:terikat,tidak_terikat',
            'program_id'        => 'required_if:sifat_pengeluaran,terikat|nullable|exists:programs,id', 
            'amount'            => 'required|numeric|min:1',     
            'judul_pengeluaran' => 'required|string|max:255',
            'keterangan'        => 'nullable|string',
        ]);

        // 2. LOGIKA CEK SALDO KAS KAS PROGRAM (HANYA BERLAKU JIKA DANA TERIKAT)
        $programIdValue = null;
        if ($request->sifat_pengeluaran === 'terikat') {
            $program = Program::findOrFail($request->program_id);
            $programIdValue = $program->id;
            
            $totalDikeluarkan = Disbursement::where('program_id', $program->id)->sum('amount');
            $sisaSaldoKas = $program->terkumpul - $totalDikeluarkan;

            if ($request->amount > $sisaSaldoKas) {
                return redirect()->back()->withErrors([
                    'amount' => 'Saldo tidak mencukupi! Sisa dana kas untuk program "' . $program->judul . '" hanya Rp ' . number_format($sisaSaldoKas, 0, ',', '.')
                ]);
            }
        }

        // 3. Gunakan DB Transaction bawaan controller asli kamu (Aman, DB support facades sudah di-import di atas)
        DB::transaction(function () use ($request, $applicant, $programIdValue) {
            
            // Update status permohonan menjadi disetujui sesuai baris kode aslimu
            $applicant->update([
                'status_permohonan' => 'disetujui'
            ]);

            // Generate Order ID Pengeluaran unik bawaan aslimu
            $orderIdOut = 'OUT-LAZISMU-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // Prefix penanda pencatatan nirlaba untuk memo keterangan
            $prefixMemo = $request->sifat_pengeluaran === 'tidak_terikat' 
                ? "[UNRESTRICTED - KAS UMUM] " 
                : "[RESTRICTED - DANA PROGRAM] ";

            // Simpan ke tabel disbursements menggunakan struktur parameter aslimu
            Disbursement::create([
                'order_id_pengeluaran' => $orderIdOut,
                'judul_pengeluaran'    => $request->judul_pengeluaran,
                'amount'               => $request->amount,
                'jenis_pengeluaran'    => 'pilar', 
                'program_id'           => $programIdValue, // Bernilai ID program jika terikat, bernilai NULL jika tidak terikat
                'pilar'                => $applicant->pilarForm->pilar, 
                'keterangan'           => $prefixMemo . $request->keterangan,
            ]);
        });

        return redirect()->route('staff.applicants.index')->with('success', 'Permohonan berhasil disetujui dan dana telah dicairkan!');
    }
}