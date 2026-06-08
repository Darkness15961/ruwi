<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleIngreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class DetalleIngresoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $detalleIngresos = DetalleIngreso::
            where('ingresos_id', $request->ingresos_id)
            ->with(['ingreso', 'insumo'])->paginate($perPage);
        
        return $detalleIngresos;
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

    public function saldoDetalleIngreso(Request $request)
    {
        $buscar = $request->buscar;

        $detalleIngresos = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub(
                DB::table('cotizacions as c')
                    ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
                    ->join('productoinsumos as pi', 'pi.productos_id', '=', 'p.id')
                    ->where('c.estado', 1)
                    ->groupBy('pi.detalleingresos_id')
                    ->select(
                        'pi.detalleingresos_id',
                        DB::raw('SUM(pi.cantidad) as cantidad_usada')
                    ),
                'u',
                function ($join) {
                    $join->on('u.detalleingresos_id', '=', 'di.id');
                }
            )
            ->select(
                'di.id',
                'i.nombre',
                'i.img_url',
                'i.umedida',
                DB::raw('di.cantidad as cantidad_ingresada'),
                DB::raw('COALESCE(u.cantidad_usada, 0) as cantidad_usada'),
                DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'),
                'di.punitario',
                DB::raw('(di.cantidad * di.punitario) as total')
            )
            ->when($buscar, function ($query) use ($buscar) {
                $query->where('i.nombre', 'like', "%{$buscar}%");
            })
            ->orderBy('i.nombre')
            ->paginate($request->per_page ?? 15);

        return $detalleIngresos;
    }

    public function saldoRealDetalleIngreso(Request $request){
        $buscar = $request->buscar;

        $detalleIngresos = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub(
                DB::table('cotizacions as c')
                    ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
                    ->join('productoinsumos as pi', 'pi.productos_id', '=', 'p.id')
                    ->where('c.estado', 1)
                    ->groupBy('pi.detalleingresos_id')
                    ->select(
                        'pi.detalleingresos_id',
                        DB::raw('SUM(pi.cantidad) as cantidad_usada')
                    ),
                'u',
                function ($join) {
                    $join->on('u.detalleingresos_id', '=', 'di.id');
                }
            )
            ->select(
                'di.id',
                'i.nombre',
                'i.img_url',
                'i.umedida',
                DB::raw('di.cantidad as cantidad_ingresada'),
                DB::raw('COALESCE(u.cantidad_usada, 0) as cantidad_usada'),
                DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'),
                'di.punitario',
                DB::raw('(di.cantidad * di.punitario) as total')
            )
            ->when($buscar, function ($query) use ($buscar) {
                $query->where('i.nombre', 'like', "%{$buscar}%");
            })
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0')
            ->orderBy('i.nombre')
            ->paginate($request->per_page ?? 15);

        return $detalleIngresos;
    }
}
