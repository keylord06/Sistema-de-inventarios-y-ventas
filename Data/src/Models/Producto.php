<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model {
    protected $table = 'productos';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'proveedor_id',
        'precio',
        'stock',
        'stock_minimo',
        'activo'
    ];

    protected $casts = [
        'id' => 'integer',
        'categoria_id' => 'integer',
        'proveedor_id' => 'integer',
        'precio' => 'float',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'activo' => 'boolean'
    ];

    public function categoria() {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function proveedor() {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
