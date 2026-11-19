<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model {
    protected $table = 'detalle_ventas';
    public $timestamps = false;

    protected $fillable = ['venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal'];

    protected $casts = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'producto_id' => 'integer',
        'cantidad' => 'integer',
        'precio_unitario' => 'float',
        'subtotal' => 'float'
    ];

    public function producto() {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
