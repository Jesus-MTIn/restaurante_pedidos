<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Producto;
use Database\Seeders\DatabaseSeeder;

class PedidoApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function payloadValido(array $overrides = []): array
    {
        return array_merge([
            'cliente_id' => 1,
            'productos' => [
                ['producto_id' => 1, 'cantidad' => 2],
                ['producto_id' => 3, 'cantidad' => 1],
            ],
            'entrega' => [
                'direccion' => 'Calle Kennedy',
                'referencia' => 'Casa blanca, porton negro',
                'observaciones' => 'Entregar en entrada principal',
                'latitud' => -0.94886,
                'longitud' => -80.63806,
            ],
        ], $overrides);
    }

    public function test_crear_pedido_correctamente(): void
    {
        $response = $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.cliente.id', 1)
            ->assertJsonCount(2, 'data.productos');

        $this->assertDatabaseHas('pedidos', ['id' => 1, 'estado' => 'pendiente']);
    }

    public function test_rechazar_pedido_sin_cliente(): void
    {
        $payload = $this->payloadValido();
        unset($payload['cliente_id']);

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['cliente_id']]);
    }

    public function test_rechazar_productos_inexistentes(): void
    {
        $payload = $this->payloadValido();
        $payload['productos'] = [['producto_id' => 999, 'cantidad' => 1]];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_rechazar_cantidades_negativas(): void
    {
        $payload = $this->payloadValido();
        $payload['productos'] = [['producto_id' => 1, 'cantidad' => -2]];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_rechazar_coordenadas_incorrectas(): void
    {
        $payload = $this->payloadValido();
        $payload['entrega']['latitud'] = 200;
        $payload['entrega']['longitud'] = -300;

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_consultar_pedido_existente(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response = $this->getJson('/api/v1/pedidos/1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', 1)
            ->assertJsonPath('data.codigo', 'PED-000001');
    }

    public function test_consultar_pedido_inexistente(): void
    {
        $response = $this->getJson('/api/v1/pedidos/999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_actualizar_estado_correctamente(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response = $this->patchJson('/api/v1/pedidos/1/estado', ['estado' => 'confirmado']);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.estado', 'confirmado');
    }

    public function test_rechazar_estados_invalidos(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response = $this->patchJson('/api/v1/pedidos/1/estado', ['estado' => 'foo']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_rechazar_transiciones_no_permitidas(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response = $this->patchJson('/api/v1/pedidos/1/estado', ['estado' => 'entregado']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_estructura_respuesta_json(): void
    {
        $response = $this->postJson('/api/v1/pedidos', $this->payloadValido());

        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors',
        ]);
    }

    public function test_totales_calculados_correctamente(): void
    {
        $response = $this->postJson('/api/v1/pedidos', $this->payloadValido());

        // 2*5.5 + 1*2.5 = 13.5 subtotal; +2.5 entrega = 16 total
        $response->assertJsonPath('data.subtotal', 13.5)
            ->assertJsonPath('data.costo_entrega', 2.5)
            ->assertJsonPath('data.total', 16);
    }
}
