<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';


    protected $fillable = [
        'fecha',
        'nombre',
        'cantidad',
        'punitario',
        'igv',
        'cotizacions_id',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacions_id');
    }

    public function productoInsumos()
    {
        return $this->hasMany(ProductoInsumo::class, 'productos_id');
    }
}
