<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('admin123'), // Selalu gunakan Hash untuk password
            'jabatan'   => 'IT Manager',
            'alamat'    => 'Jl. Sudirman No. 1, Jakarta',
            'role'      => 'admin',
        ]);
        User::create([
            'name'      => 'ngabMalik',
            'email'     => 'malik12345@gmail.com',
            'password'  => Hash::make('malik123'), // Selalu gunakan Hash untuk password
            'jabatan'   => 'Staff Keuangan',
            'alamat'    => 'Jl. Sudirman No. 7, Jakarta',
            'role'      => 'staff',
        ]);
    }
}
