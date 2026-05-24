<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Program;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicantDisbursementController extends Controller
{
    /**
     * Memproses pencairan dana berdasarkan permohonan mustahik (Jalur Pilar)
     */
    public function disburse(Request $request, $id)
    {
        // 1. Validasi Input data dari form kanan Show.vue
        $request->validate([
            'program_id'        => 'required|exists:programs,id',
            'amount'            => 'required|numeric|min:1',
            'judul_pengeluaran' => 'required|string|max:255',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        // 2. Ambil model dengan fail-safe
        $applicant = Applicant::with('pilar_form')->findOrFail($id);
        $program = Program::findOrFail($request->program_id);

        // Pertahanan Sidang: Kunci berkas jika statusnya sudah bukan pending
        if ($applicant->status_permohonan !== 'pending') {
            return back()->withErrors(['error' => 'Transaksi ditolak. Berkas permohonan ini sudah diproses/dikunci sebelumnya.']);
        }

        // 3. Jalankan Database Transaction untuk keamanan audit keuangan
        try {
            DB::transaction(function () use ($request, $applicant, $program) {
                
                // Hitung saldo riil program secara dinamis untuk validasi kas cukup/tidak
                $pemasukanProgram = DB::table('donations')->where('program_id', $program->id)->where('status', 'success')->sum('amount');
                $pengeluaranProgram = DB::table('disbursements')->where('program_id', $program->id)->sum('amount');
                $saldoRiilProgram = $pemasukanProgram - $pengeluaranProgram;

                if ($saldoRiilProgram < $request->amount) {
                    throw new \Exception("Pencairan Gagal. Saldo Kas Aktif [" . $program->judul . "] tidak mencukupi untuk melakukan transaksi.");
                }

                // Catat histori pengeluaran (Kolom 'terkumpul' di tabel programs TIDAK DIKURANGI agar frontend aman)
                Disbursement::create([
                    'order_id_pengeluaran' => 'OUT-LAZISMU-' . time() . '-' . Str::upper(Str::random(4)),
                    'judul_pengeluaran'    => $request->judul_pengeluaran,
                    'amount'               => $request->amount,
                    'program_id'           => $program->id,
                    'applicant_id'         => $applicant->id, // Terisi karena jalur permohonan mustahik
                    'pilar_terkait'        => $applicant->pilar_form?->pilar ?? 'umum',
                    'keterangan'           => $request->keterangan,
                ]);

                // Kunci status mustahik menjadi disetujui
                $applicant->update([
                    'status_permohonan' => 'disetujui'
                ]);
            });

            return redirect()->route('staff.applicants.index')->with('success', 'Verifikasi selesai, dana berhasil disalurkan.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}