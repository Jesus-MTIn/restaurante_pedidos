# Comandos reproducibles - Práctica 2.4

## Preparación

```bash
php artisan migrate:fresh --seed
php artisan serve
```

## P1 - Acceso autorizado

```bash
curl -i http://127.0.0.1:8000/api/v1/pedidos/1 \
  -H "Authorization: Basic $(printf 'cliente1@estacionburger.test:password' | base64)"
```

## P2 - Sin autenticación

```bash
curl -i http://127.0.0.1:8000/api/v1/pedidos/1
```

## P3 - Usuario sin permisos

```bash
curl -i http://127.0.0.1:8000/api/v1/pedidos/1 \
  -H "Authorization: Basic $(printf 'cliente2@estacionburger.test:password' | base64)"
```

## Modificación de estado por administrador

```bash
curl -i -X PATCH http://127.0.0.1:8000/api/v1/pedidos/1/estado \
  -H "Content-Type: application/json" \
  -H "Authorization: Basic $(printf 'admin@estacionburger.test:password' | base64)" \
  -d '{"estado":"confirmado"}'
```

## Pruebas automatizadas

```bash
php artisan test
```