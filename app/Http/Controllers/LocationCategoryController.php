<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\LocationCategory;
use App\Http\Controllers\Controller;

class LocationCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        
        $locationCategory = new LocationCategory();
        $locationCategory->name = $request->category_name;
        if ($locationCategory->save()) {
            return $this->getAllCategories();
        }
    }

    public function getAllCategories()
    {
        $allLocationCategories = LocationCategory::orderBy('name')->get()->unique()->pluck('id', 'name')->toArray();
        $returnJsonString = array();
        foreach ($allLocationCategories as $key => $value) {
            array_push($returnJsonString, ['id'=>$value, 'text'=>$key]);
        }
        return $returnJsonString;
    }

    public function viewAllCategories(Request $request)
    {
        $allLocationsCategoryIds = Location::all()->unique('location_category_id')->pluck('location_category_id')->toArray();
        return response()->json(['allCategories' => $this->getAllCategories(), 'allLocationsCategoryIds' => $allLocationsCategoryIds]);
    }

    public function updateCategoryName(Request $request){
        // return $this->getAllCategories();
        $category = LocationCategory::find($request->pk);
        $category->name = $request->value;
        if($category->save()) {
            return $this->getAllCategories();
        }
    }

    public function deleteCategory(Request $request)
    {
        if(LocationCategory::where('id', $request->id)->delete()) {
            return $this->getAllCategories();
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
