<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    // Di dalam SettingController.php
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();

        // Pastikan tulisannya 'Pengaturan', sesuai nama file .vue kamu
        return Inertia::render('Admin/Pengaturan', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'nama_organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:10000',
        ]);

        // 2. Simpan Data Teks
        $dataTeks = [
            'site_name' => $request->nama_organisasi,
            'site_address' => $request->alamat,
            'site_phone' => $request->nomor_telepon,
        ];

        foreach ($dataTeks as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 3. Simpan Logo (Jika ada upload baru)
        if ($request->hasFile('logo')) {
            $oldLogo = Setting::where('key', 'site_logo')->first();

            // Hapus file lama jika ada
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            $path = $request->file('logo')->store('system', 'public');
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $path]);
        }

        return redirect()->back()->with('message', 'Pengaturan sistem berhasil diperbarui!');
    }
}
