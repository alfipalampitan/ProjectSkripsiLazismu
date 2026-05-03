<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProgramManagerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Program', [
            'programs' => Program::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Program/Create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:Zakat,Infaq,Qurban,Wakaf,Pilar',
            'deskripsi' => 'required',
            'gambar' => 'required|image|mimes:jpg,jpeg,png|max:10000',
            'target_dana' => 'required|numeric',
        ]);

        // 2. Simpan Gambar ke storage/app/public/programs
        $path = $request->file('gambar')->store('programs', 'public');

        // 3. Simpan ke Database
        Program::create([
            'judul' => $request->judul,
            // Tambahkan random string di belakang slug agar selalu unik
            'slug' => Str::slug($request->judul).'-'.Str::lower(Str::random(5)),
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
            'target_dana' => $request->target_dana,
            'is_active' => true,
        ]);

        // 4. Redirect kembali dengan flash message
        // Inertia akan menangkap ini dan kita bisa tampilkan via SweetAlert/Toast
        return redirect()->back()->with('message', 'Program berhasil dibuat');
    }

    public function update_manual(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:Zakat,Infaq,Qurban,Wakaf,Pilar',
            'deskripsi' => 'required',
            'target_dana' => 'required|numeric',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:10000', // optional saat edit
        ]);

        $data = [
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'target_dana' => $request->target_dana,
        ];

        // Cek jika admin mengunggah gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama dari folder storage
            if ($program->gambar) {
                Storage::disk('public')->delete($program->gambar);
            }
            // Simpan gambar baru
            $data['gambar'] = $request->file('gambar')->store('programs', 'public');
        }

        $program->update($data);

        return redirect()->back()->with('message', 'Program berhasil diperbarui');
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);

        // Hapus file gambar dari storage sebelum hapus data di database
        if ($program->gambar) {
            Storage::disk('public')->delete($program->gambar);
        }

        $program->delete();

        return redirect()->back()->with('message', 'Program berhasil dihapus');
    }
}
