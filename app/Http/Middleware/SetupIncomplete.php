<?php

namespace App\Http\Middleware;

use Closure;

class SetupIncomplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user has completed then redirect to dashboard
        // else continue with request, probably to route setup.index
        if ($request->user()->setup_complete) {
            return redirect(route('dashboard'));
        }

        return $next($request);
    }
}
