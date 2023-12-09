<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $isStudent = ! Auth::user()->is_admin;

                if ($isStudent) {
                    if ($request->routeIs('courses.show')) {
                        return to_route(
                            'filament.student.resources.courses.view',
                            ['record' => $request->route()->parameter('course')]
                        );
                    }

                    return to_route('filament.student.resources.courses.index');
                }

                return $next($request);
            }
        }

        return $next($request);
    }
}
