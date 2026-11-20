<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class ProveedorValidator{
 private function reglas(): array{
   return[
      'nombre' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(2,150)),
         'mensaje' => 'El nombre es obligatorio y debe tener entre 2 y 150 caracteres',
      ],
      'telefono' => [
         'regla' => v::stringType()->length(v::max(20)),
         'mensaje' => 'El teléfono debe tener máximo 20 caracteres',
      ],
      'email' => [
         'regla' => v::stringType()->email(),
         'mensaje' => 'El email no tiene un formato válido',
      ],
   ];

 }

    public function validate(array $data):array{
    $errores = [];

    foreach($this->reglas() as $campo => $config){
        $valor = $data[$campo] ?? null;
        $regla = $config['regla'];
        $mensaje = $config['mensaje'];

        // telefono y email son opcionales: si no vienen, no se validan
        if(in_array($campo, ['telefono', 'email'], true) && ($valor === null || $valor === ''))
                continue;

        if($regla->validate($valor)->hasFailed()){
                $errores[$campo] = $mensaje;
        }
    }

    return $errores;
  }

}
