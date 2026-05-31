<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresas_id',
        'nombre',
        'moneda',
        'nro_cuenta',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresas_id');
    }
}
