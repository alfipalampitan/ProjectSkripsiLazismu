<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProgramManagerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Program', [
            'programs' => Program::latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Program/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:Zakat,Infaq,Qurban,Wakaf,Pilar',
            'deskripsi' => 'required',
            'gambar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'target_dana' => 'required|numeric',
        ]);

        $path = $request->file('gambar')->store('programs', 'public');

        Program::create([
            'judul' => $request->judul,
            'slug' => Str::slug($request->judul),
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
            'target_dana' => $request->target_dana,
            'is_active' => true,
        ]);

       return redirect()->back()->with('message', 'Program berhasil dibuat');
    }
}