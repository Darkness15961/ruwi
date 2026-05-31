<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizacions';

    protected $fillable = [
        'fecha',
        'ruc',
        'descripcion',
        'detalle',
        'fecha_vencimiento',
        'condicion',
        'users_id',
        'foto_ref',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'cotizacions_id');
    }
}
