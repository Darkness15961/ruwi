<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'umedida',
        'img_url',
        'categorias_id',
        'insumos_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categorias_id');
    }

    public function parentInsumo()
    {
        return $this->belongsTo(Insumo::class, 'insumos_id');
    }

    public function childInsumos()
    {
        return $this->hasMany(Insumo::class, 'insumos_id');
    }
}
