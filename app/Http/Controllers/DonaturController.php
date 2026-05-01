<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia; // Jika kamu pakai Vue + Inertia

class DonaturController extends Controller
{
    public function index()
    {
        // Ambil data yang statusnya 'success' saja
        $donatur = Donation::where('status', 'success')
                            ->orderBy('updated_at', 'desc')
                            ->get();

        // Jika pakai Vue (Inertia)
        return Inertia::render('Donatur', [
            'donatur' => $donatur
        ]);

        // ATAU Jika pakai Blade biasa, gunakan ini:
        // return view('donatur.index', compact('donatur'));
    }
}