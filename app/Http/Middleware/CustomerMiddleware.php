<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer access only.',
            ], 403);
        }

        return $next($request);
    }
}