<?php

namespace App\Http\Controllers;

use App\Models\ColumnManagements;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ColumnManagementController extends Controller
{

    public function columnStatus(Request $request)
    {   
        $columnManagment = ColumnManagements::where('user_id',$request->user()->id)
        ->where('section',$request->types)
        ->first();

        if(!$columnManagment){
            $columnManagment = new ColumnManagements();
        }
        
        $columnManagment->user_id = $request->user()->id;
        $columnManagment->section = $request->types;
        $columnManagment->data = $request->cols;
        
        if($columnManagment->save()) {

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failure']);
    }

    public function defaultResetColumns(Request $request)
    {
        $columnManagment =  ColumnManagements::where('user_id',$request->user()->id)
        ->where('section',$request->types);

        if($columnManagment->delete()) {

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failure']);
    }
}
