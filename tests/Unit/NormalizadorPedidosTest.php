<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NormalizadorPedidos;
use Illuminate\Validation\ValidationException;

class NormalizadorPedidosTest extends TestCase
{
    private NormalizadorPedidos $normalizador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizador = new NormalizadorPedidos();
    }

    public function test_p1_origen_a_valido_genera_contrato_normalizado(): void
    {
        $entrada = [
            'clienteId' => '1',
            'productoId' => '3',
            'cantidad' => '2',
            'precioUnitario' => '5,50',
            'direccion' => 'Calle Kennedy',
            'referencia' => 'Casa blanca',
            'observaciones' => 'Entrada principal',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ];

        $resultado = $this->normalizador->normalizarOrigenA($entrada);

        $esperado = [
            'cliente_id' => 1,
            'productos' => [
                [
                    'producto_id' => 3,
                    'cantidad' => 2,
                    'precio_unitario' => 5.5,
                ],
            ],
            'entrega' => [
                'direccion' => 'Calle Kennedy',
                'referencia' => 'Casa blanca',
                'observaciones' => 'Entrada principal',
                'latitud' => -0.94886,
                'longitud' => -80.63806,
            ],
        ];

        $this->assertSame($esperado, $resultado);
    }

    public function test_p2_origen_b_equivalente_genera_mismo_contrato(): void
    {
        $entrada = [
            'customer' => ['id' => 1],
            'item' => [
                'product_id' => 3,
                'units' => 2,
                'unit_price' => 5.50,
            ],
            'delivery' => [
                'address' => 'Calle Kennedy',
                'reference' => 'Casa blanca',
                'observations' => 'Entrada principal',
                'latitude' => -0.94886,
                'longitude' => -80.63806,
            ],
        ];

        $resultado = $this->normalizador->normalizarOrigenB($entrada);

        $esperado = [
            'cliente_id' => 1,
            'productos' => [
                [
                    'producto_id' => 3,
                    'cantidad' => 2,
                    'precio_unitario' => 5.5,
                ],
            ],
            'entrega' => [
                'direccion' => 'Calle Kennedy',
                'referencia' => 'Casa blanca',
                'observaciones' => 'Entrada principal',
                'latitud' => -0.94886,
                'longitud' => -80.63806,
            ],
        ];

        $this->assertSame($esperado, $resultado);
    }

    public function test_p3_origen_a_invalido_lanza_error_controlado(): void
    {
        $this->expectException(ValidationException::class);

        $this->normalizador->normalizarOrigenA([
            'productoId' => '3',
            'cantidad' => '2',
            'precioUnitario' => '5,50',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ]);
    }

    public function test_monto_ambiguo_con_dos_separadores_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        $this->normalizador->normalizarOrigenA([
            'clienteId' => '1',
            'productoId' => '3',
            'cantidad' => '2',
            'precioUnitario' => '1.234,56',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ]);
    }

    public function test_cantidad_invalida_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        $this->normalizador->normalizarOrigenA([
            'clienteId' => '1',
            'productoId' => '3',
            'cantidad' => '2.5',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ]);
    }

    public function test_producto_incorrecto_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        $this->normalizador->normalizarOrigenA([
            'clienteId' => '1',
            'productoId' => 'abc',
            'cantidad' => '2',
            'direccion' => 'Calle Kennedy',
            'latitud' => '-0.94886',
            'longitud' => '-80.63806',
        ]);
    }

    public function test_estructura_anidada_incompleta_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        $this->normalizador->normalizarOrigenB([
            'customer' => ['id' => 1],
            'item' => ['units' => 2],
            'delivery' => [
                'address' => 'Calle Kennedy',
                'latitude' => -0.94886,
                'longitude' => -80.63806,
            ],
        ]);
    }

    public function test_origen_b_monto_con_coma_se_normaliza(): void
    {
        $resultado = $this->normalizador->normalizarOrigenB([
            'customer' => ['id' => 1],
            'item' => [
                'product_id' => 3,
                'units' => 2,
                'unit_price' => '5,50',
            ],
            'delivery' => [
                'address' => 'Calle Kennedy',
                'latitude' => -0.94886,
                'longitude' => -80.63806,
            ],
        ]);

        $this->assertSame(5.5, $resultado['productos'][0]['precio_unitario']);
    }
}
