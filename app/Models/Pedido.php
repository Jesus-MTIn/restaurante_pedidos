<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    public const ESTADOS_VALIDOS = [
        'pendiente',
        'confirmado',
        'en_preparacion',
        'en_camino',
        'entregado',
        'cancelado',
    ];

    public const TRANSICIONES = [
        'pendiente' => ['confirmado', 'cancelado'],
        'confirmado' => ['en_preparacion', 'cancelado'],
        'en_preparacion' => ['en_camino', 'cancelado'],
        'en_camino' => ['entregado'],
        'entregado' => [],
        'cancelado' => [],
    ];

    protected $fillable = [
        'codigo',
        'cliente_id',
        'estado',
        'subtotal',
        'costo_entrega',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'costo_entrega' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function direccionEntrega(): HasOne
    {
        return $this->hasOne(DireccionEntrega::class, 'pedido_id');
    }

    public static function puedeTransicionar(string $actual, string $nuevo): bool
    {
        return in_array($nuevo, self::TRANSICIONES[$actual] ?? [], true);
    }
}
