<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donation;
use Carbon\Carbon;

class CleanPendingDonations extends Command
{
    // Ini adalah nama perintah yang akan dipanggil nanti
    protected $signature = 'donation:clean-pending';

    // Deskripsi singkat kegunaan perintah ini
    protected $description = 'Menghapus otomatis donasi berstatus pending yang sudah lewat dari 10 menit';

    public function handle()
    {
        // Hitung batas waktu (Waktu sekarang dikurangi 3 menit)
        $waktuBatas = Carbon::now()->subMinutes(3);

        // Cari data yang statusnya 'pending' DAN waktu dibuatnya (created_at) kurang dari batas waktu tersebut
        $deletedCount = Donation::where('status', 'pending')
            ->where('created_at', '<', $waktuBatas)
            ->delete(); // Eksekusi Hapus

        // Tampilkan pesan log di terminal (opsional)
        $this->info("Berhasil membersihkan {$deletedCount} data donasi pending yang kadaluarsa.");
    }
}