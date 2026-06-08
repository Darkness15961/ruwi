<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $cotizacions_id = $request->cotizacions_id;
        $perPage = $request->input('per_page', 10);
        $productos = Producto::
            with(['cotizacion', 'productoInsumos'])
            ->where('cotizacions_id', $cotizacions_id)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        
        return $productos;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'nombre' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:0',
            'punitario' => 'required|numeric|min:0',
            'igv' => 'required|numeric|min:0',
            'cotizacions_id' => 'required|exists:cotizacions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $producto = Producto::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Producto creado correctamente',
            'data' => $producto,
        ], 201);
    }

    public function show($id)
    {
        $producto = Producto::with(['cotizacion', 'productoInsumos'])->find($id);

        if (!$producto) {
            return response()->json([
                'status' => 0,
                'message' => 'Producto no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Producto obtenido correctamente',
            'data' => $producto,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'status' => 0,
                'message' => 'Producto no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|required|date',
            'nombre' => 'sometimes|required|string|max:255',
            'cantidad' => 'sometimes|required|numeric|min:0',
            'punitario' => 'sometimes|required|numeric|min:0',
            'igv' => 'sometimes|required|numeric|min:0',
            'cotizacions_id' => 'sometimes|required|exists:cotizacions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $producto->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Producto actualizado correctamente',
            'data' => $producto,
        ], 200);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'status' => 0,
                'message' => 'Producto no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $producto->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Producto eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
