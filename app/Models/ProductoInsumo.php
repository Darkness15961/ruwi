<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoInsumo extends Model
{
    use HasFactory;

    protected $table = 'productoinsumos';

    protected $fillable = [
        'detalleingresos_id',
        'productos_id',
        'cantidad',
        'destino',
        'estado',
    ];
    public function detalleIngreso()
    {
        return $this->belongsTo(DetalleIngreso::class, 'detalleingresos_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

}
