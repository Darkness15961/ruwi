<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmpresaUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpresaUsuarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $empresaUsuarios = EmpresaUsuario::with(['user', 'empresa'])->paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Asignaciones de empresa y usuario obtenidas correctamente',
            'data' => $empresaUsuarios,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cargo' => 'required|string|max:255',
            'users_id' => 'required|exists:users,id',
            'empresas_id' => 'required|exists:empresas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $empresaUsuario = EmpresaUsuario::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Asignación creada correctamente',
            'data' => $empresaUsuario,
        ], 201);
    }

    public function show($id)
    {
        $empresaUsuario = EmpresaUsuario::with(['user', 'empresa'])->find($id);

        if (!$empresaUsuario) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Asignación obtenida correctamente',
            'data' => $empresaUsuario,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $empresaUsuario = EmpresaUsuario::find($id);

        if (!$empresaUsuario) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'cargo' => 'sometimes|required|string|max:255',
            'users_id' => 'sometimes|required|exists:users,id',
            'empresas_id' => 'sometimes|required|exists:empresas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $empresaUsuario->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Asignación actualizada correctamente',
            'data' => $empresaUsuario,
        ], 200);
    }

    public function destroy($id)
    {
        $empresaUsuario = EmpresaUsuario::find($id);

        if (!$empresaUsuario) {
            return response()->json([
                'status' => 0,
                'message' => 'Asignación no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $empresaUsuario->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Asignación eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
