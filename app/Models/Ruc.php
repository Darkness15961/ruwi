<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruc extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'telefono',
        'email',
        'cci',
        'estado',
    ];
}
