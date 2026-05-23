<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_permohonan', 'pilar_form_id', 'nama_pemohon', 
        'nomor_hp', 'alamat', 'status_permohonan', 
        'biodata_dinamis', 'berkas_dinamis'
    ];

    protected $casts = [
        'biodata_dinamis' => 'array',
        'berkas_dinamis' => 'array',
    ];

    public function pilarForm()
    {
        return $this->belongsTo(PilarForm::class, 'pilar_form_id');
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class);
    }
}