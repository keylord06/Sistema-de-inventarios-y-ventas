<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model {
    protected $table = 'categorias';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    protected $casts = [
        'id' => 'integer'
    ];

    public function productos() {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
