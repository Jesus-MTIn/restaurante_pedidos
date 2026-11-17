<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@estacionburger.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'cliente_id' => null,
        ]);

        User::create([
            'name' => 'Usuario Cliente Uno',
            'email' => 'cliente1@estacionburger.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'cliente_id' => 1,
        ]);

        User::create([
            'name' => 'Usuario Cliente Dos',
            'email' => 'cliente2@estacionburger.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'cliente_id' => 2,
        ]);
    }
}