<?php

namespace App\controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Services\DataService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use App\Validators\CategoriaValidator;

class Categoria
{

    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $path = isset($args['id']) ? 'categoria/' . rawurlencode($args['id']) : 'categoria';
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

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $body = (string) $request->getBody();

            $data = json_decode($body, true);

            if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
                return $this->json($response, ['errors' => ['body' => 'Debe enviar un objeto JSON válido']], 422);
            }

            $errors = (new CategoriaValidator())->validate($data);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->post('categoria', json_encode($data, JSON_THROW_ON_ERROR));

            return $this->json($response, json_decode((string) $upstream->getBody(), true),
                $upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $body = (string) $request->getBody();

            $data = json_decode($body, true);

            if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
                return $this->json($response, ['errors' => ['body' => 'Debe enviar un objeto JSON válido']], 422);
            }

            $errors = (new CategoriaValidator())->validate($data);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->put('categoria/' . rawurlencode($args['id']), json_encode($data, JSON_THROW_ON_ERROR));

            return $this->json($response, json_decode((string) $upstream->getBody(), true),
                $upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->delete('categoria/' . rawurlencode($args['id']));

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
