<?php
declare(strict_types=1);

namespace App\controllers;

use App\Models\Proveedor as ProveedorModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Proveedor extends BaseController {

    private const CAMPOS = ['id', 'nombre', 'telefono', 'email'];

    public function read(Request $request, Response $response, array $args): Response {
        $this->db();

        if (isset($args['id'])) {
            $proveedor = ProveedorModel::query()->select(self::CAMPOS)->find($args['id']);
            if ($proveedor === null) {
                return $this->json($response, ['error' => 'Proveedor no encontrado.'], 404);
            }
            return $this->json($response, ['data' => $proveedor->toArray()]);
        }

        $proveedores = ProveedorModel::query()->select(self::CAMPOS)->orderBy('nombre')->get()->toArray();
        return $this->json($response, ['data' => $proveedores]);
    }

    public function filter(Request $request, Response $response, array $args): Response {
        $pag = $this->paginacion($args);
        if ($pag === null) {
            return $this->errorPaginacion($response);
        }
        [$offset, $limit] = $pag;

        $this->db();
        $params = $request->getQueryParams();
        $nombre = trim((string) ($params['nombre'] ?? ''));

        $query = ProveedorModel::query()->select(self::CAMPOS);
        if ($nombre !== '') {
            $query->where('nombre', 'like', "%{$nombre}%");
        }

        $total = (clone $query)->count('id');
        $proveedores = $query->orderBy('nombre')->offset($offset)->limit($limit)->get()->toArray();

        return $this->json($response, [
            'data' => $proveedores,
            'pagination' => ['offset' => $offset, 'limit' => $limit, 'total' => $total]
        ]);
    }

    public function create(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $errors = [];

        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errors['nombre'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
        }
        $telefono = $this->textoOpcional($data, 'telefono', 20, $errors);
        $email = $this->textoOpcional($data, 'email', 100, $errors);

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $this->db();
        $proveedor = ProveedorModel::create([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $email,
        ]);
        return $this->json($response, ['data' => $proveedor->toArray()], 201);
    }

    public function update(Request $request, Response $response, array $args): Response {
        $this->db();
        $proveedor = ProveedorModel::find($args['id']);
        if ($proveedor === null) {
            return $this->json($response, ['error' => 'Proveedor no encontrado.'], 404);
        }

        $data = $this->body($request);
        $errors = [];

        if (isset($data['nombre'])) {
            $nombre = trim((string) $data['nombre']);
            if ($nombre === '' || mb_strlen($nombre) > 150) {
                $errors['nombre'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
            }
            $proveedor->nombre = $nombre;
        }
        if (array_key_exists('telefono', $data)) {
            $proveedor->telefono = $this->textoOpcional($data, 'telefono', 20, $errors);
        }
        if (array_key_exists('email', $data)) {
            $proveedor->email = $this->textoOpcional($data, 'email', 100, $errors);
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $proveedor->save();
        return $this->json($response, ['data' => $proveedor->toArray()]);
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $this->db();
        $proveedor = ProveedorModel::find($args['id']);
        if ($proveedor === null) {
            return $this->json($response, ['error' => 'Proveedor no encontrado.'], 404);
        }

        if ($proveedor->productos()->exists()) {
            return $this->json($response, ['error' => 'No se puede eliminar un proveedor que tiene productos asociados.'], 409);
        }

        $proveedor->delete();
        return $this->json($response, ['message' => 'Proveedor eliminado exitosamente.']);
    }
}
