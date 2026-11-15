<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DireccionEntregaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'direccion' => $this->direccion,
            'referencia' => $this->referencia,
            'observaciones' => $this->observaciones,
            'latitud' => (float) $this->latitud,
            'longitud' => (float) $this->longitud,
        ];
    }
}
