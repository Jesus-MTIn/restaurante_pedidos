<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo_electronico' => $this->correo_electronico,
            'telefono' => $this->telefono,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
