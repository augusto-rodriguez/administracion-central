<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombre'   => 'Administrador',
            'email'    => 'admin@central.cl',
            'password' => Hash::make('admin123'),
            'rol'      => 'admin',
            'activo'   => true,
        ]);
    }
}