<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleIngreso extends Model
{
    use HasFactory;

    protected $table = 'detalleingresos';

    protected $fillable = [
        'ingresos_id',
        'insumos_id',
        'cantidad',
        'punitario',
        'fecha_vencimiento',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumos_id');
    }
}
