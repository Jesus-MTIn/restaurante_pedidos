<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionEntrega extends Model
{
    use HasFactory;

    protected $table = 'direccion_entregas';

    protected $fillable = [
        'pedido_id',
        'direccion',
        'referencia',
        'observaciones',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
