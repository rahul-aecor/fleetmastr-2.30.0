<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Group;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authUserMessageRegions = Auth::user()->messageRegions->pluck('id');
        $groups = Group::with(['users' => function ($query) use ($authUserMessageRegions) {
                $query->whereIn('user_region_id', $authUserMessageRegions);
            }])->where('created_by', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return response()->json($groups); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $group = new Group();
        $group->name = $request->name;
        $group->created_by = Auth::id();
        $group->updated_by = Auth::id();
        $group->save();
        
        $user_ids = array_pluck($request->users, 'id');        
        $group->users()->sync($user_ids);
        return $group;   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $group->name = $request->name;
        $group->save();
     
        $user_ids = array_pluck($request->users, 'id');
        $group->users()->sync($user_ids);
        return $group;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::find($id);
        if ($group) {
            $group->delete();    
        }        
        return ['status' => 'success'];        
    }
}
