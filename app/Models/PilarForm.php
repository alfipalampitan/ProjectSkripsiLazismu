<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilarForm extends Model
{
    use HasFactory;

    protected $fillable = ['nama_penyaluran', 'pilar', 'skema_form', 'is_active'];

    protected $casts = [
        'skema_form' => 'array', // Otomatis merubah JSON database jadi array di Laravel/Vue
    ];

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
}