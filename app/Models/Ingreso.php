<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'detalle',
        'origen',
        'ruc_factura',
        'serie_factura',
        'nro_factura',
    ];

    public function detalleIngresos()
    {
        return $this->hasMany(DetalleIngreso::class, 'ingresos_id');
    }
}
