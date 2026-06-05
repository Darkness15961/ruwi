<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MenuUsuarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $assignments = MenuUsuario::with(['menu', 'empresaUsuario.user', 'empresaUsuario.empresa'])->paginate($perPage);

        return response()->json([
            'status' => 1,
            'message' => 'Asignaciones de menús a usuarios obtenidas correctamente',
            'data' => $assignments,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menus_id' => [
                'required',
                'exists:menus,id',
                Rule::unique('menuusuario', 'menus_id')->where(function ($query) use ($request) {
                    return $query->where('empresausuario_id', $request->empresausuario_id);
                })
            ],
            'empresausuario_id' => 'required|exists:empresausuarios,id',
        ], [
            'menus_id.unique' => 'Este menú ya está asignado a este usuario de empresa.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $assignment = MenuUsuario::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Menú asignado correctamente al usuario',
            'data' => $assignment,
        ], 201);
    }

    public function show($id)
    {
        $assignment = MenuUsuario::with(['menu', 'empresaUsuario.user', 'empresaUsuario.empresa'])->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Asignación obtenida correctamente',
            'data' => $assignment,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $assignment = MenuUsuario::find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'menus_id' => [
                'sometimes',
                'required',
                'exists:menus,id',
                Rule::unique('menuusuario', 'menus_id')->where(function ($query) use ($request, $assignment) {
                    $empresausuarioId = $request->input('empresausuario_id', $assignment->empresausuario_id);
                    return $query->where('empresausuario_id', $empresausuarioId)->where('id', '<>', $assignment->id);
                })
            ],
            'empresausuario_id' => 'sometimes|required|exists:empresausuarios,id',
        ], [
            'menus_id.unique' => 'Este menú ya está asignado a este usuario de empresa.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $assignment->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Asignación actualizada correctamente',
            'data' => $assignment,
        ], 200);
    }

    public function destroy($id)
    {
        $assignment = MenuUsuario::find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Asignación eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
