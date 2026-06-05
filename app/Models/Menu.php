<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'title',
        'url',
        'icon',
        'menus_id',
        'orden',
        'activo',
    ];

    /**
     * Relación con el menú padre.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'menus_id');
    }

    /**
     * Relación con los submenús (menús hijos).
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'menus_id')->orderBy('orden');
    }

    /**
     * Relación con los usuarios de empresa asignados a este menú.
     */
    public function empresaUsuarios()
    {
        return $this->belongsToMany(EmpresaUsuario::class, 'menuusuario', 'menus_id', 'empresausuario_id')
            ->withTimestamps();
    }
}
