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
    }
}
