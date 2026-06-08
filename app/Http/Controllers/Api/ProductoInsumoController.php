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
        $productos_id = $request->productos_id;
        $perPage = $request->input('per_page', 10);
        $productoInsumos = ProductoInsumo::
            with(['detalleIngreso.insumo', 'producto'])
            ->where('productos_id', $productos_id)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        
        return $productoInsumos;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'detalleingresos_id' => 'required|exists:detalleingresos,id',
            'productos_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0'
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
        $productoInsumo = ProductoInsumo::with(['detalleIngreso.insumo', 'producto'])->find($id);

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
            'cantidad' => 'sometimes|required|numeric|min:0'
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
