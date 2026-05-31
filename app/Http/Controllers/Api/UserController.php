<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = User::paginate($perPage);
        
        return response()->json([
            'status' => 1,
            'message' => 'Usuarios obtenidos correctamente',
            'data' => $users,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:8',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json([
            'status' => 1,
            'message' => 'Usuario creado correctamente',
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Usuario no encontrado',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Usuario obtenido correctamente',
            'data' => $user,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Usuario no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'dni' => 'sometimes|required|string|max:8',
            'email' => 'sometimes|required|string|email|unique:users,email,'.$id,
            'password' => 'sometimes|required|string|min:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        $data = $validator->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

        return response()->json([
            'status' => 1,
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Usuario no encontrado',
                'data' => (object)[],
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Usuario eliminado correctamente',
            'data' => (object)[],
        ], 200);
    }
}
