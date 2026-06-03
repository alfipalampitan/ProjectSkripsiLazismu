<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\KasUmum;
use App\Models\Program; // Pastikan Model KasUmum di-import ke sini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function getSnapToken(Request $request)
    {
        // 1. VALIDASI DATA INPUT DARI VUE
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'total' => 'required|numeric|min:10000',
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

            $program = Program::find($request->program_id);
            $orderId = 'LAZISMU-'.time();

            $namaDonatur = $request->nama ?: 'Hamba Allah';
            $emailDonatur = $request->email ?: 'donatur@lazismu.org';
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
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'),
                    'unit' => 'minute',
                    'duration' => 3,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            Donation::create([
                'order_id' => $orderId,
                'user_name' => $namaDonatur,
                'amount' => $request->total,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'kategori' => $program->kategori,
                'keterangan' => $request->keterangan,
                'email' => $request->email,
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

            // Menggunakan Database Transaction agar aman dan tidak terjadi partial-update jika crash
            DB::transaction(function () use ($request) {

                $donation = Donation::where('order_id', $request->order_id)->first();

                if ($donation) {
                    $oldStatus = $donation->status; // Simpan status lama untuk validasi pencegahan double-increment
                    $currentStatus = $request->transaction_status;

                    // 1. Update status transaksi utama
                    if ($currentStatus == 'settlement' || $currentStatus == 'capture') {
                        $donation->update(['status' => 'success']);
                    } elseif ($currentStatus == 'pending') {
                        $donation->update(['status' => 'pending']);
                    } elseif (in_array($currentStatus, ['deny', 'expire', 'cancel'])) {
                        $donation->update(['status' => 'failed']);
                    }

                    // 2. Jika status berubah dari 'pending' menjadi 'success' (mencegah double-count akibat hit berulang dari midtrans)
                    // 2. Jika status berubah dari 'pending' menjadi 'success'
                    if (($currentStatus == 'settlement' || $currentStatus == 'capture') && $oldStatus !== 'success') {

                        // Ambil relasi program terkait
                        $program = Program::find($donation->program_id);

                        if ($program) {
                            $nominalDonasi = $donation->amount;

                            // Hitung total akumulasi terkumpul dari seluruh donasi sukses untuk program ini
                            $totalDonasiMasuk = Donation::where('program_id', $donation->program_id)
                                ->where('status', 'success')
                                ->sum('amount');

                            // KONDISI A: JIKA PROGRAM TANPA TARGET DANA (Target <= 0)
                            if ($program->target_dana <= 0) {

                                // 1. Belokkan uangnya ke Kas Umum sesuai kategorinya
                                $kategoriSistem = strtolower($program->kategori);
                                $kasUmum = KasUmum::where('kategori', $kategoriSistem)->first();

                                if ($kasUmum) {
                                    $kasUmum->increment('saldo', $nominalDonasi);
                                }

                                // 2. Update 'terkumpul' saja di program, saldo_live TIDAK bertambah
                                $program->update([
                                    'terkumpul' => (int) $totalDonasiMasuk,
                                    // 'saldo_live' sengaja tidak dimasukkan agar nilainya tetap
                                ]);
                            }

                            // KONDISI B: JIKA PROGRAM PUNYA TARGET DANA (Target > 0)
                            else {
                                // Update 'terkumpul' untuk tampilan, dan akumulasikan nominal ke 'saldo_live'
                                $program->update([
                                    'terkumpul' => (int) $totalDonasiMasuk,
                                    'saldo_live' => $program->saldo_live + $nominalDonasi,
                                ]);
                            }
                        }
                    }
                }
            });
        }

        return response()->json(['message' => 'Callback success']);
    }
}
