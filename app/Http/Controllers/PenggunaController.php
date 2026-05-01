<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class PenggunaController extends Controller
{
    public function index()
    {
        return Inertia::render('pengguna', [
            'users' => User::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|string|in:admin,staff',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
        ]);

        // 2. Simpan ke Database
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password harus di-hash
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'alamat' => $request->alamat,
        ]);

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('pengguna')->with('message', 'Pengguna baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            // Email divalidasi unik kecuali untuk ID user yang sedang diupdate
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$id,
            // Password opsional saat update
            'password' => ['nullable', Rules\Password::defaults()],
            'role' => 'required|string|in:admin,staff',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
        ]);

        // 2. Siapkan data untuk diupdate
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'alamat' => $request->alamat,
        ];

        // Hanya update password jika input password diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('pengguna')->with('message', 'Data pengguna berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Opsional: Mencegah admin menghapus dirinya sendiri
        if (auth()->id() == $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('pengguna')->with('message', 'Pengguna berhasil dihapus!');
    }
}
