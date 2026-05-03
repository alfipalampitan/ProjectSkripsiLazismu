<?php

namespace App\Http\Controllers;

use App\Models\Donation; // Pastikan ini sesuai dengan nama Model donasi Anda
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransaksiController extends Controller
{
    public function index()
    {
        // Mengambil data donasi terbaru
        $donatur = Donation::latest()->get();

        return Inertia::render('Admin/Transaksi', [
            'donatur' => $donatur,
        ]);
    }

    public function destroy($id)
    {
        $donasi = \App\Models\Donation::findOrFail($id);
        $donasi->delete();

        return redirect()->back()->with('message', 'Data berhasil dihapus');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'jenis' => 'required',
            'keterangan' => 'required',
            'nomor_hp' => 'required',
            'email' => 'required|email',
        ]);

        $donasi = Donation::findOrFail($id);
        // SESUAIKAN DENGAN FILLABLE DI MODEL KAMU
        $donasi->update([
            'user_name' => $request->nama,
            'kategori' => $request->jenis,
            'keterangan' => $request->keterangan,
            'nomor_hp' => $request->nomor_hp,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('message', 'Data berhasil diupdate');
    }
}
