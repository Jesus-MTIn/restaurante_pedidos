<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'disponibilidad',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponibilidad' => 'boolean',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }
}
