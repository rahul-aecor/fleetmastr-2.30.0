<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Location;

class LocationRepository extends EloquentRepositoryAbstract {
	public function __construct($data = null)
    {
    	$this->Database = DB::table('locations')
    		->select('locations.id', 'locations.name',
    			DB::raw("(CASE WHEN (locations.address2!='') THEN CONCAT(locations.address1,', ', locations.address2) ELSE locations.address1 END) as address"), 'locations.town_city', 'locations.postcode', 'location_categories.name as category_name', 'locations.latitude', 'locations.longitude', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name"), DB::raw("DATE_FORMAT(CONVERT_TZ(locations.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'created_at'"))
            ->leftJoin('location_categories', 'locations.location_category_id', '=', 'location_categories.id')
            ->leftJoin('users', function($join){
                $join->on('locations.created_by', '=', 'users.id')->whereNull('users.deleted_at');
            })
    		->whereNull('locations.deleted_at');

        if(isset($data['filters'])) {
                $filters = json_decode($data['filters'], true);
                $locationVal = isset($filters['locationVal']) ? $filters['locationVal'] : null;
                $categoryVal = isset($filters['categoryVal']) ? $filters['categoryVal'] : null;
            } else {
                $locationVal = isset($data['locationVal']) ? $data['locationVal'] : null;
                $categoryVal = isset($data['categoryVal']) ? $data['categoryVal'] : null;
            }

        if(isset($locationVal) && !empty($locationVal)) {
            $this->Database = $this->Database->where('locations.id', $locationVal);
        }

        if(isset($categoryVal) && !empty($categoryVal)) {
            $this->Database = $this->Database->where('location_categories.id', $categoryVal);
        }

		$this->visibleColumns = [
            'locations.name', 'locations.address', 'locations.town_city', 'locations.postcode', 'category_name'
        ];        

        $this->orderBy = [['name', 'ASC']];
    }

    public function fetchLocationByCategory($request){
        $locationCategoryId=$request->locationCategoryId;
        $query= DB::table('locations')
    		->select('locations.id', 'locations.name',
    			DB::raw("(CASE WHEN (locations.address2!='') THEN CONCAT(locations.address1,', ', locations.address2) ELSE locations.address1 END) as address"),'locations.town_city', 'locations.postcode', 'location_categories.name as category_name', 'locations.latitude', 'locations.longitude')
            ->leftJoin('location_categories', 'locations.location_category_id', '=', 'location_categories.id')
            /* ->leftJoin('users', function($join){
                $join->on('locations.created_by', '=', 'users.id')->whereNull('users.deleted_at');
            }) */
    		->whereNull('locations.deleted_at');

        if(isset($locationCategoryId) && !empty($locationCategoryId)) {
            $query->where('locations.location_category_id', $locationCategoryId);
        }
       
        $result=$query->get();
        
        if($result){
            return $result;
        }
        return [];
    }

    public function fetchLocationById($request){
        $locationId=$request->locationId;
        $query= DB::table('locations')
        ->select('locations.id', 'locations.name','locations.address1','locations.address2','locations.town_city', 'locations.postcode', 'location_categories.name as category_name', 'locations.latitude', 'locations.longitude')
        ->leftJoin('location_categories', 'locations.location_category_id', '=', 'location_categories.id');
        $query= $query->where('locations.id', $locationId);
        $result=$query->first();
        if($result){
            return $result;
        }
        return [];
    }
}