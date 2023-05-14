<?php

namespace App\Http\Controllers\Api\v1;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class APIController extends Controller
{
    use Helpers;

    public function login(Request $request)
    {
    	$imei = $request->get('imei');
        if(empty($imei)){
            return $this->response->errorBadRequest();
        }
    	$user = User::where('imei','=',$imei)->first();

    	// echo "<pre>"; print_r($regno_list); exit;
    	if(!empty($user)){
			$token = JWTAuth::fromUser($user);
			return \Response::json(compact('token'));
    	}
    	else{
    		return $this->response->errorUnauthorized();
    	}
    }
}
