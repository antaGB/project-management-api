<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Take users roles
        $userRoles = $request->user()->roles->pluck('name')->toArray();

        // Check if user has access
        if (!array_intersect($userRoles, $roles)) {
            return $this->error('Access denied.', 403);
        }

        return $next($request);
    }
}
