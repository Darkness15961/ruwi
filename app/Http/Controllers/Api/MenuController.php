<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $menus = Menu::with('parent')->paginate($perPage);

        return response()->json([
            'status' => 1,
            'message' => 'Menús obtenidos correctamente',
            'data' => $menus,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'url' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'menus_id' => 'nullable|exists:menus,id',
            'orden' => 'sometimes|integer',
            'activo' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $menu = Menu::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Menú creado correctamente',
            'data' => $menu,
        ], 201);
    }

    public function show($id)
    {
        $menu = Menu::with(['parent', 'children'])->find($id);

        if (!$menu) {
            return response()->json([
                'status' => 0,
                'message' => 'Menú no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Menú obtenido correctamente',
            'data' => $menu,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => 0,
                'message' => 'Menú no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:50',
            'url' => 'sometimes|required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'menus_id' => 'nullable|exists:menus,id',
            'orden' => 'sometimes|integer',
            'activo' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $menu->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Menú actualizado correctamente',
            'data' => $menu,
        ], 200);
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => 0,
                'message' => 'Menú no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $menu->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Menú eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
