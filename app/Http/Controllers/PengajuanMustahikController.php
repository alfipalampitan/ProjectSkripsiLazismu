<?php

namespace App\Http\Controllers;

use App\Models\Applicant; // Tetap pakai model Applicant atau sesuaikan nama model database kamu
use App\Models\PilarForm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengajuanMustahikController extends Controller
{
    /**
     * Menampilkan form pendaftaran khusus publik/mustahik
     */
    public function create()
    {
        // Ambil semua pilihan program pilar yang aktif
        $pilarForms = PilarForm::where('is_active', true)->get();

        return inertia('Pendaftaran/PengajuanMustahik', [
            'pilarForms' => $pilarForms,
        ]);
    }

    /**
     * Menyimpan data permohonan secara murni 100% dinamis
     */
    public function store(Request $request)
    {
        // 1. Validasi awal untuk memastikan ID formulir pilar ada
        $request->validate([
            'pilar_form_id' => 'required|exists:pilar_forms,id',
        ]);

        // Ambil skema form dari database
        $pilarForm = PilarForm::findOrFail($request->pilar_form_id);
        $skemaForm = $pilarForm->skema_form ?? [];

        $biodataDinamis = [];
        $berkasDinamis = [];

        // 2. Lakukan validasi dan pemisahan data berdasarkan skema buatan staff
        foreach ($skemaForm as $field) {
            // Ganti kode str_replace lama dengan ini:
            $slugKey = Str::slug($field['field_name'], '_');

            // Jika field diset 'Wajib' oleh staff, jalankan engine validasi Laravel
            if ($field['required']) {
                $request->validate([
                    $slugKey => $field['type'] === 'file' ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'required',
                ], [], [
                    $slugKey => $field['field_name'],
                ]);
            }

            // Eksekusi pemisahan data jika diisi oleh mustahik
            if ($field['type'] === 'file' && $request->hasFile($slugKey)) {
                $file = $request->file($slugKey);
                $fileName = time().'_'.$slugKey.'_'.uniqid().'.'.$file->getClientOriginalExtension();

                // Pendaratan file aman ke storage/app/public/permohonan
                $file->storeAs('permohonan', $fileName, 'public');

                $berkasDinamis[$field['field_name']] = $fileName;
            } else {
                // Simpan isian berupa teks/angka biasa ke array terpisah
                $biodataDinamis[$field['field_name']] = $request->input($slugKey);
            }
        }

        // 3. Amankan Kode Unik Registrasi Permohonan untuk Mustahik
        $nomorPermohonan = 'REQ-'.date('Ym').'-'.strtoupper(Str::random(5));

        // 4. Simpan ke database menggunakan skema gabungan yang super aman
        Applicant::create([
            'nomor_permohonan' => $nomorPermohonan,
            'pilar_form_id' => $pilarForm->id,
            'status_permohonan' => 'pending',
            'biodata_dinamis' => $biodataDinamis, // Array otomatis dikonversi ke JSON oleh Laravel cast
            'berkas_dinamis' => $berkasDinamis,  // Array otomatis dikonversi ke JSON oleh Laravel cast

            // Catatan: Isian statis seperti nama_pemohon, nomor_hp, alamat dihilangkan
            // karena sekarang data itu sudah masuk secara fleksibel di dalam $biodataDinamis!
        ]);

        return redirect()->back()->with('success', 'Pengajuan mandiri Anda berhasil dikirim! Catat Nomor Registrasi Anda: '.$nomorPermohonan);
    }
}
