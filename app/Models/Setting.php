<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // Menentukan field mana saja yang boleh diisi lewat create() atau updateOrCreate()
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Opsional: Helper untuk mempermudah mengambil value
     * Contoh penggunaan: Setting::getVal('site_name');
     */
    public static function getVal($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}