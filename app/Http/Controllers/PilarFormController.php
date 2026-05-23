<?php

namespace App\Http\Controllers;

use App\Models\PilarForm;
use Illuminate\Http\Request;

class PilarFormController extends Controller
{
    /**
     * Menampilkan daftar semua template form pilar yang ada
     */
    public function index()
    {
        $pilarForms = PilarForm::latest()->get();

        return inertia('Admin/PilarForm/Index', [
            'pilarForms' => $pilarForms
        ]);
    }

    /**
     * Form untuk membuat template baru
     */
    public function create()
    {
        return inertia('Admin/PilarForm/Create');
    }

    /**
     * Menyimpan template form baru ke database
     */
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
            'is_active' => true
        ]);

        return redirect()->route('pilar-form.index')->with('success', 'Template Form Berhasil Dibuat!');
    }

    /**
     * Form untuk mengedit/mengubah template form dinamis
     */
    public function edit($id)
    {
        $pilarForm = PilarForm::findOrFail($id);

        return inertia('Admin/PilarForm/Edit', [
            'pilarForm' => $pilarForm
        ]);
    }

    /**
     * Memperbarui/Mengubah template form dinamis (Kunci Dinamisasi Sistem)
     */
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
            'skema_form' => $request->skema_form // Struktur JSON baru akan menimpa yang lama
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
}