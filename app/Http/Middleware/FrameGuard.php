<?php

namespace App\Http\Middleware;

use Closure;

class FrameGuard
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
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Xss-Protection', '1; mode=block', false);
        $response->headers->set('Public-Key-Pins', "pin-sha256='X3pGTSOuJeEVw989IJ/cEtXUEmy52zs1TZQrU06KUKg='; pin-sha256='MHJYVThihUrJcxW6wcqyOISTXIsInsdj3xK8QrZbHec='; pin-sha256='isi41AizREkLvvft0IRW4u3XMFR2Yg7bvrF7padyCJg='; includeSubdomains; max-age=2592000", false);
        $response->headers->set('X-Content-Type-Options', "nosniff", false);
        $response->headers->set('Access-Control-Allow-Origin', env('APP_URL'), false);

        return $response;
    }
}
