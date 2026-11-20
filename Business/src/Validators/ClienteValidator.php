<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class ClienteValidator{
 private function reglas(): array{
   return[
      'cedula' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(1,20)),
         'mensaje' => 'La cédula es obligatoria y debe tener máximo 20 caracteres',
      ],
      'nombre_completo' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(2,150)),
         'mensaje' => 'El nombre completo es obligatorio y debe tener entre 2 y 150 caracteres',
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

        if(in_array($campo, ['telefono', 'email'], true) && ($valor === null || $valor === ''))
                continue;

        if($regla->validate($valor)->hasFailed()){
                $errores[$campo] = $mensaje;
        }
    }

    return $errores;
  }

}
