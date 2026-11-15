<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'estado' => $this->estado,
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'productos' => DetallePedidoResource::collection($this->whenLoaded('detalles')),
            'subtotal' => (float) $this->subtotal,
            'costo_entrega' => (float) $this->costo_entrega,
            'total' => (float) $this->total,
            'entrega' => new DireccionEntregaResource($this->whenLoaded('direccionEntrega')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
