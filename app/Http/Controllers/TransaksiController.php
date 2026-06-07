<?php

namespace App\Http\Controllers;

use App\Models\Donation; 
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransaksiController extends Controller
{
    public function index()
    {
        // 1. Ambil data Donasi (Pemasukan) yang sukses
        $pemasukan = Donation::where('status', 'success')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'order_id'    => $item->order_id,
                    'tanggal'     => $item->updated_at,
                    'nama'        => $item->user_name ?: 'Hamba Allah',
                    'email'       => $item->email ?: '-',
                    'nomor_hp'    => $item->nomor_hp ?: '-',
                    'jenis'       => 'Pemasukan', // Dikunci untuk filter Vue
                    'kategori'    => $item->kategori ?: 'Donasi',
                    'amount'      => (float) $item->amount,
                    'keterangan'  => $item->keterangan ?: '-',
                    'type_metode' => $item->type, // cash / transfer (Midtrans)
                ];
            });

        // 2. Ambil data Disbursement (Pengeluaran)
        $pengeluaran = Disbursement::latest()
            ->get()
            ->map(function ($item) {
                // Cari nama penerima: dari mustahik (applicant) atau judul pengeluaran langsung
                $namaPenerima = ($item->judul_pengeluaran ?: 'Pengeluaran Lazismu');
                
                return [
                    'id'          => $item->id,
                    'order_id'    => $item->order_id_pengeluaran ?: '-',
                    'tanggal'     => $item->updated_at,
                    'nama'        => $namaPenerima,
                    'email'       => '-',
                    'nomor_hp'    => '-',
                    'jenis'       => 'Pengeluaran', // Dikunci untuk filter Vue
                    'kategori'    => $item->sifat_pengeluaran ?: 'Umum',
                    'amount'      => (float) $item->amount,
                    'keterangan'  => $item->keterangan ?: '-',
                    'type_metode' => $item->sifat_pengeluaran ?: '-', // Penting/Mendesak/dll
                ];
            });

        // 3. Gabungkan pemasukan & pengeluaran, urutkan berdasarkan tanggal terbaru
        $semuaTransaksi = $pemasukan->merge($pengeluaran)->sortByDesc('tanggal')->values()->all();

        return Inertia::render('Admin/Transaksi', [
            'donatur' => $semuaTransaksi,
        ]);
    }

    public function destroy($id, Request $request)
    {
        // Ambil parameter jenis untuk menentukan tabel mana yang mau dihapus
        $jenis = $request->query('jenis');

        if ($jenis === 'Pemasukan') {
            $donasi = Donation::find($id);
            if ($donasi) {
                $donasi->delete();
                return redirect()->back()->with('message', 'Data rekam donasi berhasil dihapus');
            }
        }

        if ($jenis === 'Pengeluaran') {
            $pengeluaran = Disbursement::find($id);
            if ($pengeluaran) {
                $pengeluaran->delete();
                return redirect()->back()->with('message', 'Data rekam pengeluaran berhasil dihapus');
            }
        }

        return redirect()->back()->with('error', 'Data gagal dihapus atau jenis tidak valid');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'jenis'      => 'required|string|in:Pemasukan,Pengeluaran',
            'kategori'   => 'required|string',
            'amount'     => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->jenis === 'Pemasukan') {
            $donasi = Donation::findOrFail($id);
            $donasi->update([
                'user_name'  => $request->nama,
                'kategori'   => $request->kategori,
                'amount'     => $request->amount,
                'keterangan' => $request->keterangan,
            ]);
        } 
        
        if ($request->jenis === 'Pengeluaran') {
            $pengeluaran = Disbursement::findOrFail($id);
            $pengeluaran->update([
                'judul_pengeluaran'  => $request->nama,
                'kategori_dana_umum' => $request->kategori,
                'amount'             => $request->amount,
                'keterangan'         => $request->keterangan,
            ]);
        }

        return redirect()->back()->with('message', 'Data transaksi berhasil diperbarui');
    }
}