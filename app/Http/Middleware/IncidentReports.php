<?php

namespace App\Http\Middleware;

use Closure;

class IncidentReports
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
        $incidentReports = setting('is_incident_reports_enabled');
        if($incidentReports != 1) {
           return abort(401,'Unauthorized Action');
        }
        return $next($request);
    }
}
