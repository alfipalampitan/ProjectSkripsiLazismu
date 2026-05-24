<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisbursementController extends Controller
{
    public function create()
    {
        return Inertia::render('Staff/InputPengeluaran', [
            'programs' => Program::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sifat_pengeluaran' => 'required|in:terikat,tidak_terikat',
            'judul_pengeluaran' => 'required|string|max:255',
            'program_id' => 'required_if:sifat_pengeluaran,terikat|nullable|exists:programs,id',
            'amount' => 'required|numeric|min:1000',
            'pilar_terkait' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        // Tambahkan keterangan tanda pembukuan akuntansi di awal memo
        $prefixMemo = $request->sifat_pengeluaran === 'tidak_terikat'
            ? '[UNRESTRICTED - KAS UMUM] '
            : '[RESTRICTED - DANA TERIKAT] ';

        Disbursement::create([
            'order_id_pengeluaran' => 'OUT-LAZISMU-'.time(),
            'judul_pengeluaran' => $request->judul_pengeluaran,
            'amount' => $request->amount,
            // Jika tidak terikat, arahkan ke ID Program Operasional Amil, atau null (tergantung aturan foreignId di DB)
            'program_id' => $request->sifat_pengeluaran === 'terikat' ? $request->program_id : null,
            'applicant_id' => null, // Form ini murni untuk pengeluaran program internal
            'pilar_terkait' => $request->pilar_terkait,
            'keterangan' => $prefixMemo.$request->keterangan,
        ]);

        return redirect()->route('dashboard')->with('message', 'Pengeluaran kas berhasil dibukukan.');
    }
}
