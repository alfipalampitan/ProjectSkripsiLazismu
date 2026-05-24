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
        'program_id',
        'applicant_id',
        'pilar_terkait',
        'keterangan',
    ];

    /**
     * Relasi ke model Program
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Relasi ke model Applicant (Mustahik)
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
}