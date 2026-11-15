# Flujo de intercambio de información

Este documento explica cómo se realiza el intercambio de información entre la aplicación cliente y el backend Laravel.

## 1. Envío del JSON

La aplicación cliente construye un documento JSON con los datos del pedido y lo envía mediante una solicitud HTTP `POST` al endpoint `/api/v1/pedidos`, indicando el encabezado `Content-Type: application/json`.

## 2. Recepción en Laravel

El servidor web recibe la solicitud y Laravel la enruta hacia el controlador `PedidoController::store`. El middleware de Laravel parsea automáticamente el cuerpo JSON y lo convierte en un objeto de solicitud (`Request`).

## 3. Deserialización

Laravel deserializa el JSON recibido y lo mapea a un array asociativo accesible mediante el Form Request `CrearPedidoRequest`. Los datos anidados (productos, entrega) se interpretan como estructuras internas.

## 4. Validación

El Form Request valida:
- Campos obligatorios y opcionales.
- Tipos de datos (enteros, cadenas, numéricos).
- Longitudes máximas.
- Existencia de cliente y productos en la base de datos.
- Cantidades positivas.
- Coordenadas geográficas válidas.
- Disponibilidad de los productos.

Si la validación falla, se devuelve una respuesta HTTP 422 con la estructura uniforme de errores.

## 5. Procesamiento

Si la validación es exitosa, el controlador:
- Crea el pedido en la base de datos.
- Obtiene el precio de cada producto desde la base de datos (no confía en el cliente).
- Calcula los subtotales y el total.
- Almacena los detalles del pedido.
- Registra la dirección de entrega y las coordenadas.

Todo se ejecuta dentro de una transacción para garantizar la integridad.

## 6. Serialización

La información almacenada se transforma mediante `JsonResource` (`PedidoResource`, `ClienteResource`, `DetallePedidoResource`, etc.) a una estructura JSON normalizada y consistente.

## 7. Respuesta al cliente

El cliente recibe una respuesta JSON con la estructura:

```json
{
  "success": true,
  "message": "Pedido registrado correctamente",
  "data": { ... },
  "errors": null
}
```

En caso de error, la estructura se mantiene con `success: false` y los detalles en `errors`.
