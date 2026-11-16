# Comandos reproducibles - Práctica 2.3

## Ejecutar pruebas

```bash
php artisan test
php artisan test --filter NormalizadorPedidosTest
php artisan test --filter NormalizacionApiTest
```

## Probar endpoints normalizados

```bash
php artisan serve
```

Origen A:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/pedidos/origen-a \
  -H "Content-Type: application/json" \
  -d @evidencias/practica-2.3/entrada-origen-a.json
```

Origen B:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/pedidos/origen-b \
  -H "Content-Type: application/json" \
  -d @evidencias/practica-2.3/entrada-origen-b.json
```

Entrada inválida:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/pedidos/origen-a \
  -H "Content-Type: application/json" \
  -d @evidencias/practica-2.3/entrada-invalida.json
```

## Capturas manuales sugeridas

1. Ejecución de `php artisan test` con todas las pruebas aprobadas.
2. Respuesta 201 del endpoint `POST /api/v1/pedidos/origen-a`.
3. Respuesta 201 del endpoint `POST /api/v1/pedidos/origen-b`.
4. Respuesta 422 del endpoint con entrada inválida.
5. Contenido de `app/Services/NormalizadorPedidos.php`.
6. Diagrama de la documentación.