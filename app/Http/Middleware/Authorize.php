<?php

namespace App\Http\Middleware;

use URL;
use Spatie\Authorize\Middleware\Authorize as BaseAuthorize;
use Symfony\Component\HttpFoundation\Response;

class Authorize extends BaseAuthorize
{
    protected function handleUnauthorizedRequest($request, $ability = null, $model = null)
    {
        flash()->success(config('config-variables.flashMessages.unauthorized'));
        return redirect(URL::previous());
        //flash()->error(config('config-variables.flashMessages.noAccess'));
        //return redirect('/home');
        // abort(401,'Unauthorized Action');
    }
}