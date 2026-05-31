<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleIngreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetalleIngresoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $detalleIngresos = DetalleIngreso::with(['ingreso', 'insumo'])->paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Detalle de ingresos obtenidos correctamente',
            'data' => $detalleIngresos,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingresos_id' => 'required|exists:ingresos,id',
            'insumos_id' => 'required|exists:insumos,id',
            'cantidad' => 'required|numeric|min:0',
            'punitario' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $detalleIngreso = DetalleIngreso::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Detalle de ingreso creado correctamente',
            'data' => $detalleIngreso,
        ], 201);
    }

    public function show($id)
    {
        $detalleIngreso = DetalleIngreso::with(['ingreso', 'insumo'])->find($id);

        if (!$detalleIngreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Detalle de ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Detalle de ingreso obtenido correctamente',
            'data' => $detalleIngreso,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $detalleIngreso = DetalleIngreso::find($id);

        if (!$detalleIngreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Detalle de ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ingresos_id' => 'sometimes|required|exists:ingresos,id',
            'insumos_id' => 'sometimes|required|exists:insumos,id',
            'cantidad' => 'sometimes|required|numeric|min:0',
            'punitario' => 'sometimes|required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $detalleIngreso->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Detalle de ingreso actualizado correctamente',
            'data' => $detalleIngreso,
        ], 200);
    }

    public function destroy($id)
    {
        $detalleIngreso = DetalleIngreso::find($id);

        if (!$detalleIngreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Detalle de ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $detalleIngreso->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Detalle de ingreso eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
