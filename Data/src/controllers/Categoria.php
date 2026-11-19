<?php
declare(strict_types=1);

namespace App\controllers;

use App\Models\Categoria as CategoriaModel;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Categoria extends BaseController {

    public function read(Request $request, Response $response, array $args): Response {
        $this->db();

        if (isset($args['id'])) {
            $categoria = CategoriaModel::query()->withCount('productos')->find($args['id']);
            if ($categoria === null) {
                return $this->json($response, ['error' => 'Categoría no encontrada.'], 404);
            }
            return $this->json($response, ['data' => $categoria->toArray()]);
        }

        $categorias = CategoriaModel::query()
            ->withCount('productos')
            ->orderBy('nombre')
            ->get()
            ->toArray();
        return $this->json($response, ['data' => $categorias]);
    }

    public function create(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $nombre = trim((string) ($data['nombre'] ?? ''));

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return $this->json($response, ['errors' => ['nombre' => 'Es obligatorio y debe tener máximo 100 caracteres.']], 422);
        }

        $this->db();
        try {
            $categoria = CategoriaModel::create(['nombre' => $nombre]);
        } catch (QueryException $e) {
            if ($this->codigoMysql($e) === 1062) {
                return $this->json($response, ['error' => 'La categoría ya existe.'], 409);
            }
            throw $e;
        }
        return $this->json($response, ['data' => $categoria->toArray()], 201);
    }

    public function update(Request $request, Response $response, array $args): Response {
        $this->db();
        $categoria = CategoriaModel::find($args['id']);
        if ($categoria === null) {
            return $this->json($response, ['error' => 'Categoría no encontrada.'], 404);
        }

        $data = $this->body($request);
        if (isset($data['nombre'])) {
            $nombre = trim((string) $data['nombre']);
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                return $this->json($response, ['errors' => ['nombre' => 'Es obligatorio y debe tener máximo 100 caracteres.']], 422);
            }
            $categoria->nombre = $nombre;
        }

        try {
            $categoria->save();
        } catch (QueryException $e) {
            if ($this->codigoMysql($e) === 1062) {
                return $this->json($response, ['error' => 'La categoría ya existe.'], 409);
            }
            throw $e;
        }
        return $this->json($response, ['data' => $categoria->toArray()]);
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $this->db();
        $categoria = CategoriaModel::find($args['id']);
        if ($categoria === null) {
            return $this->json($response, ['error' => 'Categoría no encontrada.'], 404);
        }

        if ($categoria->productos()->exists()) {
            return $this->json($response, ['error' => 'No se puede eliminar una categoría que tiene productos.'], 409);
        }

        $categoria->delete();
        return $this->json($response, ['message' => 'Categoría eliminada exitosamente.']);
    }
}
