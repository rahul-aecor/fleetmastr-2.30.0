<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;

use Closure;
use Illuminate\Support\Facades\Route;

class XssSanitizer
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

        $excludedUrls = [
            '/settings/storeSiteConfiguration',
            '/settings',
            '/templates'
        ];
        $input = $request->all();
        $fullUrl = $request->getUri();
        $path = explode(config('app.url'),$fullUrl);
        if (isset($path[1])) {
            $path = $path[1];
            if (!in_array($path,$excludedUrls)) {
                array_walk_recursive($input, function(&$input) {
                    //$input = strip_tags($input);
                    //$input = filter_var($input, FILTER_SANITIZE_STRING);
                    $input = htmlspecialchars($input, ENT_NOQUOTES | ENT_HTML5);

                });
                $request->merge($input);
            }
        }

        return $next($request);
    }
}
