<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class CategoriaValidator{
 private function reglas(): array{
   return[
      'nombre' => [
         'regla' => v::stringType()
            ->notBlank()
            ->length(v::between(2,100)),
         'mensaje' => 'El nombre es obligatorio y debe tener entre 2 y 100 caracteres',
      ],
   ];

 }

    public function validate(array $data):array{
    $errores = [];

    foreach($this->reglas() as $campo => $config){
        $valor = $data[$campo] ?? null;
        $regla = $config['regla'];
        $mensaje = $config['mensaje'];
        if($regla->validate($valor)->hasFailed()){
                $errores[$campo] = $mensaje;
        }
    }

    return $errores;
  }

}
