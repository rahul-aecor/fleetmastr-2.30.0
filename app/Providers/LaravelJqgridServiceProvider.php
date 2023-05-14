<?php
/**
 * @file
 * LaravelJqGrid Service Provider.
 *
 * All LaravelJqGrid code is copyright by the original authors and released under the MIT License.
 * See LICENSE.
 */

namespace App\Providers;

use Mgallegos\LaravelJqgrid\Renders\Validations\ColModel\NameValidation;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelJqgridServiceProvider extends \Mgallegos\LaravelJqgrid\LaravelJqgridServiceProvider {

    /**
     * Register encoder service provider.
     *
     * @return void
     */
    public function registerEncoder()
    {
        // $this->app->bind('Mgallegos\LaravelJqgrid\Encoders\RequestedDataInterface', function($app)
        // {
        //     return new \App\Custom\Encoder\JqGridJsonEncoder($app->make('excel'));
        // });
        $this->app->bind('\App\Custom\Encoder\RequestedDataInterface', function($app)
        {
             return new \App\Custom\Encoder\JqGridJsonEncoder($app->make('excel'));
        });
    }

}
