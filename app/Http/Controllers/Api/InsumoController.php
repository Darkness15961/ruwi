<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $insumos = Insumo::with(['categoria', 'parentInsumo'])->paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Insumos obtenidos correctamente',
            'data' => $insumos,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'umedida' => 'required|string|max:255',
            'categorias_id' => 'required|exists:categorias,id',
            'insumos_id' => 'nullable|exists:insumos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $insumo = Insumo::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Insumo creado correctamente',
            'data' => $insumo,
        ], 201);
    }

    public function show($id)
    {
        $insumo = Insumo::with(['categoria', 'parentInsumo', 'childInsumos'])->find($id);

        if (!$insumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Insumo no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Insumo obtenido correctamente',
            'data' => $insumo,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::find($id);

        if (!$insumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Insumo no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'umedida' => 'sometimes|required|string|max:255',
            'categorias_id' => 'sometimes|required|exists:categorias,id',
            'insumos_id' => 'nullable|exists:insumos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $insumo->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Insumo actualizado correctamente',
            'data' => $insumo,
        ], 200);
    }

    public function destroy($id)
    {
        $insumo = Insumo::find($id);

        if (!$insumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Insumo no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $insumo->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Insumo eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
