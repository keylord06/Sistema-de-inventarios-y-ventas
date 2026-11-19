<?php
declare(strict_types=1);

namespace App\controllers;

use App\Models\Venta as VentaModel;
use App\Models\Producto as ProductoModel;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Venta extends BaseController {

    private const CAMPOS = [
        'ventas.id',
        'ventas.fecha',
        'ventas.total',
        'ventas.estado',
        'ventas.cliente_id',
        'clientes.nombre_completo as cliente',
        'ventas.usuario_id',
        'usuarios.username as vendedor'
    ];

    private function consulta() {
        return VentaModel::query()
            ->select(self::CAMPOS)
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id');
    }

    /** GET /venta  ó  GET /venta/{id} (con su detalle) */
    public function read(Request $request, Response $response, array $args): Response {
        $this->db();

        if (isset($args['id'])) {
            $venta = $this->consulta()->find($args['id']);
            if ($venta === null) {
                return $this->json($response, ['error' => 'Venta no encontrada.'], 404);
            }

            $detalle = $venta->detalles()
                ->select([
                    'detalle_ventas.id',
                    'detalle_ventas.producto_id',
                    'productos.codigo',
                    'productos.nombre as producto',
                    'detalle_ventas.cantidad',
                    'detalle_ventas.precio_unitario',
                    'detalle_ventas.subtotal'
                ])
                ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                ->get()
                ->toArray();

            return $this->json($response, ['data' => $venta->toArray() + ['detalle' => $detalle]]);
        }

        $ventas = $this->consulta()->orderByDesc('ventas.fecha')->get()->toArray();
        return $this->json($response, ['data' => $ventas]);
    }

    /**
     * GET /venta/filter/{offset}/{limit}?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&estado=&cliente_id=&usuario_id=
     */
    public function filter(Request $request, Response $response, array $args): Response {
        $pag = $this->paginacion($args);
        if ($pag === null) {
            return $this->errorPaginacion($response);
        }
        [$offset, $limit] = $pag;

        $this->db();
        $params = $request->getQueryParams();
        $desde = trim((string) ($params['desde'] ?? ''));
        $hasta = trim((string) ($params['hasta'] ?? ''));
        $estado = trim((string) ($params['estado'] ?? ''));
        $clienteId = filter_var($params['cliente_id'] ?? null, FILTER_VALIDATE_INT);
        $usuarioId = filter_var($params['usuario_id'] ?? null, FILTER_VALIDATE_INT);

        $query = $this->consulta();
        if ($desde !== '') {
            $query->whereDate('ventas.fecha', '>=', $desde);
        }
        if ($hasta !== '') {
            $query->whereDate('ventas.fecha', '<=', $hasta);
        }
        if ($estado === 'Completada' || $estado === 'Anulada') {
            $query->where('ventas.estado', $estado);
        }
        if ($clienteId !== false && $clienteId !== null) {
            $query->where('ventas.cliente_id', $clienteId);
        }
        if ($usuarioId !== false && $usuarioId !== null) {
            $query->where('ventas.usuario_id', $usuarioId);
        }

        $total = (clone $query)->count('ventas.id');
        $ventas = $query->orderByDesc('ventas.fecha')->offset($offset)->limit($limit)->get()->toArray();

        return $this->json($response, [
            'data' => $ventas,
            'pagination' => ['offset' => $offset, 'limit' => $limit, 'total' => $total]
        ]);
    }

    /** GET /venta/cliente/{id}  (usa sp_obtener_ventas_cliente) */
    public function readCliente(Request $request, Response $response, array $args): Response {
        $clienteId = filter_var($args['id'] ?? null, FILTER_VALIDATE_INT);
        if ($clienteId === false || $clienteId < 1) {
            return $this->json($response, ['error' => 'cliente_id debe ser un entero positivo.'], 422);
        }

        $ventas = $this->db()->connection()->select('CALL sp_obtener_ventas_cliente(?)', [$clienteId]);
        return $this->json($response, ['data' => $ventas]);
    }

    /**
     * POST /venta
     * {
     *   "cliente_id": 1,          (opcional, null = cliente de contado)
     *   "usuario_id": 1,
     *   "items": [ { "producto_id": 1, "cantidad": 2 }, ... ]
     * }
     * Todo va en UNA transacción: si un producto falla (sin stock, no existe)
     * se deshace la venta completa.
     */
    public function create(Request $request, Response $response, array $args): Response {
        $data = $this->body($request);
        $errors = [];

        $clienteId = ($data['cliente_id'] ?? null) === null ? null : filter_var($data['cliente_id'], FILTER_VALIDATE_INT);
        $usuarioId = filter_var($data['usuario_id'] ?? null, FILTER_VALIDATE_INT);
        $items = $data['items'] ?? null;

        if ($clienteId === false || ($clienteId !== null && $clienteId < 1)) {
            $errors['cliente_id'] = 'Debe ser un entero positivo o null.';
        }
        if ($usuarioId === false || $usuarioId === null || $usuarioId < 1) {
            $errors['usuario_id'] = 'Debe ser un entero positivo.';
        }
        if (!is_array($items) || $items === []) {
            $errors['items'] = 'Debe enviar al menos un producto.';
        } else {
            foreach ($items as $i => $item) {
                $pid = filter_var($item['producto_id'] ?? null, FILTER_VALIDATE_INT);
                $cant = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);
                if ($pid === false || $pid === null || $pid < 1 || $cant === false || $cant === null || $cant < 1) {
                    $errors["items.{$i}"] = 'producto_id y cantidad deben ser enteros positivos.';
                }
            }
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $db = $this->db();
        $conn = $db->connection();

        try {
            $ventaId = $conn->transaction(function () use ($conn, $clienteId, $usuarioId, $items): int {
                $conn->statement('CALL sp_crear_venta(?, ?, @venta_id)', [$clienteId, $usuarioId]);
                $ventaId = (int) $conn->selectOne('SELECT @venta_id AS id')->id;

                foreach ($items as $item) {
                    $productoId = (int) $item['producto_id'];
                    $conn->statement(
                        'CALL sp_agregar_producto_venta(?, ?, ?, @resultado)',
                        [$ventaId, $productoId, (int) $item['cantidad']]
                    );
                    $mensaje = (string) $conn->selectOne('SELECT @resultado AS resultado')->resultado;

                    if (str_starts_with($mensaje, 'Error:')) {
                        // Lanzar una excepción hace rollback de toda la venta
                        throw new \DomainException("{$mensaje} (producto_id {$productoId})");
                    }
                }
                return $ventaId;
            });
        } catch (\DomainException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 409);
        } catch (QueryException $e) {
            if ($this->codigoMysql($e) === 1452) {
                return $this->json($response, ['error' => 'El cliente o el usuario no existen.'], 422);
            }
            throw $e;
        }

        // Devuelve la venta recién creada con su detalle
        return $this->read($request, $response, ['id' => $ventaId])->withStatus(201);
    }

    /**
     * PUT /venta/{id}/anular
     * Cambia el estado a 'Anulada' y devuelve el stock de cada producto.
     */
    public function anular(Request $request, Response $response, array $args): Response {
        $db = $this->db();
        $conn = $db->connection();

        try {
            $conn->transaction(function () use ($args): void {
                // lockForUpdate evita que dos peticiones anulen la misma venta a la vez
                $venta = VentaModel::query()->lockForUpdate()->find($args['id']);
                if ($venta === null) {
                    throw new \OutOfBoundsException('Venta no encontrada.');
                }
                if ($venta->estado === 'Anulada') {
                    throw new \DomainException('La venta ya está anulada.');
                }

                foreach ($venta->detalles as $detalle) {
                    ProductoModel::query()->whereKey($detalle->producto_id)->increment('stock', $detalle->cantidad);
                }

                $venta->estado = 'Anulada';
                $venta->save();
            });
        } catch (\OutOfBoundsException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (\DomainException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 409);
        }

        return $this->json($response, ['message' => 'Venta anulada y stock devuelto.']);
    }
}
