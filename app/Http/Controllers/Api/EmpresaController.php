<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $empresas = Empresa::with('cuentas')->paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Empresas obtenidas correctamente',
            'data' => $empresas,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ruc' => 'required|string|max:11',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'cci' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $empresa = Empresa::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Empresa creada correctamente',
            'data' => $empresa,
        ], 201);
    }

    public function show($id)
    {
        $empresa = Empresa::with('cuentas')->find($id);

        if (!$empresa) {
            return response()->json([
                'status' => 0,
                'message' => 'Empresa no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Empresa obtenida correctamente',
            'data' => $empresa,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json([
                'status' => 0,
                'message' => 'Empresa no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ruc' => 'sometimes|required|string|max:11',
            'razon_social' => 'sometimes|required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'cci' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $empresa->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Empresa actualizada correctamente',
            'data' => $empresa,
        ], 200);
    }

    public function destroy($id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json([
                'status' => 0,
                'message' => 'Empresa no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $empresa->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Empresa eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
