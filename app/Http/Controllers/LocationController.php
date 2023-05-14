<?php

namespace App\Http\Controllers;

use Auth;
use view;
use Input;
use JavaScript;
use App\Http\Requests;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\LocationCategory;
use App\Custom\Facades\GridEncoder;
use App\Http\Controllers\Controller;
use App\Repositories\LocationRepository;

class LocationController extends Controller
{
    public $title= 'Locations';

    public function __construct() {
        View::share ( 'title', $this->title );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function anyData(Request $request) {
       return GridEncoder::encodeRequestedData(new LocationRepository($request->all()), Input::all());
    }

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
        $locationCategories = LocationCategory::orderBy('name')->get()->unique()->toArray();
        JavaScript::put([
            'locationCategories' => $locationCategories,
            'from' => 'add'
        ]);
        return view('locations.create')->with(['locationCategories' => $locationCategories, 'from' => 'add']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $location = new Location();
        if(isset($request['category_id']) && !empty($request['category_id'])) {
            $location->location_category_id = $request['category_id'];
        } else {
            $location->location_category_id = null;
        }
        
        $location->name = html_entity_decode($request['name']);
        $location->address1 = html_entity_decode($request['address1']);
        $location->address2 = html_entity_decode($request['address2']);
        $location->town_city = html_entity_decode($request['town_city']);
        $location->postcode = $request['postcode'];
        $location->latitude = $request['latitude'];
        $location->longitude = $request['longitude'];
        $location->created_by = Auth::user()->id;
        $location->save();
        if($location) {
            flash()->success(config('config-variables.flashMessages.locationSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.locationNotSaved'));
        }
        return redirect('telematics');
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
        $location = Location::findOrFail($id);
        $locationCategories = LocationCategory::orderBy('name')->get()->unique()->toArray();
        JavaScript::put([
            'locationCategories' => $locationCategories,
            'location' => $location->toArray(),
            'from' => 'edit'
        ]);
        return view('locations.edit')->with(['location' => $location, 'locationCategories' => $locationCategories, 'from' => 'edit']);
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
        $location = Location::find($id);
        if(isset($request['category_id']) && !empty($request['category_id'])) {
            $request['category_id'] = $request['category_id'];
        } else {
            $request['category_id'] = null;
        }
        $updatedLocation = $location->update([
            'name' => html_entity_decode($request['name']),
            'location_category_id' => $request['category_id'],
            'address1' => html_entity_decode($request['address1']),
            'address2' => html_entity_decode($request['address2']),
            'town_city' => html_entity_decode($request['town_city']),
            'postcode' => $request['postcode'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
        ]);
        if ($updatedLocation) {
            flash()->success(config('config-variables.flashMessages.locationSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.locationNotSaved'));
        }
        return redirect('telematics');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Location::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.locationDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.locationNotDeleted'));
        }
        return redirect('telematics');
    }

    public function viewAllCategories(Request $request)
    {
        return $this->getAllCategoriesJson();
    }

    public function getAllCategoriesJson()
    {
        $allLocationCategories = LocationCategory::all()->unique()->pluck('id', 'name')->toArray();
        $returnJsonString = array();
        foreach ($allLocationCategories as $key => $value) {
            array_push($returnJsonString, ['id'=>$value, 'text'=>$key]);
        }
        return $returnJsonString;
    }
}
