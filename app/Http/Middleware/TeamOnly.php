<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;

class TeamOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !(auth()->user() instanceof VolunteerTeam)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only volunteer teams can perform this action.'
            ], 403);
        }

        return $next($request);
    }
} 