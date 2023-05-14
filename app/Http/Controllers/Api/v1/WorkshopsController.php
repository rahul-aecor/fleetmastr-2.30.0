<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;

class WorkshopsController extends APIController
{
	public function getWorkshopCompanies(Request $request)
    {
    	return Company::where('user_type', 'Workshop')->select('name', 'id')->orderBy('name')->get()->unique('name')->values()->all();
    }
}
