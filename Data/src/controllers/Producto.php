<?php
declare(strict_types=1);

namespace App\controllers;

use App\Models\Producto as ProductoModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Producto extends BaseController {

    /** Consulta base: producto + nombre de categoría y proveedor. */
    private function consulta(): Builder {
        return ProductoModel::query()
            ->select([
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.categoria_id',
                'categorias.nombre as categoria',
                'productos.proveedor_id',
                'proveedores.nombre as proveedor',
                'productos.precio',
                'productos.stock',
                'productos.stock_minimo',
                'productos.activo'
            ])
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->leftJoin('proveedores', 'productos.proveedor_id', '=', 'proveedores.id');
    }

    public function read(Request $request, Response $response, array $args): Response {
        $this->db();

        if (isset($args['id'])) {
            $producto = $this->consulta()->find($args['id']);
            if ($producto === null) {
                return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
            }
            return $this->json($response, ['data' => $producto->toArray()]);
        }

        // La lista general solo muestra productos activos
        $productos = $this->consulta()
            ->where('productos.activo', true)
            ->orderBy('productos.nombre')
            ->get()
            ->toArray();
        return $this->json($response, ['data' => $productos]);
    }

    /**
     * GET /producto/filter/{offset}/{limit}?nombre=&codigo=&categoria_id=&proveedor_id=&activo=
     */
    public function filter(Request $request, Response $response, array $args): Response {
        $pag = $this->paginacion($args);
        if ($pag === null) {
            return $this->errorPaginacion($response);
        }
        [$offset, $limit] = $pag;

        $this->db();
        $params = $request->getQueryParams();
        $nombre = trim((string) ($params['nombre'] ?? ''));
        $codigo = trim((string) ($params['codigo'] ?? ''));
        $categoriaId = filter_var($params['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $proveedorId = filter_var($params['proveedor_id'] ?? null, FILTER_VALIDATE_INT);
        $activo = $params['activo'] ?? '';

        $query = $this->consulta();
        if ($nombre !== '') {
            $query->where('productos.nombre', 'like', "%{$nombre}%");
        }
        if ($codigo !== '') {
            $query->where('productos.codigo', 'like', "%{$codigo}%");
        }
        if ($categoriaId !== false && $categoriaId !== null) {
            $query->where('productos.categoria_id', $categoriaId);
        }
        if ($proveedorId !== false && $proveedorId !== null) {
            $query->where('productos.proveedor_id', $proveedorId);
        }
        if ($activo === '0' || $activo === '1') {
            $query->where('productos.activo', $activo === '1');
        }

        $total = (clone $query)->count('productos.id');
        $productos = $query->orderBy('productos.nombre')->offset($offset)->limit($limit)->get()->toArray();

        return $this->json($response, [
            'data' => $productos,
            'pagination' => ['offset' => $offset, 'limit' => $limit, 'total' => $total]
        ]);
    }

    /** GET /producto/stock-bajo  (usa sp_productos_stock_bajo) */
    public function stockBajo(Request $request, Response $response, array $args): Response {
        $productos = $this->db()->connection()->select('CALL sp_productos_stock_bajo()');
        return $this->json($response, ['data' => $productos]);
    }

    public function create(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $errors = [];

        $codigo = trim((string) ($data['codigo'] ?? ''));
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $categoriaId = filter_var($data['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $proveedorId = ($data['proveedor_id'] ?? null) === null
            ? null
            : filter_var($data['proveedor_id'], FILTER_VALIDATE_INT);
        $precio = filter_var($data['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($data['stock'] ?? 0, FILTER_VALIDATE_INT);
        $stockMinimo = filter_var($data['stock_minimo'] ?? 5, FILTER_VALIDATE_INT);

        if ($codigo === '' || mb_strlen($codigo) > 50) {
            $errors['codigo'] = 'Es obligatorio y debe tener máximo 50 caracteres.';
        }
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errors['nombre'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
        }
        if ($categoriaId === false || $categoriaId === null || $categoriaId < 1) {
            $errors['categoria_id'] = 'Debe ser un entero positivo.';
        }
        if ($proveedorId === false || ($proveedorId !== null && $proveedorId < 1)) {
            $errors['proveedor_id'] = 'Debe ser un entero positivo o null.';
        }
        if ($precio === false || $precio === null || $precio < 0) {
            $errors['precio'] = 'Es obligatorio y debe ser un número mayor o igual a 0.';
        }
        if ($stock === false || $stock < 0) {
            $errors['stock'] = 'Debe ser un entero mayor o igual a 0.';
        }
        if ($stockMinimo === false || $stockMinimo < 0) {
            $errors['stock_minimo'] = 'Debe ser un entero mayor o igual a 0.';
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $this->db();
        try {
            $producto = ProductoModel::create([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'categoria_id' => $categoriaId,
                'proveedor_id' => $proveedorId,
                'precio' => $precio,
                'stock' => $stock,
                'stock_minimo' => $stockMinimo,
                'activo' => true,
            ]);
        } catch (QueryException $e) {
            return $this->errorEscritura($response, $e);
        }

        return $this->json($response, ['data' => $this->consulta()->find($producto->id)->toArray()], 201);
    }

    /**
     * PUT /producto/{id}
     * El stock NO se cambia aquí: se usa /producto/{id}/entrada o las ventas.
     */
    public function update(Request $request, Response $response, array $args): Response {
        $this->db();
        $producto = ProductoModel::find($args['id']);
        if ($producto === null) {
            return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
        }

        $data = $this->body($request);
        $errors = [];

        if (isset($data['codigo'])) {
            $codigo = trim((string) $data['codigo']);
            if ($codigo === '' || mb_strlen($codigo) > 50) {
                $errors['codigo'] = 'Es obligatorio y debe tener máximo 50 caracteres.';
            }
            $producto->codigo = $codigo;
        }
        if (isset($data['nombre'])) {
            $nombre = trim((string) $data['nombre']);
            if ($nombre === '' || mb_strlen($nombre) > 150) {
                $errors['nombre'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
            }
            $producto->nombre = $nombre;
        }
        if (isset($data['categoria_id'])) {
            $categoriaId = filter_var($data['categoria_id'], FILTER_VALIDATE_INT);
            if ($categoriaId === false || $categoriaId < 1) {
                $errors['categoria_id'] = 'Debe ser un entero positivo.';
            }
            $producto->categoria_id = $categoriaId;
        }
        if (array_key_exists('proveedor_id', $data)) {
            $proveedorId = $data['proveedor_id'] === null ? null : filter_var($data['proveedor_id'], FILTER_VALIDATE_INT);
            if ($proveedorId === false || ($proveedorId !== null && $proveedorId < 1)) {
                $errors['proveedor_id'] = 'Debe ser un entero positivo o null.';
            }
            $producto->proveedor_id = $proveedorId;
        }
        if (isset($data['precio'])) {
            $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);
            if ($precio === false || $precio < 0) {
                $errors['precio'] = 'Debe ser un número mayor o igual a 0.';
            }
            $producto->precio = $precio;
        }
        if (isset($data['stock_minimo'])) {
            $stockMinimo = filter_var($data['stock_minimo'], FILTER_VALIDATE_INT);
            if ($stockMinimo === false || $stockMinimo < 0) {
                $errors['stock_minimo'] = 'Debe ser un entero mayor o igual a 0.';
            }
            $producto->stock_minimo = $stockMinimo;
        }
        if (isset($data['activo'])) {
            $activo = filter_var($data['activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo === null) {
                $errors['activo'] = 'Debe ser true o false.';
            }
            $producto->activo = (bool) $activo;
        }
        if (isset($data['stock'])) {
            $errors['stock'] = 'El stock no se modifica aquí. Use POST /producto/{id}/entrada.';
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $producto->save();
        } catch (QueryException $e) {
            return $this->errorEscritura($response, $e);
        }
        return $this->json($response, ['data' => $this->consulta()->find($producto->id)->toArray()]);
    }

    /** POST /producto/{id}/entrada  { "cantidad": 10 }  -> suma stock (compra / reposición) */
    public function entrada(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $cantidad = filter_var($data['cantidad'] ?? null, FILTER_VALIDATE_INT);
        if ($cantidad === false || $cantidad === null || $cantidad < 1) {
            return $this->json($response, ['errors' => ['cantidad' => 'Debe ser un entero mayor a 0.']], 422);
        }

        $this->db();
        // increment() hace "UPDATE ... SET stock = stock + ?" en una sola sentencia (seguro con concurrencia)
        $filas = ProductoModel::query()->whereKey($args['id'])->increment('stock', $cantidad);
        if ($filas === 0) {
            return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
        }

        return $this->json($response, ['data' => $this->consulta()->find($args['id'])->toArray()]);
    }

    /**
     * DELETE /producto/{id}
     * Borrado lógico (activo = 0), porque el producto puede estar en detalle_ventas.
     */
    public function delete(Request $request, Response $response, array $args): Response {
        $this->db();
        $producto = ProductoModel::find($args['id']);
        if ($producto === null) {
            return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
        }

        $producto->activo = false;
        $producto->save();
        return $this->json($response, ['message' => 'Producto desactivado exitosamente.']);
    }

    private function errorEscritura(Response $response, QueryException $e): Response {
        return match ($this->codigoMysql($e)) {
            1062 => $this->json($response, ['error' => 'El código de producto ya está registrado.'], 409),
            1452 => $this->json($response, ['error' => 'La categoría o el proveedor no existen.'], 422),
            default => throw $e,
        };
    }
}
