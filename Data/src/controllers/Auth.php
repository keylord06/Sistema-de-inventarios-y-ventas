<?php

declare(strict_types=1);

namespace App\controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

final class Auth {
    public function __construct(private ContainerInterface $container) {}

    public function credentials(Request $request, Response $response): Response {

        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $this->container->get('eloquent');
        $user = User::query()->where('username', $username)->where('activo', true)->first();

        if($user === null || !password_verify($password, (string) $user->password)){
            return $this->json($response, ['error' => 'Credenciales inválidas'], 401);
        }

        return $this->json($response, [
            'id' => (int) $user->id,
            'username' => $user->username,
            'rol_id' => (int) $user->rol_id,
        ],200);
    }

    public function updateTokenRef(Request $request, Response $response, $args): Response {
        $data = $request->getParsedBody();

        $this->container->get('eloquent');

        $user = User::find($args['id']);
        $user->tkRef = trim((string) ($data['tkRef'] ?? ''));
        $user->save();
        return $this->json($response, ['msj' => 'Token refresco modificado'],200);
    }
    private function json(Response $response, array $payload, int $status): Response {
        
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }

}