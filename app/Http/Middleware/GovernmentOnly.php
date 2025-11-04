<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Government;

class GovernmentOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !($request->user() instanceof Government)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only government users can access this resource.'
            ], 403);
        }

        return $next($request);
    }
} 