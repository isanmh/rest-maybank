<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ah = AuthController::Headers;
        $res1 = $request->header('X-Value');
        $res2 = $request->header('X-DATE');

        // logic cek header
        if ($res1 === $ah['X-Value'] && $res2 === $ah['X-DATE']) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 599,
                'message' => 'Kamu bukan Mr. Daud, tidak bisa akses',
            ], 599);
        }
    }
}
