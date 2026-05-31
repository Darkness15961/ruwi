<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UseTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $rucHeader = config('database.tenant.ruc_header', 'X-Ruc');
        $ruc = (string) $request->header($rucHeader, '');

        if ($ruc === '' && $request->user() !== null && isset($request->user()->ruc)) {
            $ruc = (string) $request->user()->ruc;
        }

        if ($ruc === '') {
            return response()->json([
                'status' => 0,
                'message' => "Falta el header {$rucHeader} para resolver la base de datos tenant",
                'data' => (object) [],
            ], 422);
        }

        if (! preg_match('/^\d{8,15}$/', $ruc)) {
            return response()->json([
                'status' => 0,
                'message' => 'El RUC enviado no es valido',
                'data' => (object) [],
            ], 422);
        }

        $prefix = config('database.tenant.prefix', 'ruwi_');
        $databaseName = $prefix.$ruc;

        $baseConnectionName = config('database.tenant.base_connection', config('database.default', 'mysql'));
        $baseConnection = config("database.connections.{$baseConnectionName}", []);

        if ($baseConnection === []) {
            return response()->json([
                'status' => 0,
                'message' => 'No se encontro la conexion base para el tenant',
                'data' => (object) [],
            ], 500);
        }

        $tenantConnection = array_merge($baseConnection, [
            'database' => $databaseName,
        ]);

        Config::set('database.connections.tenant', $tenantConnection);

        $previousConnection = DB::getDefaultConnection();

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        try {
            return $next($request);
        } finally {
            DB::setDefaultConnection($previousConnection);
        }
    }
}
