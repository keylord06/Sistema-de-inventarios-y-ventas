<?php
namespace App\controllers;

use Firebase\JWT\JWT;
use App\Services\DataService;
use Psr\Container\ContainerInterface;

// Responsabilidad única: generar y guardar los tokens de la sesión.
class Tokens {
    public function __construct(protected ContainerInterface $container) {}

    public function generateTokens(array $credenciales): array {
        $id = $credenciales['id'];
        $username = $credenciales['username'];
        $rol_id = $credenciales['rol_id'];
        $now = time();
        $config = $this->container->get('config');
        $payload = [
            'iss' => 'inventario-ventas-business',
            'iat' => $now,
            'exp' => $now + $config->jwt_ttl,
            'sub' => (string) $id,
            'username' => $username,
            'rol_id' => (int) $rol_id,
        ];
        $payloadRef = [
            'iss' => 'inventario-ventas-business',
            'iat' => $now,
            'sub' => (string) $id,
        ];
        return [
            'token' => JWT::encode($payload, $config->key, 'HS256'),
            'tkRef' => JWT::encode($payloadRef, $config->key, 'HS256'),
        ];
    }

    // Guarda el token de refresco en la capa de datos (vacío al cerrar sesión).
    protected function saveRefreshToken(string $id, string $tkRef) {
        $service = $this->container->get(DataService::class);
        return $service->put('auth/tkRef/' . $id, json_encode(
            ['tkRef' => $tkRef], JSON_THROW_ON_ERROR));
    }
}
