<?php

namespace App\Http\Middleware;

use Closure;

class SetupComplete
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
        // If user has not completed setup then redirect to route setup.index
        if (! $request->user()->setup_complete) {
            return redirect(route('setup.index'));
        }

        return $next($request);
    }
}
