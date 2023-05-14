<?php

namespace App\Http\Middleware;

use Auth;
use Config;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('login');
            }
        }

       /* $brandConfigExist = false;
        $brandConfigFile = config_path().'/'.env('BRAND_NAME').'/config-variables.php';
        if(\File::exists($brandConfigFile)){
            $brandConfigExist = true;
        }

        $vehicleRegions = config('config-variables.vehicleRegions');
        $userAccessibleRegionsForSelectSample = config('config-variables.userAccessibleRegionsForSelectSample');

        if(env('BRAND_NAME') == 'skanska') {
            $vehicleRegionsValArray = [];
            foreach ($vehicleRegions as $vehicleRegionsVal) {
                if(empty($vehicleRegionsVal) == false)
                {
                   foreach($vehicleRegionsVal as $keys=>$vehicleRegionsValues)
                    {   
                        $vehicleRegionsValArray[$keys]=$vehicleRegionsValues;
                    }
               }
            }
            asort($vehicleRegionsValArray);
            $vehicleRegions = $vehicleRegionsValArray;
        }
        
        $userAccessibleRegions = is_array(Auth::user()->accessible_regions) ? array_intersect_key($vehicleRegions, array_flip(Auth::user()->accessible_regions)) : [];
        if ($brandConfigExist) {
            Config::set(env('BRAND_NAME').'.config-variables.userAccessibleRegionsForQuery', $userAccessibleRegions);
        }
        else{
            Config::set('config-variables.userAccessibleRegionsForQuery', $userAccessibleRegions);
        }

        $userAccessibleRegions = array_merge(config('config-variables.userAccessibleRegions'), $userAccessibleRegions);
        foreach (array_keys($userAccessibleRegions) as $key) {
            if (strpos($key, '&') !== false) {
                $newKey = $key;
                $newKey = str_replace("&", "&amp;", $newKey);
                $userAccessibleRegions[$newKey] = $userAccessibleRegions[$key];
                unset($userAccessibleRegions[$key]);
            }
        }
        asort($userAccessibleRegions);

        if ($brandConfigExist) {
            Config::set(env('BRAND_NAME').'.config-variables.userAccessibleRegions', $userAccessibleRegions);
        }
        else{
            Config::set('config-variables.userAccessibleRegions', $userAccessibleRegions);
        }

        $userAccessibleRegionsForDashboard = is_array(Auth::user()->accessible_regions) ? array_intersect_key($userAccessibleRegionsForSelectSample, array_flip(Auth::user()->accessible_regions)) : [];
        $userAccessibleRegionsForDashboard = array_flip($userAccessibleRegionsForDashboard);
        $userAccessibleRegionsForDashboard = array_merge(config('config-variables.userAccessibleRegionsForSelect'), $userAccessibleRegionsForDashboard);
        if ($brandConfigExist) {
            Config::set(env('BRAND_NAME').'.config-variables.userAccessibleRegionsForSelect', $userAccessibleRegionsForDashboard);
        }
        else{
            Config::set('config-variables.userAccessibleRegionsForSelect', $userAccessibleRegionsForDashboard);
        }
*/
        return $next($request);
    }
}
