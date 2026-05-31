<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';

    protected $fillable = [
        'nombre',
        'umedida',
        'categorias_id',
        'insumos_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categorias_id');
    }

    public function insumoPadre()
    {
        return $this->belongsTo(Insumo::class, 'insumos_id');
    }

    public function insumosHijos()
    {
        return $this->hasMany(Insumo::class, 'insumos_id');
    }
}
