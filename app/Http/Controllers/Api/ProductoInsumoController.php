<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductoInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoInsumoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $productoInsumos = ProductoInsumo::with(['detalleIngreso', 'producto'])->paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Relación producto-insumo obtenida correctamente',
            'data' => $productoInsumos,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'detalleingresos_id' => 'required|exists:detalleingresos,id',
            'productos_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0',
            'destino' => 'required|string|max:255',
            'estado' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $productoInsumo = ProductoInsumo::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Relación producto-insumo creada correctamente',
            'data' => $productoInsumo,
        ], 201);
    }

    public function show($id)
    {
        $productoInsumo = ProductoInsumo::with(['detalleIngreso', 'producto'])->find($id);

        if (!$productoInsumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Relación producto-insumo no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Relación producto-insumo obtenida correctamente',
            'data' => $productoInsumo,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $productoInsumo = ProductoInsumo::find($id);

        if (!$productoInsumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Relación producto-insumo no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'detalleingresos_id' => 'sometimes|required|exists:detalleingresos,id',
            'productos_id' => 'sometimes|required|exists:productos,id',
            'cantidad' => 'sometimes|required|numeric|min:0',
            'destino' => 'sometimes|required|string|max:255',
            'estado' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $productoInsumo->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Relación producto-insumo actualizada correctamente',
            'data' => $productoInsumo,
        ], 200);
    }

    public function destroy($id)
    {
        $productoInsumo = ProductoInsumo::find($id);

        if (!$productoInsumo) {
            return response()->json([
                'status' => 0,
                'message' => 'Relación producto-insumo no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $productoInsumo->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Relación producto-insumo eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
