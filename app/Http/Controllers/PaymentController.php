<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Program;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function getSnapToken(Request $request)
    {
        try {
            Config::$serverKey = config('services.midtrans.serverKey', env('MIDTRANS_SERVER_KEY'));
            Config::$isProduction = config('services.midtrans.isProduction', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $program = Program::find($request->program_id);

            $orderId = 'LAZISMU-'.time();

            // AMBIL DATA DARI INPUT VUE
            // Jika di Vue kamu kirim 'nama', 'email', 'nomor_hp', maka ambil dari $request
            $namaDonatur = $request->nama;
            $emailDonatur = $request->email;
            $nomorHp = $request->nomor_hp;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $request->total,
                ],
                'customer_details' => [
                    'first_name' => $namaDonatur, // <--- UBAH INI
                    'email' => $emailDonatur, // <--- UBAH INI
                    'phone' => $nomorHp,      // Tambahkan nomor HP ke Midtrans
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // SIMPAN DATA KE DATABASE
            Donation::create([
                'order_id' => $orderId,
                'user_name' => $namaDonatur, // <--- UBAH INI (Simpan nama donatur asli)
                'amount' => $request->total,
                'status' => 'pending',
                'snap_token' => $snapToken,
                // Jika di tabel database kamu ada kolom-kolom ini, tambahkan sekalian:
                'kategori' => $program->kategori, // Pastikan kamu mengirim kategori dari Vue
                'keterangan' => $request->keterangan,
                'email' => $emailDonatur,
                'nomor_hp' => $nomorHp,
                'program_id' => $request->program_id, // Pastikan kamu mengirim program_id dari Vue
            ]);

            return response()->json(['token' => $snapToken]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        $namaDonatur = $request->nama ?: 'Hamba Allah'; // Jika kosong isi Hamba Allah
        $emailDonatur = $request->email ?: 'donatur@lazismu.org'; // Midtrans butuh format email
        $nomorHp = $request->nomor_hp ?: '0000000000';

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $request->total,
            ],
            'customer_details' => [
                'first_name' => $namaDonatur,
                'email' => $emailDonatur,
                'phone' => $nomorHp,
            ],
        ];
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.serverKey');

        // 1. Verifikasi keamanan (Signature Key) agar tidak ada yang bisa kirim data palsu
        $hashed = hash('sha512', $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        if ($hashed == $request->signature_key) {
            // 2. Cari data donasi berdasarkan order_id
            $donation = Donation::where('order_id', $request->order_id)->first();

            if ($donation) {
                // 3. Cek status transaksi dari Midtrans
                if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                    $donation->update(['status' => 'success']);
                } elseif ($request->transaction_status == 'pending') {
                    $donation->update(['status' => 'pending']);
                } elseif ($request->transaction_status == 'deny' || $request->transaction_status == 'expire' || $request->transaction_status == 'cancel') {
                    $donation->update(['status' => 'failed']);
                }
            }
        }

        return response()->json(['message' => 'Callback success']);
    }
}
