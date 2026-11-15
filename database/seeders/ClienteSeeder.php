<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Pérez',
                'correo_electronico' => 'juan.perez@example.com',
                'telefono' => '0991234567',
            ],
            [
                'nombre' => 'María Gómez',
                'correo_electronico' => 'maria.gomez@example.com',
                'telefono' => '0987654321',
            ],
            [
                'nombre' => 'Carlos Andrade',
                'correo_electronico' => 'carlos.andrade@example.com',
                'telefono' => '0971112233',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
