<?php

namespace App\Http\Middleware;

use Closure;

class Telematics
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
        $telematicsValue = setting('is_telematics_enabled');
        if($telematicsValue != 1) {
           return abort(401,'Unauthorized Action');
        }
        return $next($request);
    }
}
