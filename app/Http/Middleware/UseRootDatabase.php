<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UseRootDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $rootConnection = config('database.tenant.base_connection', config('database.default', 'mysql'));
        $previousConnection = DB::getDefaultConnection();

        DB::setDefaultConnection($rootConnection);

        try {
            return $next($request);
        } finally {
            DB::setDefaultConnection($previousConnection);
        }
    }
}
