<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $cotizacions = Cotizacion::with(['productos'])
            ->where('ruc', 'like', '%' . $request->buscar . '%')
            ->orWhere('descripcion', 'like', '%' . $request->buscar . '%')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        
        return $cotizacions;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'ruc' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'detalle' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date',
            'condicion' => 'nullable|string',
            'users_id' => 'required',
            'foto_ref' => 'nullable|string|max:255',
            'estado' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $cotizacion = Cotizacion::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Cotización creada correctamente',
            'data' => $cotizacion,
        ], 201);
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with(['productos'])->find($id);

        if (!$cotizacion) {
            return response()->json([
                'status' => 0,
                'message' => 'Cotización no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Cotización obtenida correctamente',
            'data' => $cotizacion,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json([
                'status' => 0,
                'message' => 'Cotización no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|required|date',
            'ruc' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string|max:255',
            'detalle' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date',
            'condicion' => 'nullable|string',
            'users_id' => 'sometimes|required',
            'foto_ref' => 'nullable|string|max:255',
            'estado' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $cotizacion->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Cotización actualizada correctamente',
            'data' => $cotizacion,
        ], 200);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json([
                'status' => 0,
                'message' => 'Cotización no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $cotizacion->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Cotización eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
