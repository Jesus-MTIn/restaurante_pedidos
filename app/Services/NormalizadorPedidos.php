<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/**
 * Normaliza estructuras heterogeneas de pedidos hacia el contrato comun del backend.
 *
 * El servicio es independiente de HTTP: recibe arrays y devuelve el contrato normalizado.
 * Los controladores solo orquestan la llamada y la persistencia.
 */
class NormalizadorPedidos
{
    /**
     * Normaliza un pedido proveniente del formulario web / cliente del restaurante.
     *
     * Estructura esperada (plana, con nombres camelCase y tipos flexibles):
     * {
     *   "clienteId": "1",
     *   "productoId": "3",
     *   "cantidad": "2",
     *   "precioUnitario": "5,50",
     *   "direccion": "Calle Kennedy",
     *   "latitud": "-0.94886",
     *   "longitud": "-80.63806"
     * }
     *
     * @throws ValidationException
     */
    public function normalizarOrigenA(array $datos): array
    {
        $clienteId = $this->obtenerCampo($datos, ['clienteId', 'cliente_id', 'idCliente'], 'clienteId');

        $productos = [];
        if (isset($datos['productos']) && is_array($datos['productos'])) {
            foreach (array_values($datos['productos']) as $index => $item) {
                if (! is_array($item)) {
                    throw ValidationException::withMessages([
                        "productos.{$index}" => ['Cada producto debe ser un objeto.'],
                    ]);
                }
                $productos[] = $this->normalizarProductoOrigenA($item, $index);
            }
        } elseif ($this->tieneAlgunCampo($datos, ['productoId', 'producto_id', 'idProducto'])) {
            $productos[] = $this->normalizarProductoOrigenA($datos, 0);
        } else {
            throw ValidationException::withMessages([
                'productoId' => ['El producto es obligatorio.'],
            ]);
        }

        if ($productos === []) {
            throw ValidationException::withMessages([
                'productos' => ['Debe incluir al menos un producto.'],
            ]);
        }

        $entrega = $this->normalizarEntregaOrigenA($datos);

        return [
            'cliente_id' => $this->normalizarEntero($clienteId, 'clienteId'),
            'productos' => $productos,
            'entrega' => $entrega,
        ];
    }

    /**
     * Normaliza un pedido proveniente de un servicio externo simulado.
     *
     * Estructura esperada (anidada):
     * {
     *   "customer": { "id": 1 },
     *   "item": { "product_id": 3, "units": 2, "unit_price": "5.50" },
     *   "delivery": { "address": "Calle Kennedy", "latitude": -0.94886, "longitude": -80.63806 }
     * }
     *
     * @throws ValidationException
     */
    public function normalizarOrigenB(array $datos): array
    {
        $cliente = $datos['customer'] ?? $datos['cliente'] ?? null;
        if (! is_array($cliente)) {
            throw ValidationException::withMessages([
                'customer' => ['Los datos del cliente son obligatorios.'],
            ]);
        }
        $clienteId = $this->obtenerCampo($cliente, ['id', 'cliente_id', 'customer_id'], 'customer.id');

        $itemsEntrada = [];
        if (isset($datos['items']) && is_array($datos['items'])) {
            foreach (array_values($datos['items']) as $index => $item) {
                if (! is_array($item)) {
                    throw ValidationException::withMessages([
                        "items.{$index}" => ['Cada item debe ser un objeto.'],
                    ]);
                }
                $itemsEntrada[] = $this->normalizarProductoOrigenB($item, $index);
            }
        } elseif (isset($datos['item']) && is_array($datos['item'])) {
            $itemsEntrada[] = $this->normalizarProductoOrigenB($datos['item'], 0);
        } elseif ($this->tieneAlgunCampo($datos, ['product_id', 'producto_id', 'id'])) {
            $itemsEntrada[] = $this->normalizarProductoOrigenB($datos, 0);
        } else {
            throw ValidationException::withMessages([
                'item' => ['El item del pedido es obligatorio.'],
            ]);
        }

        if ($itemsEntrada === []) {
            throw ValidationException::withMessages([
                'items' => ['Debe incluir al menos un item.'],
            ]);
        }

        $delivery = $datos['delivery'] ?? $datos['entrega'] ?? null;
        if (! is_array($delivery)) {
            throw ValidationException::withMessages([
                'delivery' => ['Los datos de entrega son obligatorios.'],
            ]);
        }
        $entrega = $this->normalizarEntregaOrigenB($delivery);

        return [
            'cliente_id' => $this->normalizarEntero($clienteId, 'customer.id'),
            'productos' => $itemsEntrada,
            'entrega' => $entrega,
        ];
    }

