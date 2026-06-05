<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuUsuario extends Model
{
    use HasFactory;

    protected $table = 'menuusuario';

    protected $fillable = [
        'menus_id',
        'empresausuario_id',
    ];

    /**
     * Relación con el menú.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menus_id');
    }

    /**
     * Relación con la asignación empresa-usuario.
     */
    public function empresaUsuario()
    {
        return $this->belongsTo(EmpresaUsuario::class, 'empresausuario_id');
    }
}
