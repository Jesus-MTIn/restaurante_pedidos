# Estación Burger API

API REST para gestionar pedidos del sistema Estación Burger, desarrollada con Laravel. Implementa el intercambio de información semántica entre una aplicación cliente y el backend.

## Requisitos

- PHP 8.2 o superior (con extensiones pdo_sqlite, sqlite3, mbstring, openssl, etc.)
- Composer
- SQLite

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configura `.env` con `DB_CONNECTION=sqlite`.

## Configuración de base de datos

Crea el archivo SQLite (opcional, Laravel lo crea al migrar):

```bash
touch database/database.sqlite
```

En Windows PowerShell:

```powershell
New-Item database/database.sqlite
```

## Migraciones y seeders

```bash
php artisan migrate --seed
```

## Ejecución

```bash
php artisan serve
```

La API quedará disponible en `http://127.0.0.1:8000`.

## Endpoints

- `POST /api/v1/pedidos` - Crear pedido (contrato original).
- `POST /api/v1/pedidos/origen-a` - Crear pedido desde origen A (formulario web) con normalizacion.
- `POST /api/v1/pedidos/origen-b` - Crear pedido desde origen B (servicio externo) con normalizacion.
- `GET /api/v1/pedidos/{id}` - Consultar pedido.
- `PATCH /api/v1/pedidos/{id}/estado` - Actualizar estado.

## Pruebas

```bash
php artisan test
```

## Verificador PowerShell

```powershell
powershell -ExecutionPolicy Bypass -File scripts/verificar-practica.ps1
```

El informe se genera en `docs/INFORME_VERIFICACION.md`.

## Documentación

- `docs/CONTRATOS_JSON.md`
- `docs/FLUJO_INTERCAMBIO.md`
- `docs/ARQUITECTURA.md`
- `docs/PRUEBAS.md`
