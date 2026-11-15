<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Producto;

class CrearPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'entrega' => ['required', 'array'],
            'entrega.direccion' => ['required', 'string', 'max:255'],
            'entrega.referencia' => ['nullable', 'string', 'max:255'],
            'entrega.observaciones' => ['nullable', 'string', 'max:500'],
            'entrega.latitud' => ['required', 'numeric', 'between:-90,90'],
            'entrega.longitud' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'productos.required' => 'Debe incluir al menos un producto.',
            'productos.min' => 'Debe incluir al menos un producto.',
            'productos.*.producto_id.required' => 'El identificador del producto es obligatorio.',
            'productos.*.producto_id.exists' => 'Uno de los productos no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min' => 'La cantidad debe ser un numero positivo.',
            'entrega.required' => 'Los datos de entrega son obligatorios.',
            'entrega.direccion.required' => 'La direccion de entrega es obligatoria.',
            'entrega.latitud.required' => 'La latitud es obligatoria.',
            'entrega.latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'entrega.longitud.required' => 'La longitud es obligatoria.',
            'entrega.longitud.between' => 'La longitud debe estar entre -180 y 180.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $productos = $this->input('productos', []);
            foreach ($productos as $index => $item) {
                $producto = Producto::find($item['producto_id'] ?? null);
                if ($producto && ! $producto->disponibilidad) {
                    $validator->errors()->add(
                        "productos.{$index}.producto_id",
                        'El producto seleccionado no esta disponible.'
                    );
                }
            }
        });
    }
}
