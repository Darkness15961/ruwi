<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\EmpresaUsuario;
use App\Models\Menu;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    /**
     * Obtiene y estructura los menús permitidos para el usuario y empresa activos.
     */
    public function getMenu(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Usuario no autenticado',
                'data' => [],
            ], 401);
        }

        $empresaUsuarioId = $request->input('empresausuario_id') ?: $request->header('X-Empresa-Usuario-Id');
        $empresaUsuario = null;

        if ($empresaUsuarioId) {
            // Si nos envían el ID de la asignación directamente
            $empresaUsuario = EmpresaUsuario::where('id', $empresaUsuarioId)
                ->where('users_id', $user->id)
                ->first();
        } else {
            // Resolver usando el header X-Ruc
            $rucHeader = config('database.tenant.ruc_header', 'X-Ruc');
            $ruc = (string) $request->header($rucHeader, '');

            if ($ruc === '' && isset($user->ruc)) {
                $ruc = (string) $user->ruc;
            }

            if ($ruc !== '') {
                $empresa = Empresa::where('ruc', $ruc)->first();
                if ($empresa) {
                    $empresaUsuario = EmpresaUsuario::where('users_id', $user->id)
                        ->where('empresas_id', $empresa->id)
                        ->first();
                }
            }
        }

        // Si aún no se resolvió, buscar la primera asignación activa del usuario
        if (!$empresaUsuario) {
            $empresaUsuario = EmpresaUsuario::where('users_id', $user->id)->first();
        }

        if (!$empresaUsuario) {
            return response()->json([
                'status' => 0,
                'message' => 'No se encontró una asignación de empresa válida para este usuario.',
                'data' => [],
            ], 403);
        }

        // 1. Obtener los menús asignados al EmpresaUsuario que estén activos
        $assignedMenus = $empresaUsuario->menus()
            ->where('activo', 1)
            ->get();

        $menuMap = [];
        foreach ($assignedMenus as $menu) {
            $menuMap[$menu->id] = $menu;
        }

        // 2. Garantizar que si un submenú está asignado, su padre también esté en el set (auto-recuperación de padres)
        $missingParentIds = [];
        foreach ($menuMap as $menu) {
            if ($menu->menus_id !== null && !isset($menuMap[$menu->menus_id])) {
                $missingParentIds[] = $menu->menus_id;
            }
        }

        if (!empty($missingParentIds)) {
            $parents = Menu::whereIn('id', array_unique($missingParentIds))
                ->where('activo', 1)
                ->get();
            foreach ($parents as $parent) {
                $menuMap[$parent->id] = $parent;
            }
        }

        // 3. Construir la estructura jerárquica
        $navMain = [];
        foreach ($menuMap as $menu) {
            // Solo procesamos raíces (menús padre)
            if ($menu->menus_id === null) {
                $children = [];
                foreach ($menuMap as $child) {
                    if ($child->menus_id === $menu->id) {
                        $children[] = [
                            'title' => $child->title,
                            'url' => $child->url,
                            'icon' => $child->icon, // Ej. 'User', 'Lock'
                            'orden' => $child->orden,
                        ];
                    }
                }

                // Ordenar submenús
                usort($children, function ($a, $b) {
                    return $a['orden'] <=> $b['orden'];
                });

                $navMain[] = [
                    'title' => $menu->title,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'orden' => $menu->orden,
                    'items' => $children,
                ];
            }
        }

        // Ordenar menús principales
        usort($navMain, function ($a, $b) {
            return $a['orden'] <=> $b['orden'];
        });

        // Limpiar el campo temporal 'orden' antes de retornar el JSON si lo prefiere
        foreach ($navMain as &$m) {
            unset($m['orden']);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Estructura de menús obtenida correctamente',
            'data' => [
                'navMain' => $navMain
            ],
        ], 200);
    }
}
