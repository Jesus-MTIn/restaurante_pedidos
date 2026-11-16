<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Producto;
use Database\Seeders\DatabaseSeeder;

class NormalizacionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_origen_a_crea_pedido_correctamente(): void
    {
        $payload = [
            'clienteId' => '1',
            'productoId' => '3',
            'cantidad' => '2',
            'precioUnitario' => '5,50',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ];

        $response = $this->postJson('/api/v1/pedidos/origen-a', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.cliente.id', 1);

        $this->assertDatabaseHas('pedidos', ['id' => 1, 'estado' => 'pendiente']);
    }

    public function test_origen_b_crea_pedido_correctamente(): void
    {
        $payload = [
            'customer' => ['id' => 1],
            'item' => [
                'product_id' => 3,
                'units' => 2,
                'unit_price' => '5.50',
            ],
            'delivery' => [
                'address' => 'Calle Kennedy',
                'latitude' => -0.94886,
                'longitude' => -80.63806,
            ],
        ];

        $response = $this->postJson('/api/v1/pedidos/origen-b', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.cliente.id', 1);
    }

    public function test_origen_a_invalido_retorna_422(): void
    {
        $payload = [
            'productoId' => '3',
            'cantidad' => '2',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ];

        $response = $this->postJson('/api/v1/pedidos/origen-a', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_origen_b_monto_invalido_retorna_422(): void
    {
        $payload = [
            'customer' => ['id' => 1],
            'item' => [
                'product_id' => 3,
                'units' => 2,
                'unit_price' => '5,50,0',
            ],
            'delivery' => [
                'address' => 'Calle Kennedy',
                'latitude' => -0.94886,
                'longitude' => -80.63806,
            ],
        ];

        $response = $this->postJson('/api/v1/pedidos/origen-b', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_compatibilidad_endpoint_original_se_mantiene(): void
    {
        $payload = [
            'cliente_id' => 1,
            'productos' => [
                ['producto_id' => 1, 'cantidad' => 2],
                ['producto_id' => 3, 'cantidad' => 1],
            ],
            'entrega' => [
                'direccion' => 'Calle Kennedy',
                'latitud' => -0.94886,
                'longitud' => -80.63806,
            ],
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.subtotal', 13.5)
            ->assertJsonPath('data.total', 16);
    }
}
