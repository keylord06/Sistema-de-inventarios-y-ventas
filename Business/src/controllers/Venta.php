<?php

namespace App\controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Services\DataService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use App\Validators\VentaValidator;

class Venta
{

    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $path = isset($args['id']) ? 'venta/' . rawurlencode($args['id']) : 'venta';
            $upstream = $dataService->get($path);
            $response->getBody()->write((string) $upstream->getBody());
            return $response
                ->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function filter(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $path = 'venta/filter/' . rawurlencode($args['offset']) . '/' . rawurlencode($args['limit']);
            $query = $request->getQueryParams();

            $upstream = $dataService->filter($path, $query);

            $response->getBody()->write((string) $upstream->getBody());
            return $response
                ->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function readCliente(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->get('venta/cliente/' . rawurlencode($args['id']));
            $response->getBody()->write((string) $upstream->getBody());
            return $response
                ->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $body = (string) $request->getBody();

            $data = json_decode($body, true);

            if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
                return $this->json($response, ['errors' => ['body' => 'Debe enviar un objeto JSON válido']], 422);
            }

            // El usuario_id se toma del JWT, nunca de lo que mande el cliente.
            $token = $request->getAttribute('token');
            $data['usuario_id'] = isset($token['sub']) ? (int) $token['sub'] : null;

            $errors = (new VentaValidator())->validate($data);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->post('venta', json_encode($data, JSON_THROW_ON_ERROR));

            return $this->json($response, json_decode((string) $upstream->getBody(), true),
                $upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function anular(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->put('venta/' . rawurlencode($args['id']) . '/anular', '{}');

            return $this->json($response, json_decode((string) $upstream->getBody(), true),
                $upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

}
