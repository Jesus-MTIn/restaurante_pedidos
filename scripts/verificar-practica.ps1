<#
.SYNOPSIS
    Verificador automático de la práctica de intercambio de información semántica.
.DESCRIPTION
    Comprueba la existencia de los componentes del proyecto Laravel y ejecuta las pruebas.
#>

$ErrorActionPreference = 'Continue'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$root = Resolve-Path (Join-Path $scriptDir '..')
Set-Location $root

$docsDir = Join-Path $root 'docs'
if (-not (Test-Path $docsDir)) { New-Item -ItemType Directory -Path $docsDir | Out-Null }

$reportPath = Join-Path $docsDir 'INFORME_VERIFICACION.md'

$aprobados = 0
$pendientes = 0
$errores = 0
$archivosEncontrados = @()
$requisitos = @()
$pruebasEjecutadas = 0
$pruebasAprobadas = 0
$pruebasFallidas = 0
$pendientesLista = @()

function Registrar {
    param(
        [string]$Estado,
        [string]$Mensaje
    )
    Write-Host "[$Estado] $Mensaje"
    $script:requisitos += "- [$Estado] $Mensaje"
    switch ($Estado) {
        'APROBADO' { $script:aprobados++ }
        'PENDIENTE' { $script:pendientes++; $script:pendientesLista += $Mensaje }
        'ERROR' { $script:errores++ }
    }
}

function Verificar-Archivo {
    param([string]$Ruta, [string]$Etiqueta, [switch]$Obligatorio)
    $full = Join-Path $root $Ruta
    if (Test-Path $full) {
        Registrar 'APROBADO' "$Etiqueta ($Ruta)"
        $script:archivosEncontrados += $Ruta
    } else {
        if ($Obligatorio) {
            Registrar 'PENDIENTE' "$Etiqueta ($Ruta)"
        } else {
            Registrar 'PENDIENTE' "$Etiqueta ($Ruta)"
        }
    }
}

function Verificar-Contenido {
    param([string]$Ruta, [string]$Patron, [string]$Etiqueta)
    $full = Join-Path $root $Ruta
    if (Test-Path $full) {
        $content = Get-Content $full -Raw
        if ($content -match $Patron) {
            Registrar 'APROBADO' $Etiqueta
        } else {
            Registrar 'PENDIENTE' $Etiqueta
        }
    } else {
        Registrar 'PENDIENTE' $Etiqueta
    }
}

# 1. Proyecto Laravel
Verificar-Archivo 'artisan' 'Existencia del proyecto Laravel (artisan)' -Obligatorio
Verificar-Archivo 'composer.json' 'Archivo composer.json' -Obligatorio
Verificar-Archivo 'vendor/autoload.php' 'Dependencias instaladas (vendor/autoload.php)' -Obligatorio

# 2. Configuración
Verificar-Archivo '.env' 'Archivo de configuración .env' -Obligatorio
Verificar-Archivo 'config/database.php' 'Configuración de base de datos' -Obligatorio
Verificar-Archivo 'bootstrap/app.php' 'Bootstrap de la aplicación' -Obligatorio
Verificar-Contenido '.env' 'DB_CONNECTION=sqlite' 'Configuración de SQLite en .env'

# Verificar extensión PDO SQLite
try {
    $ext = php -m 2>$null | Select-String -Pattern 'pdo_sqlite'
    if ($ext) {
        Registrar 'APROBADO' 'Extensión PHP pdo_sqlite disponible'
    } else {
        Registrar 'ERROR' 'Extensión PHP pdo_sqlite no disponible'
    }
} catch {
    Registrar 'ERROR' 'No se pudo verificar la extensión pdo_sqlite'
}

# 3. Migraciones
Verificar-Archivo 'database/migrations' 'Directorio de migraciones' -Obligatorio
$migraciones = Get-ChildItem (Join-Path $root 'database/migrations') -Filter '*create_clientes_table.php' -ErrorAction SilentlyContinue
if ($migraciones) { Registrar 'APROBADO' 'Migración de clientes' } else { Registrar 'PENDIENTE' 'Migración de clientes' }
$migraciones = Get-ChildItem (Join-Path $root 'database/migrations') -Filter '*create_productos_table.php' -ErrorAction SilentlyContinue
if ($migraciones) { Registrar 'APROBADO' 'Migración de productos' } else { Registrar 'PENDIENTE' 'Migración de productos' }
$migraciones = Get-ChildItem (Join-Path $root 'database/migrations') -Filter '*create_pedidos_table.php' -ErrorAction SilentlyContinue
if ($migraciones) { Registrar 'APROBADO' 'Migración de pedidos' } else { Registrar 'PENDIENTE' 'Migración de pedidos' }
$migraciones = Get-ChildItem (Join-Path $root 'database/migrations') -Filter '*create_detalle_pedidos_table.php' -ErrorAction SilentlyContinue
if ($migraciones) { Registrar 'APROBADO' 'Migración de detalles de pedido' } else { Registrar 'PENDIENTE' 'Migración de detalles de pedido' }
$migraciones = Get-ChildItem (Join-Path $root 'database/migrations') -Filter '*create_direccion_entregas_table.php' -ErrorAction SilentlyContinue
if ($migraciones) { Registrar 'APROBADO' 'Migración de direcciones de entrega' } else { Registrar 'PENDIENTE' 'Migración de direcciones de entrega' }

