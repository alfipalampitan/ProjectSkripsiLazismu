<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id_pengeluaran',
        'judul_pengeluaran',
        'amount',
        'jenis_pengeluaran',
        'program_id',
        'pilar',
        'keterangan',
    ];

    /**
     * Relasi ke model Program (Mengambil data program jika pengeluaran berdasarkan program)
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}