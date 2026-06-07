<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $categorias = Categoria::where('nombre', 'like', '%' . $request->buscar . '%')
            ->paginate($perPage);
        
        return $categorias;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $categoria = Categoria::create($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Categoría creada correctamente',
            'data' => $categoria,
        ], 201);
    }

    public function show($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'status' => 0,
                'message' => 'Categoría no encontrada',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Categoría obtenida correctamente',
            'data' => $categoria,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'status' => 0,
                'message' => 'Categoría no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $categoria->update($validator->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Categoría actualizada correctamente',
            'data' => $categoria,
        ], 200);
    }

    public function destroy($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'status' => 0,
                'message' => 'Categoría no encontrada',
                'data' => (object)[],
            ], 404);
        }

        $categoria->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Categoría eliminada correctamente',
            'data' => (object)[],
        ], 200);
    }
}
