<?php

declare(strict_types=1);

namespace App\controllers;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Services\DataService;

// Auth hereda de Tokens para delegarle la generación y guardado de tokens.
final class Auth extends Tokens
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function login(Request $request, Response $response): Response
    {
        $request->getBody()->rewind();
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($username === '' || $password === '') {
            return $this->json($response, [
                'error' => 'El usuario y la contraseña son obligatorios.'
            ], 422);
        }

        try {
            $service = $this->container->get(DataService::class);
            $upstream = $service->post('auth/credentials', json_encode([
                'username' => $username,
                'password' => $password
            ], JSON_THROW_ON_ERROR));

            $credentials = json_decode((string) $upstream->getBody(), true);

            if ($upstream->getStatusCode() !== 200 || !is_array($credentials)) {
                return $this->json($response, [
                    'error' => 'Credenciales inválidas.'
                ], 401);
            }

            $tokens = $this->generateTokens($credentials);

            // guardar el token de refresco
            $this->saveRefreshToken((string) $credentials['id'], $tokens['tkRef']);

            return $this->json($response, $tokens, 200);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible.'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos.'], 502);
        }
    }

    public function loggout(Request $request, Response $response, array $args): Response
    {
        try {
            $this->saveRefreshToken((string) $args['id'], '');
            return $this->json($response, ['message' => 'Sesión cerrada correctamente.'], 200);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible.'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos.'], 502);
        }
    }

    // NOTA: falta implementar refresh-session. El Auth.php de Datos todavia no
    // expone un endpoint para leer/verificar el tkRef guardado de un usuario,
    // asi que no hay con que comparar el token que mande el cliente.
    // Pendiente: pedirle a Keylord un GET /auth/tkRef/{id} (o similar) en Datos.

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
}
