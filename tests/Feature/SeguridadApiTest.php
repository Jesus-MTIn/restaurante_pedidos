<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;

class SeguridadApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function crearPedidoCliente1(): int
    {
        $pedido = \App\Models\Pedido::create([
            'codigo' => 'PED-000001',
            'cliente_id' => 1,
            'estado' => 'pendiente',
            'subtotal' => 5.50,
            'costo_entrega' => 2.50,
            'total' => 8.00,
        ]);

        return (int) $pedido->id;
    }

    public function test_p1_acceso_autorizado_consulta_pedido_propio(): void
    {
        $pedidoId = $this->crearPedidoCliente1();

        $response = $this->withBasicAuth('cliente1@estacionburger.test', 'password')
            ->getJson("/api/v1/pedidos/{$pedidoId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $pedidoId);
    }

    public function test_p2_acceso_sin_autenticacion_retorna_401(): void
    {
        $pedidoId = $this->crearPedidoCliente1();

        $response = $this->withoutHeader('Authorization')
            ->getJson("/api/v1/pedidos/{$pedidoId}");

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_p3_usuario_autenticado_sin_permisos_retorna_403(): void
    {
        $pedidoId = $this->crearPedidoCliente1();

        $response = $this->withoutHeader('Authorization')
            ->withBasicAuth('cliente2@estacionburger.test', 'password')
            ->getJson("/api/v1/pedidos/{$pedidoId}");

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_usuario_normal_no_puede_modificar_estado_retorna_403(): void
    {
        $pedidoId = $this->crearPedidoCliente1();

        $response = $this->withoutHeader('Authorization')
            ->withBasicAuth('cliente1@estacionburger.test', 'password')
            ->patchJson("/api/v1/pedidos/{$pedidoId}/estado", ['estado' => 'confirmado']);

        $response->assertStatus(403);
    }

    public function test_administrador_puede_modificar_estado_retorna_200(): void
    {
        $pedidoId = $this->crearPedidoCliente1();

        $response = $this->withoutHeader('Authorization')
            ->withBasicAuth('admin@estacionburger.test', 'password')
            ->patchJson("/api/v1/pedidos/{$pedidoId}/estado", ['estado' => 'confirmado']);

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'confirmado');
    }

    public function test_credenciales_invalidas_retornan_401(): void
    {
        $response = $this->withBasicAuth('cliente1@estacionburger.test', 'incorrecta')
            ->getJson('/api/v1/pedidos/1');

        $response->assertStatus(401);
    }

    public function test_endpoints_normalizacion_tambien_requieren_autenticacion(): void
    {
        $response = $this->withoutHeader('Authorization')
            ->postJson('/api/v1/pedidos/origen-a', [
                'clienteId' => '1',
                'productoId' => '1',
                'cantidad' => '1',
                'direccion' => 'Calle Kennedy',
                'latitud' => '-0.94886',
                'longitud' => '-80.63806',
            ]);

        $response->assertStatus(401);
    }
}
