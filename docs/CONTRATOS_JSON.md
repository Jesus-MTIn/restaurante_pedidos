# Contratos JSON

Este documento describe los contratos de intercambio de información semántica de la API de Estación Burger.

## Contrato 1: Crear pedido

**Endpoint:** `POST /api/v1/pedidos`

**Descripción:** Registra un nuevo pedido asociado a un cliente, con uno o más productos y datos de entrega.

**Campos:**

| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| cliente_id | integer | Sí | Identificador del cliente que realiza el pedido. |
| productos | array | Sí | Lista de productos solicitados (mínimo uno). |
| productos[].producto_id | integer | Sí | Identificador del producto. |
| productos[].cantidad | integer | Sí | Cantidad solicitada (mayor a cero). |
| entrega | object | Sí | Datos de entrega del pedido. |
| entrega.direccion | string | Sí | Dirección de entrega (máx. 255 caracteres). |
| entrega.referencia | string | No | Referencia de ubicación. |
| entrega.observaciones | string | No | Observaciones para el repartidor. |
| entrega.latitud | number | Sí | Latitud geográfica (-90 a 90). |
| entrega.longitud | number | Sí | Longitud geográfica (-180 a 180). |

**Ejemplo correcto:**

```json
{
  "cliente_id": 1,
  "productos": [
    { "producto_id": 1, "cantidad": 2 },
    { "producto_id": 3, "cantidad": 1 }
  ],
  "entrega": {
    "direccion": "Calle Kennedy",
    "referencia": "Casa blanca, portón negro",
    "observaciones": "Entregar en la entrada principal",
    "latitud": -0.94886,
    "longitud": -80.63806
  }
}
```

**Ejemplos incorrectos:**

- Sin cliente:
```json
{
  "productos": [{ "producto_id": 1, "cantidad": 2 }],
  "entrega": { "direccion": "Calle Kennedy", "latitud": -0.94886, "longitud": -80.63806 }
}
```

- Producto inexistente:
```json
{
  "cliente_id": 1,
  "productos": [{ "producto_id": 999, "cantidad": 1 }],
  "entrega": { "direccion": "Calle Kennedy", "latitud": -0.94886, "longitud": -80.63806 }
}
```

- Cantidad negativa:
```json
{
  "cliente_id": 1,
  "productos": [{ "producto_id": 1, "cantidad": -2 }],
  "entrega": { "direccion": "Calle Kennedy", "latitud": -0.94886, "longitud": -80.63806 }
}
```

- Coordenadas inválidas:
```json
{
  "cliente_id": 1,
  "productos": [{ "producto_id": 1, "cantidad": 2 }],
  "entrega": { "direccion": "Calle Kennedy", "latitud": 200, "longitud": -300 }
}
```

**Respuesta exitosa (201):**

```json
{
  "success": true,
  "message": "Pedido registrado correctamente",
  "data": { "...": "..." },
  "errors": null
}
```

## Contrato 2: Consultar pedido

**Endpoint:** `GET /api/v1/pedidos/{id}`

**Descripción:** Obtiene la información completa de un pedido.

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | integer | Identificador del pedido. |
| codigo | string | Código único del pedido. |
| estado | string | Estado actual del pedido. |
| cliente | object | Datos del cliente. |
| productos | array | Lista de productos con cantidades y precios. |
| subtotal | number | Suma de subtotales de los productos. |
| costo_entrega | number | Costo de entrega. |
| total | number | Total a pagar. |
| entrega | object | Datos de entrega y coordenadas. |
| created_at | string | Fecha de creación. |

**Respuesta exitosa (200):** estructura uniforme con `data` conteniendo los campos anteriores.

**Error (404):**

```json
{
  "success": false,
  "message": "Recurso no encontrado",
  "data": null,
  "errors": null
}
```

## Contrato 3: Actualizar estado

**Endpoint:** `PATCH /api/v1/pedidos/{id}/estado`

**Descripción:** Actualiza el estado de un pedido respetando las transiciones permitidas.

**Campos:**

| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| estado | string | Sí | Nuevo estado del pedido. |

**Estados válidos:** `pendiente`, `confirmado`, `en_preparacion`, `en_camino`, `entregado`, `cancelado`.

**Transiciones permitidas:**
- pendiente → confirmado, cancelado
- confirmado → en_preparacion, cancelado
- en_preparacion → en_camino, cancelado
- en_camino → entregado
- entregado → (ninguna)
- cancelado → (ninguna)

**Ejemplo correcto:**

```json
{
  "estado": "en_camino"
}
```

**Ejemplo incorrecto (estado inexistente):**

```json
{
  "estado": "despachado"
}
```

**Ejemplo incorrecto (transición no permitida):**

```json
{
  "estado": "entregado"
}
```
(cuando el pedido está en estado `pendiente`).
