<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelematicsJourneys extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'telematics_journeys';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->select("id", "email", "first_name", "last_name")->latest();
    }
    /**
     * Following function is to return the user who should be considered driver for a journey, it is based on following order:
     * 
        a. RFID - show the driver who has used their RFID card; IF that is not available...
        b. THEN show the 'Nominated Driver'; IF that is not available...
        c. Driver unknown
     * */
    public static function getDriverToShow($journeyId){
        $journey = Self::with(['user', 'vehicle'])->where('id',$journeyId)->first();
        $user = null;
        $returnArray = [];
        if($journey->user != null && $journey->user_id != env('SYSTEM_USER_ID')){
            $user = $journey->user;
       }
       elseif ( $journey->vehicle->nominated_driver != null){
            $user = $journey->vehicle->nominatedDriver;
       }
       else {
            $user = User::find(env('SYSTEM_USER_ID'));
       }
       if ($user->id == env('SYSTEM_USER_ID')) {
            $returnArray['user_id'] = $user->id;
            $returnArray['first_name'] = config('config-variables.telematicsSystemUserVisibleName.FN');
            $returnArray['last_name'] = config('config-variables.telematicsSystemUserVisibleName.LN');
            $returnArray['email'] = $user->email;
        }
        else{
            $returnArray['user_id'] = $user->id;
            $returnArray['first_name'] = $user->first_name;
            $returnArray['last_name'] = $user->last_name;
            $returnArray['email'] = $user->email;
        }
        return $returnArray;
                
        
        
    }
    public function getTelematicsDetailsByStartDate($sdate, $edate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $groupBy)
    {   
        $startDate = Carbon::createFromFormat('d/m/Y H:i:s',  $sdate)->startOfDay(); 
        $endDate = Carbon::createFromFormat('d/m/Y H:i:s',  $edate)->endOfDay();
        $userDetailsSql = Self::with(['user', 'vehicle'])
        ->select('telematics_journeys.*',DB::raw('group_concat(id) as journey_ids'))
        ->whereHas('vehicle', function ($query){
                $query->where('is_telematics_enabled','=','1');
            })
        ->where('start_time', '>=', $startDate)
        ->where('start_time', '<=', $endDate)
        ->whereNotNull('end_time');

        if($groupBy == 'user') {
            if (!empty($userFilterValue)) {
                $userDetailsSql = $userDetailsSql->where("user_id",$userFilterValue);
            }

            if ( $regionFilterValue != "" ) {
                if(!is_array($regionFilterValue)) {
                    $regionFilterValue = explode(",", $regionFilterValue);
                }
                $users = User::withTrashed()->whereIn('user_region_id',$regionFilterValue)->get()->pluck('id')->toArray();
                $userDetailsSql = $userDetailsSql->whereIn("user_id", $users);
            }

            $userDetailsSql = $userDetailsSql->groupBy('user_id');
        } else {
            if ( $registrationFilterValue != "" ) {
                $vehicle = Vehicle::withTrashed()->where('registration',$registrationFilterValue)->first();
                $userDetailsSql = $userDetailsSql->where("vehicle_id",$vehicle->id);
            }
            if ( $regionFilterValue != "" ) {
                if(!is_array($regionFilterValue)) {
                    $regionFilterValue = explode(",", $regionFilterValue);
                }
                $vehicle = Vehicle::withTrashed()->whereIn('vehicle_region_id',$regionFilterValue)->get()->pluck('id')->toArray();
                $userDetailsSql = $userDetailsSql->whereIn("vehicle_id",$vehicle);
            }
            $userDetailsSql = $userDetailsSql->groupBy('vehicle_id');
        }

        $userDetailsSql = $userDetailsSql->orderBy('start_time', 'desc');
        $userDetails = $userDetailsSql->get();
        return $userDetails;
    }

    public function getTelematicsDetailsByStartDateAndUserOrVehicle($startDate, $endDate, $groupBy, $userRegions = null, $sortBy = null)
    {
        if(!$userRegions) {
            $userRegions = Auth::user()->regions->lists('id')->toArray();
        } else {
            $userRegions = $userRegions;
        }

        $userDetailsSql = Self::leftJoin('users', 'telematics_journeys.user_id', '=', 'users.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->leftJoin('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                        ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                        ->selectRaw($groupBy.', group_concat(journey_id) as journey_ids, sum(incident_count) as incident_count, vehicles.registration, CASE WHEN users.id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN users.id = 1 THEN "Unknown" ELSE users.last_name END as last_name, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category, CASE WHEN vehicle_types.vehicle_subcategory = "" OR vehicle_types.vehicle_subcategory IS NULL THEN "None" ELSE CONCAT(UCASE(LEFT(vehicle_subcategory, 1)), SUBSTRING(vehicle_subcategory, 2)) END AS vehicle_subcategory, vehicle_regions.name as region_name, vehicle_divisions.name as division_name')
                        ->whereDate('start_time', '>=', $startDate)
                        ->whereDate('start_time', '<=', $endDate)
                        ->where('is_telematics_enabled', '1')
                        ->whereNotNull('end_time');

        if($sortBy) {
            $userDetails = $userDetailsSql->orderBy($sortBy);
        }

        if($groupBy == 'user_id') {
            $userDetails = $userDetailsSql->where(function ($query) use($userRegions) {
                                $query->whereNull('user_region_id')
                                      ->orWhereIn('user_region_id', $userRegions);
                            })
                            ->groupBy('user_id')
                            ->get();
        } else {
            $userDetails = $userDetailsSql->where(function ($query) use($userRegions) {
                                $query->whereNull('vehicle_region_id')
                                      ->orWhereIn('vehicle_region_id', $userRegions);
                            })
                            ->groupBy('vehicle_id')
                            ->get();
        }

        return $userDetails;
    }

    public function details() {
        return $this->hasMany(TelematicsJourneyDetails::class,'telematics_journey_id','id');
    }

}
