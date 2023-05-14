<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class UserTelematicsJourney extends Model
{
    protected $table = 'user_telematics_journeys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "journey_id",
        "registration",
        "start_lat",
        "start_lon",
        "start_time",
        "start_street",
        "start_town",
        "start_post_code",
        "end_lat",
        "end_lon",
        "end_time",
        "end_street",
        "end_town",
        "end_post_code",
        ];

    /**
     * Get the vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->select("id", "email", "first_name", "last_name")->latest();
    }

   // public function getUserTelematicslDetailsByStartDate($startDate, $endDate, $fieldName, $fieldValue)
    public function getUserTelematicslDetailsByStartDate($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue)
    {
        $userDetailsSql = UserTelematicsJourney::with(['user', 'vehicle'])
        ->select('user_telematics_journeys.*',DB::raw('group_concat(journey_id) as journey_ids'))
                         ->whereHas('vehicle', function ($query) {
                                $query->where('is_telematics_enabled','=','1');
                            })
                         ->whereDate('start_time', '>=', $startDate)
                         ->whereDate('start_time', '<=', $endDate);
        if (!empty($userFilterValue)) {
            $userDetailsSql = $userDetailsSql->where("user_id",$userFilterValue);
        }
        if ( $registrationFilterValue != "" ) {
            $vehicle = Vehicle::withTrashed()->where('registration',$registrationFilterValue)->first();
            $userDetailsSql = $userDetailsSql->where("vehicle_id",$vehicle->id);
        }
        if ( $regionFilterValue != "" ) {
            $vehicle = Vehicle::withTrashed()->where('vehicle_region_id',$regionFilterValue)->first();
            $userDetailsSql = $userDetailsSql->where("vehicle_id",$vehicle->id);
        }
        $userDetails = $userDetailsSql->orderBy('start_time', 'desc')
                         ->groupBy(['user_id', 'vehicle_id'])
                         ->get();
        return $userDetails;
    }
}
