<?php

namespace App\Http\Controllers;

use Carbon\Carbon as Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*protected function calcTaxYear(){
        //$today = Carbon::today();
        $currentyear = date_format(Carbon::now(),"Y");
        $newTaxYearDate = Carbon::parse('06-04-'.$currentyear);//new tax year
        if(Carbon::parse($newTaxYearDate)->gt(Carbon::now())){
            return ($currentyear-1).'-'.$currentyear;
        }
        else{
            return $currentyear.'-'.($currentyear+1);
        }
    }*/
}
