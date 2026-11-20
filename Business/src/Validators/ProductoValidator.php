<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class ProductoValidator{
 private function reglas(): array{
   return[
      'codigo' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(1,50)),
         'mensaje' => 'El código es obligatorio y debe tener máximo 50 caracteres',
      ],
      'nombre' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(2,150)),
         'mensaje' => 'El nombre es obligatorio y debe tener entre 2 y 150 caracteres',
      ],
      'categoria_id' => [
         'regla' => v::intType()->positive(),
         'mensaje' => 'Debe tener una categoría válida',
      ],
      'proveedor_id' => [
         'regla' => v::intType()->positive(),
         'mensaje' => 'El proveedor debe ser un entero positivo',
      ],
      'precio' => [
         'regla' => v::numericVal()->min(0, true),
         'mensaje' => 'El precio es obligatorio y debe ser un número mayor o igual a 0',
      ],
      'stock' => [
         'regla' => v::intType()->min(0, true),
         'mensaje' => 'El stock debe ser un entero mayor o igual a 0',
      ],
      'stock_minimo' => [
         'regla' => v::intType()->min(0, true),
         'mensaje' => 'El stock mínimo debe ser un entero mayor o igual a 0',
      ],
   ];

 }

    public function validate(array $data):array{
    $errores = [];

    // campos opcionales: si no vienen, no se validan (Datos les pone un valor por defecto)
    $opcionales = ['proveedor_id', 'stock', 'stock_minimo'];

    foreach($this->reglas() as $campo => $config){
        $valor = $data[$campo] ?? null;
        $regla = $config['regla'];
        $mensaje = $config['mensaje'];

        if(in_array($campo, $opcionales, true) && ($valor === null || $valor === ''))
                continue;

        if($regla->validate($valor)->hasFailed()){
                $errores[$campo] = $mensaje;
        }
    }

    return $errores;
  }

}