    private function normalizarProductoOrigenA(array $item, int $index): array
    {
        $productoId = $this->obtenerCampo($item, ['productoId', 'producto_id', 'idProducto'], "productos.{$index}.productoId");
        $cantidad = $this->obtenerCampo($item, ['cantidad', 'units', 'unidades'], "productos.{$index}.cantidad");

        $resultado = [
            'producto_id' => $this->normalizarEntero($productoId, "productos.{$index}.productoId"),
            'cantidad' => $this->normalizarEntero($cantidad, "productos.{$index}.cantidad"),
        ];

        $precio = $this->obtenerCampoOpcional($item, ['precioUnitario', 'precio_unitario', 'unitPrice', 'unit_price']);
        if ($precio !== null) {
            $resultado['precio_unitario'] = $this->normalizarMonto($precio, "productos.{$index}.precioUnitario");
        }

        return $resultado;
    }

    private function normalizarProductoOrigenB(array $item, int $index): array
    {
        $productoId = $this->obtenerCampo($item, ['product_id', 'producto_id', 'id', 'productId'], "productos.{$index}.product_id");
        $cantidad = $this->obtenerCampo($item, ['units', 'cantidad', 'quantity'], "productos.{$index}.units");

        $resultado = [
            'producto_id' => $this->normalizarEntero($productoId, "productos.{$index}.product_id"),
            'cantidad' => $this->normalizarEntero($cantidad, "productos.{$index}.units"),
        ];

        $precio = $this->obtenerCampoOpcional($item, ['unit_price', 'precio_unitario', 'unitPrice', 'precioUnitario']);
        if ($precio !== null) {
            $resultado['precio_unitario'] = $this->normalizarMonto($precio, "productos.{$index}.unit_price");
        }

        return $resultado;
    }

    private function normalizarEntregaOrigenA(array $datos): array
    {
        $direccion = $this->obtenerCampo($datos, ['direccion', 'address', 'direccionEntrega'], 'entrega.direccion');
        $latitud = $this->obtenerCampo($datos, ['latitud', 'latitude'], 'entrega.latitud');
        $longitud = $this->obtenerCampo($datos, ['longitud', 'longitude'], 'entrega.longitud');

        $entrega = [
            'direccion' => $this->normalizarCadena($direccion, 'entrega.direccion'),
        ];

        $referencia = $this->obtenerCampoOpcional($datos, ['referencia', 'reference']);
        if ($referencia !== null) {
            $entrega['referencia'] = $this->normalizarCadenaOpcional($referencia, 'entrega.referencia');
        }

        $observaciones = $this->obtenerCampoOpcional($datos, ['observaciones', 'observations', 'notas']);
        if ($observaciones !== null) {
            $entrega['observaciones'] = $this->normalizarCadenaOpcional($observaciones, 'entrega.observaciones');
        }

        $entrega['latitud'] = $this->normalizarCoordenada($latitud, 'entrega.latitud');
        $entrega['longitud'] = $this->normalizarCoordenada($longitud, 'entrega.longitud');

        return $entrega;
    }

    private function normalizarEntregaOrigenB(array $delivery): array
    {
        $direccion = $this->obtenerCampo($delivery, ['address', 'direccion', 'direccionEntrega'], 'delivery.address');
        $latitud = $this->obtenerCampo($delivery, ['latitude', 'latitud'], 'delivery.latitude');
        $longitud = $this->obtenerCampo($delivery, ['longitude', 'longitud'], 'delivery.longitude');

        $entrega = [
            'direccion' => $this->normalizarCadena($direccion, 'entrega.direccion'),
        ];

        $referencia = $this->obtenerCampoOpcional($delivery, ['reference', 'referencia']);
        if ($referencia !== null) {
            $entrega['referencia'] = $this->normalizarCadenaOpcional($referencia, 'entrega.referencia');
        }

        $observaciones = $this->obtenerCampoOpcional($delivery, ['observations', 'observaciones', 'notas']);
        if ($observaciones !== null) {
            $entrega['observaciones'] = $this->normalizarCadenaOpcional($observaciones, 'entrega.observaciones');
        }

        $entrega['latitud'] = $this->normalizarCoordenada($latitud, 'entrega.latitud');
        $entrega['longitud'] = $this->normalizarCoordenada($longitud, 'entrega.longitud');

        return $entrega;
    }

