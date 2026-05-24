<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    // Kolom lama yang sudah dihapus dari database wajib dibuang dari $fillable
    protected $fillable = [
        'nomor_permohonan', 
        'pilar_form_id', 
        'status_permohonan', 
        'biodata_dinamis', 
        'berkas_dinamis'
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

    public function getNamaPemohonAttribute()
    {
        // Mencari key dengan nama 'Nama' atau 'Nama Pemohon' di dalam JSON
        return $this->biodata_dinamis['Nama'] ?? $this->biodata_dinamis['Nama Pemohon'] ?? 'Tanpa Nama';
    }
}