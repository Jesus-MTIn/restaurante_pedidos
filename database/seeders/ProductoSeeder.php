<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'Hamburguesa Clásica',
                'descripcion' => 'Hamburguesa con carne, lechuga, tomate y queso',
                'precio' => 5.50,
                'disponibilidad' => true,
            ],
            [
                'nombre' => 'Hamburguesa Doble',
                'descripcion' => 'Doble carne, doble queso y tocino',
                'precio' => 8.00,
                'disponibilidad' => true,
            ],
            [
                'nombre' => 'Papas Fritas',
                'descripcion' => 'Porción de papas fritas crujientes',
                'precio' => 2.50,
                'disponibilidad' => true,
            ],
            [
                'nombre' => 'Gaseosa',
                'descripcion' => 'Bebida gaseosa 500ml',
                'precio' => 1.50,
                'disponibilidad' => true,
            ],
            [
                'nombre' => 'Hot Dog',
                'descripcion' => 'Perro caliente con salchicha y salsas',
                'precio' => 3.00,
                'disponibilidad' => false,
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
