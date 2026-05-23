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
        // 1. VALIDASI DATA INPUT DARI VUE
        $request->validate([
            'program_id' => 'required|exists:programs,id', // Wajib ada di tabel programs
            'total' => 'required|numeric|min:10000',   // Minimal donasi Rp 10.000 (sesuai standar gateway)
            'nama' => 'nullable|regex:/^[a-zA-Z\s]+$/|max:255',
            'email' => 'nullable|email|max:255',
            'nomor_hp' => 'nullable|regex:/^[0-9]+$/|min:10|max:15',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            Config::$serverKey = config('services.midtrans.serverKey', env('MIDTRANS_SERVER_KEY'));
            Config::$isProduction = config('services.midtrans.isProduction', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Aman digunakan karena sudah divalidasi pasti ada di database
            $program = Program::find($request->program_id);

            $orderId = 'LAZISMU-'.time();

            // Berikan nilai default jika user memilih anonim (Hamba Allah)
            $namaDonatur = $request->nama ?: 'Hamba Allah';
            $emailDonatur = $request->email ?: 'donatur@lazismu.org'; // Midtrans butuh format email valid
            $nomorHp = $request->nomor_hp ?: '081111111111';

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

                // TAMBAHKAN BLOK EXPIRY INI AGAR MIDTRANS IKUT KADALUARSA DALAM 3 MENIT
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'), // waktu sekarang
                    'unit' => 'minute',
                    'duration' => 3, // 3 menit
                ],
            ];

            // Request Token ke Midtrans
            $snapToken = Snap::getSnapToken($params);

            // SIMPAN DATA KE DATABASE
            Donation::create([
                'order_id' => $orderId,
                'user_name' => $namaDonatur,
                'amount' => $request->total,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'kategori' => $program->kategori,
                'keterangan' => $request->keterangan,
                'email' => $request->email, // Tetap simpan null di DB jika aslinya kosong
                'nomor_hp' => $request->nomor_hp,
                'program_id' => $request->program_id,
            ]);

            return response()->json(['token' => $snapToken]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pembayaran: '.$e->getMessage()], 500);
        }
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.serverKey', env('MIDTRANS_SERVER_KEY'));
        $hashed = hash('sha512', $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        if ($hashed == $request->signature_key) {
            $donation = Donation::where('order_id', $request->order_id)->first();

            if ($donation) {
                // 1. Update status donasi berdasarkan response Midtrans
                if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                    $donation->update(['status' => 'success']);
                } elseif ($request->transaction_status == 'pending') {
                    $donation->update(['status' => 'pending']);
                } elseif ($request->transaction_status == 'deny' || $request->transaction_status == 'expire' || $request->transaction_status == 'cancel') {
                    $donation->update(['status' => 'failed']);
                }

                // 2. Hitung ulang total donasi sukses untuk program ini
                $totalDonasiMasuk = Donation::where('program_id', $donation->program_id)
                    ->where('status', 'success')
                    ->sum('amount');

                // 3. Update nominal fisik ke kolom terkumpul di tabel programs
                \DB::table('programs')
                    ->where('id', $donation->program_id)
                    ->update(['terkumpul' => (int) $totalDonasiMasuk]);
            }
        }

        return response()->json(['message' => 'Callback success']);
    }
}
