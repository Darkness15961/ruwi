<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'cci',
    ];

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'empresas_id');
    }
}
