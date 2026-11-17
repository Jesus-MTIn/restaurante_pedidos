<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrearPedidoRequest;
use App\Http\Requests\ActualizarEstadoPedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\NormalizadorPedidos;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    use ApiResponse;

    private const COSTO_ENTREGA = 2.50;

    public function __construct(private NormalizadorPedidos $normalizador)
    {
    }

    public function store(CrearPedidoRequest $request): JsonResponse
    {
        return $this->storeDesdeContrato($request->validated());
    }

    public function storeOrigenA(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $normalizado = $this->normalizador->normalizarOrigenA($request->all());
        } catch (ValidationException $e) {
            return $this->errorResponse('Datos del origen A invalidos.', $e->errors(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error de normalizacion del origen A.', ['normalizacion' => [$e->getMessage()]], 422);
        }

        return $this->storeDesdeContrato($normalizado);
    }

    public function storeOrigenB(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $normalizado = $this->normalizador->normalizarOrigenB($request->all());
        } catch (ValidationException $e) {
            return $this->errorResponse('Datos del origen B invalidos.', $e->errors(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error de normalizacion del origen B.', ['normalizacion' => [$e->getMessage()]], 422);
        }

        return $this->storeDesdeContrato($normalizado);
    }

    private function storeDesdeContrato(array $data): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $formRequest = new CrearPedidoRequest();
        $validator = Validator::make($data, $formRequest->rules(), $formRequest->messages());
        $formRequest->withValidator($validator);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos normalizados no cumplen el contrato del pedido.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();

        $pedido = DB::transaction(function () use ($data) {
            $pedido = Pedido::create([
                'codigo' => 'TEMP',
                'cliente_id' => $data['cliente_id'],
                'estado' => 'pendiente',
                'subtotal' => 0,
                'costo_entrega' => self::COSTO_ENTREGA,
                'total' => 0,
            ]);

            $subtotal = 0;

            foreach ($data['productos'] as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $cantidad = (int) $item['cantidad'];
                $precioUnitario = (float) $producto->precio;
                $subtotalItem = $precioUnitario * $cantidad;

                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalItem,
                ]);

                $subtotal += $subtotalItem;
            }

            $pedido->direccionEntrega()->create([
                'direccion' => $data['entrega']['direccion'],
                'referencia' => $data['entrega']['referencia'] ?? null,
                'observaciones' => $data['entrega']['observaciones'] ?? null,
                'latitud' => $data['entrega']['latitud'],
                'longitud' => $data['entrega']['longitud'],
            ]);

            $pedido->codigo = 'PED-' . str_pad((string) $pedido->id, 6, '0', STR_PAD_LEFT);
            $pedido->subtotal = $subtotal;
            $pedido->total = $subtotal + self::COSTO_ENTREGA;
            $pedido->save();

            return $pedido;
        });

        $pedido->load(['cliente', 'detalles.producto', 'direccionEntrega']);

        return $this->successResponse(
            new PedidoResource($pedido),
            'Pedido registrado correctamente',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with(['cliente', 'detalles.producto', 'direccionEntrega'])->findOrFail($id);

        $this->authorize('view', $pedido);

        return $this->successResponse(
            new PedidoResource($pedido),
            'Pedido consultado correctamente'
        );
    }

    public function actualizarEstado(ActualizarEstadoPedidoRequest $request, int $id): JsonResponse
    {
        $pedido = Pedido::findOrFail($id);
        $this->authorize('updateEstado', $pedido);
        $nuevoEstado = $request->validated('estado');

        if (! Pedido::puedeTransicionar($pedido->estado, $nuevoEstado)) {
            return $this->errorResponse(
                'Transición de estado no permitida.',
                ['estado' => ['No se puede cambiar de ' . $pedido->estado . ' a ' . $nuevoEstado . '.']],
                422
            );
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();
        $pedido->load(['cliente', 'detalles.producto', 'direccionEntrega']);

        return $this->successResponse(
            new PedidoResource($pedido),
            'Estado del pedido actualizado correctamente'
        );
    }
}
