<?php
namespace App\Repositories;

use Carbon\Carbon as Carbon;
use App\Custom\Helper\Common;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use Auth;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;

class TelematicsJourneyIncidentsRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null)
    {
        $commonHelper = new Common();
        if ($request != null) {
            $this->Database = DB::table('telematics_journeys');
            $incidentData = [];
            $userRegions = Auth::user()->regions->lists('id')->toArray();
            $systemUserId = env("SYSTEM_USER_ID");

            if(isset($request->filters)) {
                $filters = json_decode($request->filters, true);
                $incidentTypeFilterValue = isset($filters['incidentTypeFilterValue']) ? $filters['incidentTypeFilterValue'] : null;
                $userFilterValue = isset($filters['userFilterValue']) ? $filters['userFilterValue'] : null;
                $registrationFilterValue = isset($filters['registrationFilterValue']) ? $filters['registrationFilterValue'] : null;
                $regionFilterValue = isset($filters['regionFilterValue']) ? $filters['regionFilterValue'] : null;
                $startDate = isset($filters['startDate']) ? $filters['startDate'] : null;
                $endDate = isset($filters['endDate']) ? $filters['endDate'] : null;
            } else {
                $data = $request->all();
                $incidentTypeFilterValue = isset($data['incidentTypeFilterValue']) ? $data['incidentTypeFilterValue'] : null;
                $userFilterValue = isset($data['userFilterValue']) ? $data['userFilterValue'] : null;
                $registrationFilterValue = isset($data['registrationFilterValue']) ? $data['registrationFilterValue'] : null;
                $regionFilterValue = isset($data['regionFilterValue']) ? $data['regionFilterValue'] : null;
                $startDate = isset($data['startDate']) ? $data['startDate'] : null;
                $endDate = isset($data['endDate']) ? $data['endDate'] : null;
            }

            if ($startDate && $endDate) {
                //$startDate = request()->get('startDate').' 00:00:00';
                //$endDate = request()->get('endDate').' 23:59:59';
                $startDate = $commonHelper->convertBstToUtc($startDate);
                $endDate = $commonHelper->convertBstToUtc($endDate);
            } else {
                $startDate = Carbon::now()->toDateString().' 00:00:00';
                $endDate = Carbon::now()->toDateString().' 23:59:59';
            }
            
            $incidentsData = [];
            //$journeyIds = array_merge($journeyIds,['0']);//this line is added because journeyid for heartbeat incident is 0;
            //$this->Database = DB::table('telematics_journey_details')
            $this->Database = DB::table(DB::raw('telematics_journey_details force index (telematics_journey_details_time_index)'))
                                    ->join('telematics_journeys as telematics_journeys', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                                    ->join('users as user', 'telematics_journeys.user_id', '=', 'user.id')
                                    ->join('vehicles as vehicle', 'telematics_journeys.vehicle_id', '=', 'vehicle.id')
                                    ->leftjoin('users as nominatedDriver', 'vehicle.nominated_driver', '=', 'nominatedDriver.id')
                                    ->where('vehicle.is_telematics_enabled','1')
                                    ->whereNull('telematics_journey_details.deleted_at')
                                    ->where('ns', '!=', 'tm8.dfb2.spd')
                                    ->whereBetween("time",[$startDate,$endDate]);

            if ($incidentTypeFilterValue) {
                if ($incidentTypeFilterValue == 'harsh.cornering') {
                    $this->Database = $this->Database->whereIn('ns', ['tm8.dfb2.cnrl.l','tm8.dfb2.cnrr.l']);
                }
                else{
                    $this->Database = $this->Database->where('ns', $incidentTypeFilterValue);
                }
            } else {
                $incidentTypes = array_keys(config('config-variables.telematics_incidents'));
                $this->Database = $this->Database->whereIn('ns', $incidentTypes);
            }

            if ($userFilterValue != '') {
                if($userFilterValue==$systemUserId){
                    $this->Database->where("telematics_journeys.user_id",$userFilterValue)->whereNull('vehicle.nominated_driver');
                }else{
                    $this->Database->where(function ($query) use($userFilterValue,$systemUserId) {
                                                $query->where("telematics_journeys.user_id","=",$userFilterValue)
                                                      ->orWhere(function ($query) use($userFilterValue,$systemUserId) {
                                                            $query->where("telematics_journeys.user_id","=",$systemUserId)
                                                                  ->Where('vehicle.nominated_driver', '=', $userFilterValue);
                                                        });
                                            });
                }
            }

            if ($registrationFilterValue) {
                $this->Database->where("telematics_journey_details.vrn", $registrationFilterValue);
            }
            
            if ($regionFilterValue) {
                $this->Database->where("vehicle.vehicle_region_id", $regionFilterValue);
            } else {
                $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
                $this->Database->whereIn("vehicle.vehicle_region_id", $regionFilterValue);
            }

            $this->Database = $this->Database->selectRaw('vehicle.registration,
               
                CASE WHEN telematics_journeys.end_time IS NULL OR user.id = "'.$systemUserId.'" THEN "'.config('config-variables.telematicsSystemUserVisibleName.FULL').'" 
                ELSE CONCAT(user.first_name, " ", user.last_name) END AS user,

                DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as date_edited,
                CASE WHEN telematics_journey_details.post_code is null THEN CONCAT(" ", telematics_journey_details.lat,",", telematics_journey_details.lon) ELSE CONCAT_WS(" ", telematics_journey_details.street, telematics_journey_details.town ,telematics_journey_details.post_code) END as location,lat as latitude,lon as longitude,telematics_journey_details.id as journeyIncidentIndex,telematics_journey_details.telematics_journey_id as journey_id,
                telematics_journey_details.idle_duration as idleDurationSort,
                CASE WHEN telematics_journey_details.idle_duration IS NOT NULL THEN
                CONCAT(
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 1), ":", -1) = "00" THEN "" ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 1), ":", -1), " hr ") END,
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 2), ":", -1) = "00" THEN "" ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 2), ":", -1), " min ") END,
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 3), ":", -1) = "00" THEN "" ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(SEC_TO_TIME(telematics_journey_details.idle_duration), ":", 3), ":", -1), " sec") END
                ) ELSE "N/A" END AS  idleDuration,
                    
                    CONCAT(CASE WHEN speed IS NULL THEN 0 ELSE ROUND(speed * 2.236936, 0) END, " MPH") AS vehicle_speed,
                    CASE WHEN speed IS NULL THEN 0 ELSE ROUND(speed * 2.236936, 0) END AS vehicle_speed_sort,
                    CONCAT(CASE WHEN street_speed IS NULL THEN 0 ELSE
                    CASE WHEN FLOOR(street_speed * 2.236936) > 0 THEN
                        CASE WHEN FLOOR((street_speed * 2.236936) % 10) >= 5 THEN (FLOOR((street_speed * 2.236936)/ 10) + 1) * 10
                        ELSE FLOOR((street_speed * 2.236936) / 10) * 10 END
                    ELSE 0 END END, " MPH") AS speed_limit,
                    CASE WHEN street_speed IS NULL THEN 0 ELSE
                    CASE WHEN FLOOR(street_speed * 2.236936) > 0 THEN
                        CASE WHEN FLOOR((street_speed * 2.236936) % 10) >= 5 THEN (FLOOR((street_speed * 2.236936)/ 10) + 1) * 10
                        ELSE FLOOR((street_speed * 2.236936) / 10) * 10 END
                    ELSE 0 END END AS speed_limit_sort,

                    CASE WHEN ns = "tm8.dfb2.acc.l" THEN "harsh_acceleration.png"
                    WHEN ns = "tm8.dfb2.cnrl.l" THEN "harsh_left_cornering.png"
                    WHEN ns = "tm8.dfb2.cnrr.l" THEN "harsh_right_cornering.png"
                    WHEN ns = "tm8.dfb2.dec.l" THEN "harsh_braking.png"
                    WHEN ns = "tm8.dfb2.rpm" THEN "rpm_over_threshold.png"
                    WHEN ns = "tm8.dfb2.spdinc" THEN "speeding_over_threshold.png"
                    WHEN ns = "tm8.gps.idle.end" THEN "idling.png"
                    WHEN ns = "tm8.gps.idle.start" THEN "idling.png"
                    WHEN ns = "tm8.gps.idle.ongoing" THEN "idling.png"
                    WHEN ns = "tm8.gps.exces.idle" THEN "idling.png"
                    WHEN ns = "tm8.fnol" THEN "crash.png"
                    ELSE "location.png" END as icon,
                    CASE WHEN ns = "tm8.dfb2.acc.l" THEN "Harsh Acceleration"
                    WHEN ns = "tm8.dfb2.cnrl.l" THEN "Harsh Left Cornering"
                    WHEN ns = "tm8.dfb2.cnrr.l" THEN "Harsh Right Cornering"
                    WHEN ns = "tm8.dfb2.dec.l" THEN "Harsh Braking"
                    WHEN ns = "tm8.dfb2.rpm" THEN "RPM"
                    WHEN ns = "tm8.dfb2.spdinc" THEN "Speeding"
                    WHEN ns = "tm8.gps.idle.end" THEN "Idling"
                    WHEN ns = "tm8.fnol" THEN "FNOL"

                    END as incident_type,
                    1 as count
                ');
            $this->orderBy = [['telematics_journey_details.time', 'DESC']];
            $this->Database->take(25000);
            
        }

        //return $journeyData;
    }

}
