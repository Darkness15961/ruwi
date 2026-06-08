<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApisController extends Controller
{
    private function respuesta($data, string $message = 'Datos obtenidos correctamente')
    {
        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function subqueryCantidadUsada()
    {
        return DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->join('productoinsumos as pi', 'pi.productos_id', '=', 'p.id')
            ->where('c.estado', 1)
            ->groupBy('pi.detalleingresos_id')
            ->select(
                'pi.detalleingresos_id',
                DB::raw('SUM(pi.cantidad) as cantidad_usada')
            );
    }

    private function periodo(Request $request): array
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        return [
            'mes' => (int) $mes,
            'anio' => (int) $anio,
            'inicio' => Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString(),
            'fin' => Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString(),
        ];
    }

    private function tablaExiste(string $tabla): bool
    {
        return Schema::hasTable($tabla);
    }

    public function resumen(Request $request)
    {
        $periodo = $this->periodo($request);

        $inventario = DB::table('detalleingresos as di')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->selectRaw('
                SUM(di.cantidad * di.punitario) as valor_total,
                SUM((di.cantidad - COALESCE(u.cantidad_usada, 0)) * di.punitario) as valor_disponible,
                COUNT(CASE WHEN (di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0 THEN 1 END) as lotes_con_stock
            ')
            ->first();

        $cotizacionesMes = DB::table('cotizacions')
            ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
            ->count();

        $valorCotizacionesMes = DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$periodo['inicio'], $periodo['fin']])
            ->selectRaw('SUM((p.cantidad * p.punitario)) as total')
            ->value('total') ?? 0;

        $comprasMes = DB::table('ingresos')
            ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
            ->count();

        $gastoComprasMes = DB::table('ingresos as ing')
            ->join('detalleingresos as di', 'di.ingresos_id', '=', 'ing.id')
            ->whereBetween('ing.fecha', [$periodo['inicio'], $periodo['fin']])
            ->selectRaw('SUM(di.cantidad * di.punitario) as total')
            ->value('total') ?? 0;

        $data = [
            'periodo' => $periodo,
            'inventario' => [
                'valor_total' => round((float) $inventario->valor_total, 2),
                'valor_disponible' => round((float) $inventario->valor_disponible, 2),
                'lotes_con_stock' => (int) $inventario->lotes_con_stock,
                'total_insumos' => DB::table('insumos')->count(),
                'total_categorias' => DB::table('categorias')->count(),
            ],
            'cotizaciones' => [
                'total' => DB::table('cotizacions')->count(),
                'mes_actual' => $cotizacionesMes,
                'valor_mes' => round((float) $valorCotizacionesMes, 2),
                'activas' => DB::table('cotizacions')->where('estado', 1)->count(),
            ],
            'compras' => [
                'total' => DB::table('ingresos')->count(),
                'mes_actual' => $comprasMes,
                'gasto_mes' => round((float) $gastoComprasMes, 2),
            ],
            'clientes' => [
                'total' => $this->tablaExiste('rucs') ? DB::table('rucs')->count() : 0,
            ],
            'produccion' => $this->kpisProduccionResumen(),
        ];

        return $this->respuesta($data, 'Resumen del dashboard obtenido correctamente');
    }

    public function inventario(Request $request)
    {
        $porCategoria = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->join('categorias as cat', 'cat.id', '=', 'i.categorias_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->groupBy('cat.id', 'cat.nombre')
            ->select(
                'cat.id',
                'cat.nombre',
                DB::raw('COUNT(DISTINCT i.id) as total_insumos'),
                DB::raw('SUM(di.cantidad * di.punitario) as valor_total'),
                DB::raw('SUM((di.cantidad - COALESCE(u.cantidad_usada, 0)) * di.punitario) as valor_disponible')
            )
            ->orderByDesc('valor_disponible')
            ->get();

        $topInsumos = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->groupBy('i.id', 'i.nombre', 'i.umedida', 'i.img_url')
            ->select(
                'i.id',
                'i.nombre',
                'i.umedida',
                'i.img_url',
                DB::raw('SUM(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_disponible'),
                DB::raw('SUM((di.cantidad - COALESCE(u.cantidad_usada, 0)) * di.punitario) as valor_disponible')
            )
            ->having('cantidad_disponible', '>', 0)
            ->orderByDesc('valor_disponible')
            ->limit(10)
            ->get();

        $stockBajo = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->select(
                'di.id',
                'i.nombre',
                'i.umedida',
                DB::raw('di.cantidad as cantidad_ingresada'),
                DB::raw('COALESCE(u.cantidad_usada, 0) as cantidad_usada'),
                DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'),
                'di.punitario'
            )
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0')
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) <= (di.cantidad * 0.2)')
            ->orderBy('cantidad_restante')
            ->limit(15)
            ->get();

        $porVencer = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->whereNotNull('di.fecha_vencimiento')
            ->whereBetween('di.fecha_vencimiento', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0')
            ->select(
                'di.id',
                'i.nombre',
                'di.fecha_vencimiento',
                DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'),
                'i.umedida'
            )
            ->orderBy('di.fecha_vencimiento')
            ->limit(15)
            ->get();

        $totales = DB::table('detalleingresos as di')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->selectRaw('
                SUM(di.cantidad * di.punitario) as valor_total,
                SUM((di.cantidad - COALESCE(u.cantidad_usada, 0)) * di.punitario) as valor_disponible,
                SUM(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_disponible,
                COUNT(CASE WHEN (di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0 THEN 1 END) as lotes_con_stock,
                COUNT(CASE WHEN (di.cantidad - COALESCE(u.cantidad_usada, 0)) <= 0 THEN 1 END) as lotes_agotados
            ')
            ->first();

        return $this->respuesta([
            'totales' => [
                'valor_total' => round((float) $totales->valor_total, 2),
                'valor_disponible' => round((float) $totales->valor_disponible, 2),
                'cantidad_disponible' => round((float) $totales->cantidad_disponible, 2),
                'lotes_con_stock' => (int) $totales->lotes_con_stock,
                'lotes_agotados' => (int) $totales->lotes_agotados,
                'items_stock_bajo' => $stockBajo->count(),
                'items_por_vencer' => $porVencer->count(),
            ],
            'por_categoria' => $porCategoria,
            'top_insumos' => $topInsumos,
            'stock_bajo' => $stockBajo,
            'por_vencer' => $porVencer,
        ], 'KPIs de inventario obtenidos correctamente');
    }

    public function compras(Request $request)
    {
        $periodo = $this->periodo($request);

        $porOrigen = DB::table('ingresos')
            ->select('origen', DB::raw('COUNT(*) as total'))
            ->groupBy('origen')
            ->orderByDesc('total')
            ->get();

        $gastoTotal = DB::table('detalleingresos')
            ->selectRaw('SUM(cantidad * punitario) as total')
            ->value('total') ?? 0;

        $gastoMes = DB::table('ingresos as ing')
            ->join('detalleingresos as di', 'di.ingresos_id', '=', 'ing.id')
            ->whereBetween('ing.fecha', [$periodo['inicio'], $periodo['fin']])
            ->selectRaw('SUM(di.cantidad * di.punitario) as total')
            ->value('total') ?? 0;

        $topProveedores = DB::table('ingresos')
            ->whereNotNull('ruc_factura')
            ->where('ruc_factura', '!=', '')
            ->select(
                'ruc_factura',
                'detalle',
                DB::raw('COUNT(*) as total_compras'),
                DB::raw('MAX(fecha) as ultima_compra')
            )
            ->groupBy('ruc_factura', 'detalle')
            ->orderByDesc('total_compras')
            ->limit(10)
            ->get();

        $comprasRecientes = DB::table('ingresos')
            ->select('id', 'fecha', 'detalle', 'origen', 'ruc_factura', 'serie_factura', 'nro_factura')
            ->orderByDesc('fecha')
            ->limit(10)
            ->get();

        return $this->respuesta([
            'periodo' => $periodo,
            'totales' => [
                'total_compras' => DB::table('ingresos')->count(),
                'compras_mes' => DB::table('ingresos')
                    ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                    ->count(),
                'gasto_total' => round((float) $gastoTotal, 2),
                'gasto_mes' => round((float) $gastoMes, 2),
            ],
            'por_origen' => $porOrigen,
            'top_proveedores' => $topProveedores,
            'compras_recientes' => $comprasRecientes,
        ], 'KPIs de compras obtenidos correctamente');
    }

    public function cotizaciones(Request $request)
    {
        $periodo = $this->periodo($request);

        $porEstado = DB::table('cotizacions')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        $valorPipeline = DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->where('c.estado', 1)
            ->selectRaw('SUM((p.cantidad * p.punitario) + p.igv) as total')
            ->value('total') ?? 0;

        $valorMes = DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$periodo['inicio'], $periodo['fin']])
            ->selectRaw('SUM((p.cantidad * p.punitario) + p.igv) as total')
            ->value('total') ?? 0;

        $promedioValor = DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->selectRaw('c.id, SUM((p.cantidad * p.punitario) + p.igv) as total_cotizacion')
            ->groupBy('c.id');

        $promedio = DB::query()
            ->fromSub($promedioValor, 't')
            ->selectRaw('AVG(total_cotizacion) as promedio')
            ->value('promedio') ?? 0;

        $topClientes = DB::table('cotizacions as c')
            ->leftJoin('rucs as r', 'r.ruc', '=', 'c.ruc')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->select(
                'c.ruc',
                DB::raw('COALESCE(r.razon_social, c.ruc) as cliente'),
                DB::raw('COUNT(DISTINCT c.id) as total_cotizaciones'),
                DB::raw('SUM((p.cantidad * p.punitario) + p.igv) as valor_total')
            )
            ->groupBy('c.ruc', 'r.razon_social')
            ->orderByDesc('valor_total')
            ->limit(10)
            ->get();

        $porVencer = DB::table('cotizacions')
            ->where('estado', 1)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(15)->toDateString()])
            ->select('id', 'ruc', 'descripcion', 'fecha', 'fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->limit(15)
            ->get();

        $cotizacionesRecientes = DB::table('cotizacions')
            ->select('id', 'fecha', 'ruc', 'descripcion', 'estado', 'fecha_vencimiento')
            ->orderByDesc('fecha')
            ->limit(10)
            ->get();

        return $this->respuesta([
            'periodo' => $periodo,
            'totales' => [
                'total_cotizaciones' => DB::table('cotizacions')->count(),
                'cotizaciones_mes' => DB::table('cotizacions')
                    ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                    ->count(),
                'activas' => DB::table('cotizacions')->where('estado', 1)->count(),
                'valor_pipeline' => round((float) $valorPipeline, 2),
                'valor_mes' => round((float) $valorMes, 2),
                'promedio_valor' => round((float) $promedio, 2),
                'por_vencer' => $porVencer->count(),
            ],
            'por_estado' => $porEstado,
            'top_clientes' => $topClientes,
            'por_vencer' => $porVencer,
            'cotizaciones_recientes' => $cotizacionesRecientes,
        ], 'KPIs de cotizaciones obtenidos correctamente');
    }

    public function produccion()
    {
        if (!$this->tablaExiste('trabajos')) {
            return $this->respuesta([
                'disponible' => false,
                'mensaje' => 'Módulo de producción no configurado aún',
            ], 'Módulo de producción no disponible');
        }

        $trabajosPorEstado = DB::table('trabajos')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->get();

        $totalTrabajos = DB::table('trabajos')->count();
        $finalizados = DB::table('trabajos')->where('estado', 'FINALIZADO')->count();
        $fallidos = DB::table('trabajos')->where('estado', 'FALLIDO')->count();

        $costoTotal = DB::table('trabajos')
            ->whereNotNull('costo_total')
            ->selectRaw('SUM(costo_total) as total')
            ->value('total') ?? 0;

        $tiempoPromedio = DB::table('trabajos')
            ->whereNotNull('tiempo_real_min')
            ->selectRaw('AVG(tiempo_real_min) as promedio')
            ->value('promedio') ?? 0;

        $maquinasPorEstado = $this->tablaExiste('maquinas')
            ? DB::table('maquinas')
                ->select('estado', DB::raw('COUNT(*) as total'))
                ->groupBy('estado')
                ->get()
            : collect();

        $trabajosRecientes = DB::table('trabajos as t')
            ->when($this->tablaExiste('maquinas'), function ($query) {
                $query->join('maquinas as m', 'm.id', '=', 't.maquinas_id')
                    ->addSelect('m.nombre as maquina');
            })
            ->select('t.id', 't.nombre', 't.estado', 't.fecha_inicio', 't.fecha_fin', 't.costo_total', 't.tiempo_real_min')
            ->orderByDesc('t.created_at')
            ->limit(10)
            ->get();

        $costoMateriales = $this->tablaExiste('trabajomateriales')
            ? DB::table('trabajomateriales')->selectRaw('SUM(costo) as total')->value('total') ?? 0
            : 0;

        $costoEnergia = $this->tablaExiste('trabajoenergia')
            ? DB::table('trabajoenergia')->selectRaw('SUM(costo_total) as total')->value('total') ?? 0
            : 0;

        return $this->respuesta([
            'disponible' => true,
            'totales' => [
                'total_trabajos' => $totalTrabajos,
                'pendientes' => DB::table('trabajos')->where('estado', 'PENDIENTE')->count(),
                'imprimiendo' => DB::table('trabajos')->where('estado', 'IMPRIMIENDO')->count(),
                'finalizados' => $finalizados,
                'fallidos' => $fallidos,
                'tasa_exito' => $totalTrabajos > 0 ? round(($finalizados / $totalTrabajos) * 100, 2) : 0,
                'costo_total' => round((float) $costoTotal, 2),
                'costo_materiales' => round((float) $costoMateriales, 2),
                'costo_energia' => round((float) $costoEnergia, 2),
                'tiempo_promedio_min' => round((float) $tiempoPromedio, 2),
            ],
            'trabajos_por_estado' => $trabajosPorEstado,
            'maquinas_por_estado' => $maquinasPorEstado,
            'trabajos_recientes' => $trabajosRecientes,
        ], 'KPIs de producción obtenidos correctamente');
    }

    public function graficos(Request $request)
    {
        $meses = (int) $request->input('meses', 6);
        $inicio = now()->subMonths($meses - 1)->startOfMonth();

        $comprasMensuales = DB::table('ingresos as ing')
            ->join('detalleingresos as di', 'di.ingresos_id', '=', 'ing.id')
            ->where('ing.fecha', '>=', $inicio->toDateString())
            ->selectRaw("DATE_FORMAT(ing.fecha, '%Y-%m') as periodo")
            ->selectRaw('COUNT(DISTINCT ing.id) as total_compras')
            ->selectRaw('SUM(di.cantidad * di.punitario) as gasto')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $cotizacionesMensuales = DB::table('cotizacions')
            ->where('fecha', '>=', $inicio->toDateString())
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $valorCotizacionesMensual = DB::table('cotizacions as c')
            ->join('productos as p', 'p.cotizacions_id', '=', 'c.id')
            ->where('c.fecha', '>=', $inicio->toDateString())
            ->selectRaw("DATE_FORMAT(c.fecha, '%Y-%m') as periodo")
            ->selectRaw('SUM((p.cantidad * p.punitario) + p.igv) as valor')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $produccionMensual = collect();
        if ($this->tablaExiste('trabajos')) {
            $produccionMensual = DB::table('trabajos')
                ->where('created_at', '>=', $inicio)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as periodo")
                ->selectRaw('COUNT(*) as total_trabajos')
                ->selectRaw("SUM(CASE WHEN estado = 'FINALIZADO' THEN 1 ELSE 0 END) as finalizados")
                ->selectRaw("SUM(CASE WHEN estado = 'FALLIDO' THEN 1 ELSE 0 END) as fallidos")
                ->selectRaw('SUM(COALESCE(costo_total, 0)) as costo_total')
                ->groupBy('periodo')
                ->orderBy('periodo')
                ->get();
        }

        $inventarioPorCategoria = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->join('categorias as cat', 'cat.id', '=', 'i.categorias_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->groupBy('cat.nombre')
            ->select(
                'cat.nombre',
                DB::raw('SUM((di.cantidad - COALESCE(u.cantidad_usada, 0)) * di.punitario) as valor_disponible')
            )
            ->orderByDesc('valor_disponible')
            ->get();

        return $this->respuesta([
            'meses' => $meses,
            'compras_mensuales' => $comprasMensuales,
            'cotizaciones_mensuales' => $cotizacionesMensuales,
            'valor_cotizaciones_mensual' => $valorCotizacionesMensual,
            'produccion_mensual' => $produccionMensual,
            'inventario_por_categoria' => $inventarioPorCategoria,
        ], 'Datos para gráficos obtenidos correctamente');
    }

    public function alertas()
    {
        $stockBajo = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->select(
                'i.nombre',
                DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'),
                'i.umedida'
            )
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0')
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) <= (di.cantidad * 0.2)')
            ->orderBy('cantidad_restante')
            ->limit(10)
            ->get();

        $porVencer = DB::table('detalleingresos as di')
            ->join('insumos as i', 'i.id', '=', 'di.insumos_id')
            ->leftJoinSub($this->subqueryCantidadUsada(), 'u', function ($join) {
                $join->on('u.detalleingresos_id', '=', 'di.id');
            })
            ->whereNotNull('di.fecha_vencimiento')
            ->where('di.fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            ->whereRaw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) > 0')
            ->select('i.nombre', 'di.fecha_vencimiento', DB::raw('(di.cantidad - COALESCE(u.cantidad_usada, 0)) as cantidad_restante'))
            ->orderBy('di.fecha_vencimiento')
            ->limit(10)
            ->get();

        $cotizacionesPorVencer = DB::table('cotizacions')
            ->where('estado', 1)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->addDays(15)->toDateString())
            ->select('id', 'ruc', 'descripcion', 'fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        $maquinasAlerta = collect();
        if ($this->tablaExiste('maquinas')) {
            $maquinasAlerta = DB::table('maquinas')
                ->whereIn('estado', ['MANTENIMIENTO', 'FUERA_SERVICIO'])
                ->select('id', 'nombre', 'estado', 'marca', 'modelo')
                ->get();
        }

        $trabajosFallidos = collect();
        if ($this->tablaExiste('trabajos')) {
            $trabajosFallidos = DB::table('trabajos')
                ->where('estado', 'FALLIDO')
                ->select('id', 'nombre', 'fecha_inicio', 'fecha_fin')
                ->orderByDesc('fecha_fin')
                ->limit(10)
                ->get();
        }

        return $this->respuesta([
            'total_alertas' => $stockBajo->count() + $porVencer->count() + $cotizacionesPorVencer->count() + $maquinasAlerta->count() + $trabajosFallidos->count(),
            'stock_bajo' => $stockBajo,
            'insumos_por_vencer' => $porVencer,
            'cotizaciones_por_vencer' => $cotizacionesPorVencer,
            'maquinas_alerta' => $maquinasAlerta,
            'trabajos_fallidos' => $trabajosFallidos,
        ], 'Alertas obtenidas correctamente');
    }

    private function kpisProduccionResumen(): array
    {
        if (!$this->tablaExiste('trabajos')) {
            return [
                'disponible' => false,
                'pendientes' => 0,
                'imprimiendo' => 0,
                'finalizados' => 0,
                'maquinas_activas' => 0,
            ];
        }

        $maquinasActivas = $this->tablaExiste('maquinas')
            ? DB::table('maquinas')->where('estado', 'ACTIVA')->count()
            : 0;

        return [
            'disponible' => true,
            'pendientes' => DB::table('trabajos')->where('estado', 'PENDIENTE')->count(),
            'imprimiendo' => DB::table('trabajos')->where('estado', 'IMPRIMIENDO')->count(),
            'finalizados' => DB::table('trabajos')->where('estado', 'FINALIZADO')->count(),
            'maquinas_activas' => $maquinasActivas,
        ];
    }
}
