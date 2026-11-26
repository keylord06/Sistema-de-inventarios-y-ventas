<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

// Rol_id de la tabla roles: 1 = Admin, 2 = Vendedor, 3 = Bodeguero.
// Middleware simple para restringir una ruta a ciertos roles.
// El JWT ya viene decodificado en el atributo "token" (lo pone JwtAuthentication).
function soloRoles(array $rolesPermitidos): callable {
    return function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($rolesPermitidos): ResponseInterface {
        $token = $request->getAttribute('token');
        $rolId = isset($token['rol_id']) ? (int) $token['rol_id'] : null;

        if ($rolId === null || !in_array($rolId, $rolesPermitidos, true)) {
            $response = new Response(403);
            $response->getBody()->write(json_encode(
                ['error' => 'No tiene permisos para realizar esta acción.'],
                JSON_UNESCAPED_UNICODE
            ));
            return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return $handler->handle($request);
    };
}