# 4. Modelos
foreach ($m in @('Cliente','Producto','Pedido','DetallePedido','DireccionEntrega')) {
    Verificar-Archivo "app/Models/$m.php" "Modelo $m" -Obligatorio
}

# 5. Controladores
Verificar-Archivo 'app/Http/Controllers/Api/V1/PedidoController.php' 'Controlador PedidoController' -Obligatorio

# 6. Form Requests
Verificar-Archivo 'app/Http/Requests/CrearPedidoRequest.php' 'Form Request CrearPedidoRequest' -Obligatorio
Verificar-Archivo 'app/Http/Requests/ActualizarEstadoPedidoRequest.php' 'Form Request ActualizarEstadoPedidoRequest' -Obligatorio

# 7. JsonResources
foreach ($r in @('ClienteResource','ProductoResource','DetallePedidoResource','DireccionEntregaResource','PedidoResource')) {
    Verificar-Archivo "app/Http/Resources/$r.php" "JsonResource $r" -Obligatorio
}

# 8. Rutas API
Verificar-Archivo 'routes/api.php' 'Archivo de rutas API' -Obligatorio
Verificar-Contenido 'routes/api.php' 'pedidos' 'Rutas de pedidos registradas'

# 9. Contratos JSON
Verificar-Archivo 'docs/CONTRATOS_JSON.md' 'Documentación de contratos JSON' -Obligatorio
Verificar-Contenido 'docs/CONTRATOS_JSON.md' 'POST /api/v1/pedidos' 'Contrato de creación de pedido documentado'
Verificar-Contenido 'docs/CONTRATOS_JSON.md' 'GET /api/v1/pedidos' 'Contrato de consulta de pedido documentado'
Verificar-Contenido 'docs/CONTRATOS_JSON.md' 'PATCH /api/v1/pedidos' 'Contrato de actualización de estado documentado'

# 10. Documentación
foreach ($d in @('FLUJO_INTERCAMBIO.md','ARQUITECTURA.md','PRUEBAS.md')) {
    Verificar-Archivo "docs/$d" "Documentación $d" -Obligatorio
}

# 11. Pruebas
Verificar-Archivo 'tests/Feature/PedidoApiTest.php' 'Pruebas automatizadas de pedidos' -Obligatorio

# 12. Ejecución real de pruebas
Write-Host 'Ejecutando pruebas automatizadas...'
$testOutput = & php artisan test 2>&1 | Out-String
$exitCode = $LASTEXITCODE
$testOutput | Out-File -FilePath (Join-Path $root 'docs\ULTIMA_EJECUCION_PRUEBAS.txt') -Encoding utf8

if ($exitCode -eq 0) {
    Registrar 'APROBADO' 'Ejecución de pruebas automatizadas (php artisan test)'
} else {
    Registrar 'ERROR' 'Ejecución de pruebas automatizadas (php artisan test)'
}

# Intentar extraer contadores
if ($testOutput -match 'Tests:\s*(\d+).*?(\d+)\s*passed') {
    $pruebasEjecutadas = [int]$matches[1]
    $pruebasAprobadas = [int]$matches[2]
} elseif ($testOutput -match '(\d+)\s*passed') {
    $pruebasAprobadas = [int]$matches[1]
    $pruebasEjecutadas = $pruebasAprobadas
}
if ($testOutput -match '(\d+)\s*failed') {
    $pruebasFallidas = [int]$matches[1]
}

# 13. Evidencias
Verificar-Archivo 'evidencias/GUIA_CAPTURAS.md' 'Guía de capturas de evidencias' -Obligatorio

# 14. Script de verificación (auto)
Verificar-Archivo 'scripts/verificar-practica.ps1' 'Script de verificación' -Obligatorio

# Resultado general
$resultadoGeneral = 'APROBADO'
if ($pendientes -gt 0 -or $errores -gt 0) { $resultadoGeneral = 'CON PENDIENTES/ERRORES' }
if ($pruebasFallidas -gt 0) { $resultadoGeneral = 'FALLIDO' }

# Generar informe
$fecha = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
$informe = @"
# Informe de Verificación

**Fecha de verificación:** $fecha

## Requisitos comprobados

$($requisitos -join "`n")

## Archivos encontrados

$($archivosEncontrados | ForEach-Object { "- $_" } | Out-String)

## Pruebas ejecutadas

- Comando: php artisan test
- Pruebas ejecutadas: $pruebasEjecutadas
- Pruebas aprobadas: $pruebasAprobadas
- Pruebas fallidas: $pruebasFallidas

## Requisitos pendientes

$(if ($pendientesLista.Count -gt 0) { $pendientesLista | ForEach-Object { "- $_" } | Out-String } else { 'Ninguno.' })

## Resultado general

$resultadoGeneral

Resumen: Aprobados=$aprobados, Pendientes=$pendientes, Errores=$errores.
"@

Set-Content -Path $reportPath -Value $informe -Encoding utf8
Write-Host ''
Write-Host "Informe generado en: $reportPath"

if ($pendientes -gt 0 -or $errores -gt 0 -or $pruebasFallidas -gt 0) {
    exit 1
} else {
    exit 0
}
