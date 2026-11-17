<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function view(User $user, Pedido $pedido): bool
    {
        if ($user->esAdministrador()) {
            return true;
        }

        return $user->cliente_id !== null && $user->cliente_id === $pedido->cliente_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateEstado(User $user, Pedido $pedido): bool
    {
        return $user->esAdministrador();
    }
}
