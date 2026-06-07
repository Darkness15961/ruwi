<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IngresoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $ingresos = Ingreso::
            where('detalle', 'like', '%' . $request->buscar . '%')
            ->orWhere('origen', 'like', '%' . $request->buscar . '%')
            ->paginate($perPage);
        
        return $ingresos;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'detalle' => 'required|string|max:255',
            'origen' => 'required|string|max:255',
            'ruc_factura' => 'required|string|max:255',
            'serie_factura' => 'required|string|max:255',
            'nro_factura' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $ingreso = Ingreso::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Ingreso creado correctamente',
            'data' => $ingreso,
        ], 201);
    }

    public function show($id)
    {
        $ingreso = Ingreso::find($id);

        if (!$ingreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Ingreso obtenido correctamente',
            'data' => $ingreso,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $ingreso = Ingreso::find($id);

        if (!$ingreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|required|date',
            'detalle' => 'sometimes|required|string|max:255',
            'origen' => 'sometimes|required|string|max:255',
            'ruc_factura' => 'sometimes|required|string|max:255',
            'serie_factura' => 'sometimes|required|string|max:255',
            'nro_factura' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $ingreso->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Ingreso actualizado correctamente',
            'data' => $ingreso,
        ], 200);
    }

    public function destroy($id)
    {
        $ingreso = Ingreso::find($id);

        if (!$ingreso) {
            return response()->json([
                'status' => 0,
                'message' => 'Ingreso no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $ingreso->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Ingreso eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
