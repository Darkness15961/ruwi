<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $cuentas = Cuenta::paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Cuentas obtenidas correctamente',
            'data' => $cuentas,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'moneda' => 'required|string|max:255',
            'nro_cuenta' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $cuenta = Cuenta::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Cuenta creada correctamente',
            'data' => $cuenta,
        ], 201);
    }

    public function show($id)
    {
        $cuenta = Cuenta::with('empresa')->find($id);

        if (!$cuenta) {
            return response()->json([
                'status' => 0,
                'message' => 'Cuenta no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Cuenta obtenida correctamente',
            'data' => $cuenta,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $cuenta = Cuenta::find($id);

        if (!$cuenta) {
            return response()->json([
                'status' => 0,
                'message' => 'Cuenta no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'moneda' => 'sometimes|required|string|max:255',
            'nro_cuenta' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $cuenta->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Cuenta actualizada correctamente',
            'data' => $cuenta,
        ], 200);
    }

    public function destroy($id)
    {
        $cuenta = Cuenta::find($id);

        if (!$cuenta) {
            return response()->json([
                'status' => 0,
                'message' => 'Cuenta no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $cuenta->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Cuenta eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
