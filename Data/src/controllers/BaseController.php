<?php
declare(strict_types=1);

namespace App\controllers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Funciones comunes para todos los controladores de la capa de datos.
 */
abstract class BaseController {

    public function __construct(protected ContainerInterface $container) {}

    /** Inicializa Eloquent y devuelve el Capsule (para transacciones y SPs). */
    protected function db(): Capsule {
        return $this->container->get('eloquent');
    }

    protected function body(Request $request): array {
        $data = $request->getParsedBody();
        return is_array($data) ? $data : [];
    }

    protected function json(Response $response, array $payload, int $status = 200): Response {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

    /**
     * Valida {offset}/{limit} de las rutas /filter.
     * Devuelve [offset, limit] o null si son inválidos.
     */
    protected function paginacion(array $args): ?array {
        $offset = filter_var($args['offset'] ?? 0, FILTER_VALIDATE_INT);
        $limit = filter_var($args['limit'] ?? 20, FILTER_VALIDATE_INT);

        if ($offset === false || $offset < 0 || $limit === false || $limit < 1 || $limit > 100) {
            return null;
        }
        return [$offset, $limit];
    }

    protected function errorPaginacion(Response $response): Response {
        return $this->json($response, [
            'error' => 'El offset debe ser mayor o igual a 0 y el limite debe estar entre 1 y 100'
        ], 422);
    }

    /**
     * Lee un texto opcional del body. '' se convierte en null.
     * Si supera $max agrega el error en $errors.
     */
    protected function textoOpcional(array $data, string $campo, int $max, array &$errors): ?string {
        if (!array_key_exists($campo, $data) || $data[$campo] === null) {
            return null;
        }
        $valor = trim((string) $data[$campo]);
        if ($valor === '') {
            return null;
        }
        if (mb_strlen($valor) > $max) {
            $errors[$campo] = "Debe tener máximo {$max} caracteres.";
        }
        return $valor;
    }

    /** Código de error de MySQL de una QueryException (1062 duplicado, 1451/1452 llave foránea). */
    protected function codigoMysql(\Illuminate\Database\QueryException $e): int {
        return (int) ($e->errorInfo[1] ?? 0);
    }
}
