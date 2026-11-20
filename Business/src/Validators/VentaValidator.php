<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class VentaValidator{

    public function validate(array $data):array{
    $errores = [];

    $clienteId = $data['cliente_id'] ?? null;
    if ($clienteId !== null && !v::intType()->positive()->validate($clienteId)) {
        $errores['cliente_id'] = 'Debe ser un entero positivo o null.';
    }

    $usuarioId = $data['usuario_id'] ?? null;
    if (!v::intType()->positive()->validate($usuarioId)) {
        $errores['usuario_id'] = 'El usuario_id es obligatorio y debe ser un entero positivo.';
    }

    $items = $data['items'] ?? null;
    if (!is_array($items) || $items === []) {
        $errores['items'] = 'Debe enviar al menos un producto.';
    } else {
        foreach ($items as $i => $item) {
            $productoId = $item['producto_id'] ?? null;
            $cantidad = $item['cantidad'] ?? null;
            if (!v::intType()->positive()->validate($productoId) || !v::intType()->positive()->validate($cantidad)) {
                $errores["items.{$i}"] = 'producto_id y cantidad deben ser enteros positivos.';
            }
        }
    }

    return $errores;
  }

}
