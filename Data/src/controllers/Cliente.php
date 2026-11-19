<?php
declare(strict_types=1);

namespace App\controllers;

use App\Models\Cliente as ClienteModel;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Cliente extends BaseController {

    private const CAMPOS = ['id', 'cedula', 'nombre_completo', 'telefono', 'email'];

    public function read(Request $request, Response $response, array $args): Response {
        $this->db();

        if (isset($args['id'])) {
            $cliente = ClienteModel::query()->select(self::CAMPOS)->find($args['id']);
            if ($cliente === null) {
                return $this->json($response, ['error' => 'Cliente no encontrado.'], 404);
            }
            return $this->json($response, ['data' => $cliente->toArray()]);
        }

        $clientes = ClienteModel::query()->select(self::CAMPOS)->orderBy('nombre_completo')->get()->toArray();
        return $this->json($response, ['data' => $clientes]);
    }

    public function filter(Request $request, Response $response, array $args): Response {
        $pag = $this->paginacion($args);
        if ($pag === null) {
            return $this->errorPaginacion($response);
        }
        [$offset, $limit] = $pag;

        $this->db();
        $params = $request->getQueryParams();
        $nombre = trim((string) ($params['nombre'] ?? $params['nombre_completo'] ?? ''));
        $cedula = trim((string) ($params['cedula'] ?? ''));

        $query = ClienteModel::query()->select(self::CAMPOS);
        if ($nombre !== '') {
            $query->where('nombre_completo', 'like', "%{$nombre}%");
        }
        if ($cedula !== '') {
            $query->where('cedula', 'like', "%{$cedula}%");
        }

        $total = (clone $query)->count('id');
        $clientes = $query->orderBy('nombre_completo')->offset($offset)->limit($limit)->get()->toArray();

        return $this->json($response, [
            'data' => $clientes,
            'pagination' => ['offset' => $offset, 'limit' => $limit, 'total' => $total]
        ]);
    }

    public function create(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $errors = [];

        $cedula = trim((string) ($data['cedula'] ?? ''));
        $nombre = trim((string) ($data['nombre_completo'] ?? ''));
        if ($cedula === '' || mb_strlen($cedula) > 20) {
            $errors['cedula'] = 'Es obligatoria y debe tener máximo 20 caracteres.';
        }
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errors['nombre_completo'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
        }
        $telefono = $this->textoOpcional($data, 'telefono', 20, $errors);
        $email = $this->textoOpcional($data, 'email', 100, $errors);

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $this->db();
        try {
            $cliente = ClienteModel::create([
                'cedula' => $cedula,
                'nombre_completo' => $nombre,
                'telefono' => $telefono,
                'email' => $email,
            ]);
        } catch (QueryException $e) {
            if ($this->codigoMysql($e) === 1062) {
                return $this->json($response, ['error' => 'La cédula ya está registrada.'], 409);
            }
            throw $e;
        }
        return $this->json($response, ['data' => $cliente->toArray()], 201);
    }

    public function update(Request $request, Response $response, array $args): Response {
        $this->db();
        $cliente = ClienteModel::find($args['id']);
        if ($cliente === null) {
            return $this->json($response, ['error' => 'Cliente no encontrado.'], 404);
        }

        $data = $this->body($request);
        $errors = [];

        if (isset($data['cedula'])) {
            $cedula = trim((string) $data['cedula']);
            if ($cedula === '' || mb_strlen($cedula) > 20) {
                $errors['cedula'] = 'Es obligatoria y debe tener máximo 20 caracteres.';
            }
            $cliente->cedula = $cedula;
        }
        if (isset($data['nombre_completo'])) {
            $nombre = trim((string) $data['nombre_completo']);
            if ($nombre === '' || mb_strlen($nombre) > 150) {
                $errors['nombre_completo'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
            }
            $cliente->nombre_completo = $nombre;
        }
        if (array_key_exists('telefono', $data)) {
            $cliente->telefono = $this->textoOpcional($data, 'telefono', 20, $errors);
        }
        if (array_key_exists('email', $data)) {
            $cliente->email = $this->textoOpcional($data, 'email', 100, $errors);
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $cliente->save();
        } catch (QueryException $e) {
            if ($this->codigoMysql($e) === 1062) {
                return $this->json($response, ['error' => 'La cédula ya está registrada.'], 409);
            }
            throw $e;
        }
        return $this->json($response, ['data' => $cliente->toArray()]);
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $this->db();
        $cliente = ClienteModel::find($args['id']);
        if ($cliente === null) {
            return $this->json($response, ['error' => 'Cliente no encontrado.'], 404);
        }

        if ($cliente->ventas()->exists()) {
            return $this->json($response, ['error' => 'No se puede eliminar un cliente que tiene ventas registradas.'], 409);
        }

        $cliente->delete();
        return $this->json($response, ['message' => 'Cliente eliminado exitosamente.']);
    }
}
