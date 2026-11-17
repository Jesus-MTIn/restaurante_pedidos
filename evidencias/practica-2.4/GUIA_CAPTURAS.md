# Guía de capturas - Práctica 2.4

## Preparación

```bash
php artisan migrate:fresh --seed
php artisan serve
```

Usuarios de prueba (ficticios):
- admin@estacionburger.test / password (rol admin)
- cliente1@estacionburger.test / password (rol user, cliente_id = 1)
- cliente2@estacionburger.test / password (rol user, cliente_id = 2)

## Figura 1 - Código de autenticación
- Abrir: `routes/api.php`
- Mostrar: `middleware('auth.basic')`.
- Pie: "Figura 1. Middleware de autenticación aplicado a las rutas de pedidos."

## Figura 2 - Código de autorización
- Abrir: `app/Policies/PedidoPolicy.php`
- Mostrar: métodos `view`, `create`, `updateEstado`.
- Pie: "Figura 2. Policy de autorización por propietario y rol."

## Figura 3 - Matriz de acceso
- Abrir: `evidencias/practica-2.4/matriz-acceso.json` o la tabla de `docs/practica-2.4/SEGURIDAD.md`.
- Pie: "Figura 3. Matriz de acceso de los endpoints protegidos."

## Figura 4 - P1 acceso autorizado HTTP 200
- Herramienta: Postman o curl.
- Petición: `GET http://127.0.0.1:8000/api/v1/pedidos/1` con Basic Auth `cliente1@estacionburger.test`.
- Resultado: HTTP 200, `success = true`.
- Pie: "Figura 4. P1: acceso autorizado a un pedido propio."

## Figura 5 - P2 sin autenticación HTTP 401
- Petición: `GET http://127.0.0.1:8000/api/v1/pedidos/1` sin credenciales.
- Resultado: HTTP 401.
- Pie: "Figura 5. P2: rechazo de solicitud sin autenticación."

## Figura 6 - P3 usuario sin permisos HTTP 403
- Petición: `GET http://127.0.0.1:8000/api/v1/pedidos/1` con Basic Auth `cliente2@estacionburger.test`.
- Resultado: HTTP 403.
- Pie: "Figura 6. P3: usuario autenticado sin permisos."

## Figura 7 - Modificación de estado por administrador
- Petición: `PATCH http://127.0.0.1:8000/api/v1/pedidos/1/estado` con Basic Auth admin, body `{"estado":"confirmado"}`.
- Resultado: HTTP 200.
- Pie: "Figura 7. Modificación de estado permitida al administrador."

## Figura 8 - Usuario normal no modifica estado
- Petición: misma PATCH con Basic Auth cliente1.
- Resultado: HTTP 403.
- Pie: "Figura 8. Restricción de modificación de estados a usuarios normales."

## Figura 9 - Exclusión de .env
- Abrir: `.gitignore` y verificar que `.env` está listado.
- Pie: "Figura 9. Exclusión de archivos de entorno del control de versiones."

## Figura 10 - Pruebas automatizadas
- Terminal: `php artisan test`.
- Mostrar: `34 passed`.
- Pie: "Figura 10. Resultado de las pruebas automatizadas de seguridad."

## Figura 11 - Diagrama de componentes
- Abrir: `docs/practica-2.4/SEGURIDAD.md`, sección 8.
- Pie: "Figura 11. Diagrama de componentes de seguridad."