    private function obtenerCampo(array $datos, array $claves, ?string $nombre = null): mixed
    {
        $encontradas = [];
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $datos)) {
                $encontradas[$clave] = $datos[$clave];
            }
        }

        if ($encontradas === []) {
            $campo = $nombre ?? $claves[0];
            throw ValidationException::withMessages([
                $campo => ['El campo es obligatorio.'],
            ]);
        }

        // Deteccion de valores ambiguos: si hay varias claves presentes con valores distintos.
        $valores = array_values($encontradas);
        $primero = $valores[0];
        foreach ($valores as $valor) {
            if ($valor !== $primero) {
                $campo = $nombre ?? $claves[0];
                throw ValidationException::withMessages([
                    $campo => ['Se detectaron valores ambiguos para el mismo campo.'],
                ]);
            }
        }

        return $primero;
    }

    private function obtenerCampoOpcional(array $datos, array $claves): mixed
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $datos) && $datos[$clave] !== null && $datos[$clave] !== '') {
                return $datos[$clave];
            }
        }
        return null;
    }

    private function tieneAlgunCampo(array $datos, array $claves): bool
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $datos)) {
                return true;
            }
        }
        return false;
    }

    private function normalizarEntero(mixed $valor, string $campo): int
    {
        if (is_bool($valor)) {
            throw ValidationException::withMessages([
                $campo => ['El valor debe ser un numero entero.'],
            ]);
        }

        if (is_int($valor)) {
            return $valor;
        }

        if (is_float($valor)) {
            if (floor($valor) !== $valor) {
                throw ValidationException::withMessages([
                    $campo => ['El valor debe ser un numero entero.'],
                ]);
            }
            return (int) $valor;
        }

        if (is_string($valor)) {
            $valor = trim($valor);
            if ($valor === '' || ! preg_match('/^[+-]?\d+$/', $valor)) {
                throw ValidationException::withMessages([
                    $campo => ['El valor debe ser un numero entero.'],
                ]);
            }
            return (int) $valor;
        }

        throw ValidationException::withMessages([
            $campo => ['El valor debe ser un numero entero.'],
        ]);
    }

    /**
     * Normaliza montos monetarios.
     *
     * Formatos aceptados:
     * - Entero: 5
     * - Decimal con punto: 5.50
     * - Decimal con coma: 5,50
     *
     * Se rechazan separadores de miles y combinaciones ambiguas.
     */
    private function normalizarMonto(mixed $valor, string $campo): float
    {
        if (is_bool($valor) || is_array($valor) || is_object($valor) || $valor === null) {
            throw ValidationException::withMessages([
                $campo => ['El monto monetario es obligatorio y debe ser valido.'],
            ]);
        }

        if (is_int($valor)) {
            return (float) $valor;
        }

        if (is_float($valor)) {
            if (! is_finite($valor)) {
                throw ValidationException::withMessages([
                    $campo => ['El monto monetario no es valido.'],
                ]);
            }
            return $valor;
        }

        if (is_string($valor)) {
            $valor = trim($valor);
            if ($valor === '') {
                throw ValidationException::withMessages([
                    $campo => ['El monto monetario es obligatorio.'],
                ]);
            }

            // Ambiguedad: dos separadores decimales o separador de miles.
            if (substr_count($valor, ',') + substr_count($valor, '.') > 1) {
                throw ValidationException::withMessages([
                    $campo => ['El monto monetario es ambiguo o esta mal formado.'],
                ]);
            }

            $normalizado = str_replace(',', '.', $valor);
            if (! preg_match('/^[+-]?\d+(\.\d+)?$/', $normalizado)) {
                throw ValidationException::withMessages([
                    $campo => ['El monto monetario no tiene un formato valido.'],
                ]);
            }

            return (float) $normalizado;
        }

        throw ValidationException::withMessages([
            $campo => ['El monto monetario no tiene un formato valido.'],
        ]);
    }

    private function normalizarCoordenada(mixed $valor, string $campo): float
    {
        if (is_bool($valor)) {
            throw ValidationException::withMessages([
                $campo => ['La coordenada debe ser numerica.'],
            ]);
        }

        if (is_int($valor) || is_float($valor)) {
            if (! is_finite((float) $valor)) {
                throw ValidationException::withMessages([
                    $campo => ['La coordenada no es valida.'],
                ]);
            }
            return (float) $valor;
        }

        if (is_string($valor)) {
            $valor = trim(str_replace(',', '.', $valor));
            if ($valor === '' || ! is_numeric($valor)) {
                throw ValidationException::withMessages([
                    $campo => ['La coordenada debe ser numerica.'],
                ]);
            }
            return (float) $valor;
        }

        throw ValidationException::withMessages([
            $campo => ['La coordenada debe ser numerica.'],
        ]);
    }

    private function normalizarCadena(mixed $valor, string $campo): string
    {
        if (! is_string($valor) && ! is_int($valor) && ! is_float($valor)) {
            throw ValidationException::withMessages([
                $campo => ['El valor debe ser una cadena de texto.'],
            ]);
        }
        $cadena = trim((string) $valor);
        if ($cadena === '') {
            throw ValidationException::withMessages([
                $campo => ['El campo es obligatorio.'],
            ]);
        }
        return $cadena;
    }

    private function normalizarCadenaOpcional(mixed $valor, string $campo): ?string
    {
        if ($valor === null) {
            return null;
        }
        if (! is_string($valor) && ! is_int($valor) && ! is_float($valor)) {
            throw ValidationException::withMessages([
                $campo => ['El valor debe ser una cadena de texto.'],
            ]);
        }
        $cadena = trim((string) $valor);
        return $cadena === '' ? null : $cadena;
    }
}
