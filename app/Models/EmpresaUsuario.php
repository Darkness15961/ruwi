<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaUsuario extends Model
{
    use HasFactory;

    protected $table = 'empresausuarios';

    protected $fillable = [
        'cargo',
        'users_id',
        'empresas_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresas_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menuusuario', 'empresausuario_id', 'menus_id')
            ->withTimestamps();
    }
}
