<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Agent access only.',
            ], 403);
        }

        return $next($request);
    }
}