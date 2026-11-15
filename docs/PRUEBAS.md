# Pruebas

## Casos de prueba implementados

Archivo: `tests/Feature/PedidoApiTest.php`

1. `test_crear_pedido_correctamente`: crea un pedido válido y verifica código 201 y estructura.
2. `test_rechazar_pedido_sin_cliente`: envía pedido sin cliente_id y espera 422.
3. `test_rechazar_productos_inexistentes`: usa producto_id inexistente y espera 422.
4. `test_rechazar_cantidades_negativas`: cantidad negativa y espera 422.
5. `test_rechazar_coordenadas_incorrectas`: latitud/longitud fuera de rango y espera 422.
6. `test_consultar_pedido_existente`: consulta un pedido creado y espera 200.
7. `test_consultar_pedido_inexistente`: consulta id inexistente y espera 404.
8. `test_actualizar_estado_correctamente`: cambia estado válido y espera 200.
9. `test_rechazar_estados_invalidos`: envía estado inexistente y espera 422.
10. `test_rechazar_transiciones_no_permitidas`: transición inválida y espera 422.
11. `test_estructura_respuesta_json`: verifica la estructura uniforme de la respuesta.
12. `test_totales_calculados_correctamente`: verifica subtotal, costo de entrega y total.

## Comandos ejecutados

```bash
php artisan test
```

## Resultados reales

Última ejecución: 14 pruebas, 14 aprobadas, 38 aserciones (incluye pruebas de ejemplo de Laravel). Tiempo aproximado 0.8 s.

Para ejecutar únicamente las pruebas de pedidos:

```bash
php artisan test --filter PedidoApiTest
```
