<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model {
    protected $table = 'ventas';
    public $timestamps = false;

    protected $fillable = ['cliente_id', 'usuario_id', 'fecha', 'total', 'estado'];

    protected $casts = [
        'id' => 'integer',
        'cliente_id' => 'integer',
        'usuario_id' => 'integer',
        'total' => 'float'
    ];

    public function detalles() {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function cliente() {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
