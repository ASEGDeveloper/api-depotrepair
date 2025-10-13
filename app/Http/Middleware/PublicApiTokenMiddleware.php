<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicApiTokenMiddleware
{
   
   public function handle(Request $request, Closure $next)
    {
        $token = $request->header('x-api-key');

        if ($token !== env('API_ACCESS_KEY')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
