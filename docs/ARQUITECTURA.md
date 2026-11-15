# Arquitectura del sistema

## Visión general

La aplicación es una API REST construida con Laravel, que gestiona pedidos de un restaurante. La arquitectura sigue el patrón MVC adaptado a APIs, con capas de validación, serialización y persistencia.

## Componentes

### Modelos (app/Models)
- `Cliente`: representa a los clientes.
- `Producto`: representa los productos del menú.
- `Pedido`: representa un pedido.
- `DetallePedido`: líneas de un pedido (producto, cantidad, precio).
- `DireccionEntrega`: dirección y coordenadas de entrega.

### Controladores (app/Http/Controllers/Api/V1)
- `PedidoController`: gestiona la creación, consulta y actualización de estado de pedidos. Es pequeño y delega la validación a Form Requests.

### Form Requests (app/Http/Requests)
- `CrearPedidoRequest`: valida la creación de pedidos.
- `ActualizarEstadoPedidoRequest`: valida la actualización de estado.

### JsonResources (app/Http/Resources)
- `ClienteResource`, `ProductoResource`, `DetallePedidoResource`, `DireccionEntregaResource`, `PedidoResource`: serializan las entidades a JSON normalizado.

### Migraciones (database/migrations)
- Definen el esquema de las tablas y sus claves foráneas.

### Seeders (database/seeders)
- `ClienteSeeder`, `ProductoSeeder`: datos iniciales de prueba.

### Rutas (routes/api.php)
- Define los endpoints bajo el prefijo `/api/v1`.

## Relaciones entre modelos

- Cliente 1:N Pedido
- Pedido 1:N DetallePedido
- Pedido 1:1 DireccionEntrega
- Producto 1:N DetallePedido

## Flujo de datos

1. Solicitud HTTP → Ruta → Controlador.
2. Form Request valida y deserializa.
3. Controlador procesa y persiste mediante Eloquent.
4. JsonResource serializa la respuesta.
5. Respuesta HTTP uniforme al cliente.
