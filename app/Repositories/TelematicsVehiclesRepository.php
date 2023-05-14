<?php
namespace App\Repositories;

use Carbon\Carbon as Carbon;
use \Illuminate\Support\Facades\DB;
use Auth;
use App\Custom\Helper\Common;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;

class TelematicsVehiclesRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null, $data = null)
    {
       /* $this->Database = Vehicle::with('nominatedDriver')
        ->leftJoin('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
        ->leftJoin(DB::raw("(select vehicle_id, max(id) as journey_id, max(start_time) as journey_time from telematics_journeys group by vehicle_id) as journey"), 'vehicles.id', '=', 'journey.vehicle_id')
        ->leftJoin(DB::raw("(SELECT MAX(id) AS tjdi, telematics_journey_id FROM telematics_journey_details GROUP BY telematics_journey_id) as td"), 'td.telematics_journey_id', '=', 'journey.journey_id')
        ->leftJoin(DB::raw("(SELECT id,time AS teleamtics_journey_details_time,CONCAT(street,', ',town,', ',post_code) AS teleamtics_journey_details FROM telematics_journey_details) as tdj"), 'tdj.id', '=', 'td.tjdi')
        ->leftJoin(DB::raw("(SELECT id,name AS vehicle_region_name FROM vehicle_regions) as vrn"), 'vrn.id', '=', 'vehicles.vehicle_region_id')
        ->where('is_telematics_enabled','1')
        ->whereNotNull('vehicles.vehicle_region_id')
        ->whereNotIn('vehicles.status',['Archived','Archived - De-commissioned','Archived - Written off'])
        ->select('vehicle_types.*','vehicles.*', 'journey_time', 'journey_id', 'tjdi','teleamtics_journey_details_time', 'teleamtics_journey_details','vehicle_region_name'
        // DB::raw("(select concat(street,', ',town,', ',post_code) from telematics_journey_details where telematics_journey_id = journey_id order by time desc limit 1) as teleamtics_journey_details")
        );
*/
         $this->Database = Vehicle::join('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
         ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
         ->leftjoin('users as nominatedDriver', 'vehicles.nominated_driver', '=', 'nominatedDriver.id')
         //->leftjoin('devices', 'vehicles.id', '=', 'devices.vehicle_id')
         ->where('vehicles.is_telematics_enabled','1')
         ->whereNotNull('vehicles.vehicle_region_id')
         ->whereNotIn('vehicles.status',['Archived','Archived - De-commissioned','Archived - Written off'])
         ->select('vehicles.id','vehicles.registration','vehicle_regions.name as vehicle_region_name', 'vehicles.telematics_ns',
            DB::raw("CASE WHEN vehicles.nominated_driver IS NULL THEN 'Unassigned' ELSE CONCAT(nominatedDriver.first_name, ' ', nominatedDriver.last_name) END as nominatedDriverName"),
            // 'vehicle_types.vehicle_category as vehicle_category',
            DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'), 
            'vehicle_type',
            DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_journey_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_journey_time'),
            DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_location_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_location_time'),
            //DB::raw('DATE_FORMAT(CONVERT_TZ(heartbeat, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as heartbeat'),
            'telematics_latest_journey_id',
            DB::raw("CONCAT(telematics_street,', ',telematics_town,', ',telematics_postcode) AS teleamtics_journey_details"),DB::raw("CASE WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.moving_events'))."') THEN 'Driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.start_events'))."') THEN 'Driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.stopped_events'))."') THEN 'Stopped' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.idling_events'))."') THEN 'Idling' ELSE '' END as telematics_ns_label, CASE WHEN vehicles.telematics_odometer is null THEN 0 ELSE cast((vehicles.telematics_odometer/ 1609.344) as decimal(10,0)) END AS telematics_odometer")
            );

        if ($request != null) {
            if(isset($request->filters)) {
                $filters = json_decode($request->filters, true);
                $registrationFilter = isset($filters['registrationFilter']) ? $filters['registrationFilter'] : null;
                $vehicleTypeFilterValue = isset($filters['vehicleTypeFilterValue']) ? $filters['vehicleTypeFilterValue'] : null;
                $regionFilterValue = isset($filters['regionFilterValue']) ? $filters['regionFilterValue'] : null;
            } else {
                $data = $request->all();
                $registrationFilter = isset($data['registrationFilter']) ? $data['registrationFilter'] : null;
                $vehicleTypeFilterValue = isset($data['vehicleTypeFilterValue']) ? $data['vehicleTypeFilterValue'] : null;
                $regionFilterValue = isset($data['regionFilterValue']) ? $data['regionFilterValue'] : null;
            }
            if ($registrationFilter) {
                $this->Database->where('registration', $registrationFilter);
            }
            if ($vehicleTypeFilterValue) {
                $this->Database->where('vehicle_type_id', $vehicleTypeFilterValue);
            }
            if ($regionFilterValue) {
                $this->Database->where('vehicle_region_id', $regionFilterValue);
            } else {
                $userRegions = Auth::user()->regions->lists('id')->toArray();
                $this->Database->whereIn('vehicle_region_id', $userRegions);
            }
        }
        //$this->Database->select('id','registration','vehicle_region_name','nominated_driver','vehicle_category','vehicle_type','journey_time','teleamtics_journey_details')
        $this->orderBy = [['vehicles.registration', 'asc']];
    }

    public function retrieveVehicleList($request){
        $cl=20;
        $query=Vehicle::join('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
         ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
         ->leftjoin('users as nominatedDriver', 'vehicles.nominated_driver', '=', 'nominatedDriver.id')
         ->where('is_telematics_enabled','1')
         ->whereNotNull('telematics_lat')
        ->whereNotNull('telematics_lon')
         ->whereNotNull('vehicles.vehicle_region_id')
         ->whereNotIn('vehicles.status',['Archived','Archived - De-commissioned','Archived - Written off'])
         ->select('vehicles.id','vehicles.registration','vehicle_regions.name as vehicle_region_name', 'vehicles.telematics_ns',
            DB::raw("CASE WHEN vehicles.nominated_driver IS NULL THEN 'Unassigned driver' ELSE CONCAT(concat ( upper(substring(nominatedDriver.first_name,1,1)), lower(right(nominatedDriver.first_name,length(nominatedDriver.first_name)-1))), ' ', concat ( upper(substring(nominatedDriver.last_name,1,1)), lower(right(nominatedDriver.last_name,length(nominatedDriver.last_name)-1)))) END as nominatedDriverName"),
            'vehicle_types.vehicle_category as vehicle_category','vehicle_type',
            DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_journey_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_journey_time')
            ,DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_location_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_location_time'),
            'telematics_latest_journey_id',
            DB::raw("CONCAT(telematics_street,', ',telematics_town,', ',telematics_postcode) AS teleamtics_journey_details"),DB::raw("CASE WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.moving_events'))."') THEN 'driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.start_events'))."') THEN 'driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.stopped_events'))."') THEN 'stopped' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.idling_events'))."') THEN 'idling' ELSE 'no-status' END as telematics_ns_label")
            );

        if ($request != null) {
            

            $data = $request->all();
               
            $regionFilterValue = isset($data['liveTabRegionFilter']) ? $data['liveTabRegionFilter'] : null;
            $vehicleTypeFilterValue = isset($data['liveTabVehicleTypeFilter']) ? $data['liveTabVehicleTypeFilter'] : null;
            
            //map ui
                if (isset($request->vRegistration) && !empty($request->vRegistration)) {
                    $query->where('registration', $request->vRegistration);
                }

                if(isset($request->filterOnOff)){
                    $filterOnOff=$request->filterOnOff;
                }
            //

           

            if (isset($filterOnOff) && ($filterOnOff==true || $filterOnOff=="true") && $vehicleTypeFilterValue && $regionFilterValue) {
                $query->whereIn('vehicle_type_id', $vehicleTypeFilterValue);
                $query->whereIn('vehicle_region_id', $regionFilterValue);
            }else if (isset($filterOnOff) && ($filterOnOff==false || $filterOnOff=="false")) {
                $userRegions = Auth::user()->regions->lists('id')->toArray();
                $query->whereIn('vehicle_region_id', $userRegions);
            }else{
                return collect([]);
            }
            
            if(isset($request->contentLimit)){
                $cl=$request->contentLimit;
            }
        }
        
        // $result=$query->orderBy('vehicles.registration')->limit($cl)->get();
        $result=$query->orderBy('vehicles.updated_at', 'desc')->limit($cl)->get();
        //echo json_encode($result); exit;
        return $result;
    }

    public function fetchVehicleDetailByVehicle($request){
        $commonHelper = new Common();
        if ($request != null) {
            if(isset($request->filters)) {
                $filters = json_decode($request->filters, true);
                $userFilterValue = isset($filters['userFilterValue']) ? $filters['userFilterValue'] : '';
                $registrationFilterValue = isset($filters['registrationFilterValue']) ? $filters['registrationFilterValue'] : '';
                $regionFilterValue = isset($filters['regionFilterValue']) ? $filters['regionFilterValue'] : '';
                /* $startDate = isset($filters['startDate']) ? $filters['startDate'] : '';
                $endDate = isset($filters['endDate']) ? $filters['endDate'] : ''; */
                $postcodeFilterValue = isset($filters['postcodeFilterValue']) ? $filters['postcodeFilterValue'] : '';
            } else {
                $data = $request->all();
                $userFilterValue = isset($data['userFilterValue']) ? $data['userFilterValue'] : '';
                $registrationFilterValue = isset($data['registrationFilterValue']) ? $data['registrationFilterValue'] : '';
                $regionFilterValue = isset($data['regionFilterValue']) ? $data['regionFilterValue'] : '';
                /* $startDate = isset($data['startDate']) ? $data['startDate'] : '';
                $endDate = isset($data['endDate']) ? $data['endDate'] : ''; */
                $postcodeFilterValue = isset($data['postcodeFilterValue']) ? $data['postcodeFilterValue'] : '';
            }
            $requestedVehicleId=$request->vehicleId;
           /*  if ($startDate != '' && $endDate != '') {
                $startDate = $commonHelper->convertBstToUtc($startDate.' 00:00:00');
                $endDate = $commonHelper->convertBstToUtc($endDate.' 23:59:59');
            } else {
                $startDate = Carbon::now()->subYear(5)->firstOfMonth()->format('Y-m-d H:i:s');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
            } */
            
            $telematicsProvider = env("TELEMATICS_PROVIDER");

            //$query = DB::table(DB::raw('telematics_journeys as j force index (telematics_journeys_start_time_index)'))
            $query=DB::table('vehicles as v')
            //$this->Database = DB::table('telematics_journeys as j')
            ->selectRaw('"'.$telematicsProvider.'" as provider,j.id,v.id as vehicleId,vt.vehicle_type,CASE WHEN vt.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category,count(j.id) as journeyCount,vregion.name as vehicle_region_name,v.telematics_ns,CONCAT(v.telematics_street,", ",v.telematics_town,", ",v.telematics_postcode) AS last_telematics_location,v.telematics_latest_location_time,
                SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration,CASE WHEN j.engine_duration is null THEN 0 WHEN j.engine_duration = 0 THEN 0 ELSE SUM(j.engine_duration) END AS engine_duration,
                CASE WHEN j.incident_count is null THEN 0 WHEN j.incident_count = 0 THEN 0 ELSE SUM(j.incident_count) END AS incident_count,
                SUM(j.fuel) as fuel, SUM(j.co2) as co2,
                CASE WHEN j.gps_distance is null THEN 0 ELSE cast((SUM(j.gps_distance* 0.00062137)) as decimal(10,2)) END AS gps_distance,
                CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph,
                CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph,
                CASE WHEN odometer_start is null THEN 0 ELSE cast((odometer_start/ 1609.344) as decimal(10,0)) END AS journeyStart,
                CASE WHEN odometer_end is null THEN 0 ELSE cast((odometer_end/ 1609.344) as decimal(10,0)) END AS journeyEnd,
                CASE WHEN j.fuel is null THEN 0 WHEN j.fuel = "0.00" THEN 0 ELSE cast(((j.gps_distance* 0.00062137)/ (j.fuel* 0.264172)) as decimal(10,2)) END AS mpg,
                CASE WHEN v.vehiclefuelsum is null THEN 0 WHEN v.vehiclefuelsum = "0.00" THEN 0 ELSE cast(((v.vehicledistancesum * 0.00062137)/ (v.vehiclefuelsum * 0.264172)) as decimal(10,2)) END AS mpgExpected,
            j.id AS journey,v.vehiclefuelsum,v.vehicledistancesum,

            CASE WHEN v.nominated_driver IS NULL THEN "Unassigned driver" ELSE CONCAT(u.first_name, " ", u.last_name) END as user,

            j.vrn AS registration,
            DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as start_time_edited,
            DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") AS end_time_edited')
            ->leftjoin('telematics_journeys as j','j.vehicle_id', '=', 'v.id')
            // ->join('users as u','u.id','=','j.user_id')
            ->leftjoin('users as u','u.id','=','v.nominated_driver')
            //->join('vehicles as v','v.id','=','j.vehicle_id')
            ->join('vehicle_types as vt', 'v.vehicle_type_id', '=', 'vt.id')
            ->join('vehicle_regions as vregion','v.vehicle_region_id', '=', 'vregion.id')
            //->whereBetween("start_time",[$startDate,$endDate])
            ->where('v.is_telematics_enabled','=','1')
            ->where('v.id',$requestedVehicleId);
            if($telematicsProvider == 'webfleet') {
                $query = $query->where('is_details_added','=',1);
            }

            if ($userFilterValue != '') {
                $query->where("j.user_id",$userFilterValue);
            }
            if ($registrationFilterValue != '') {
                $query->where("j.vrn", $registrationFilterValue);
            }

            if ($regionFilterValue != '') {
                $query->where("v.vehicle_region_id", $regionFilterValue);
            } else {
                if(Auth::check()) {
                    $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
                    $query->whereIn("v.vehicle_region_id", $regionFilterValue);
                }
            }

            if ($postcodeFilterValue != '') {
                $query->join('telematics_journey_details as jd','j.id','=','jd.telematics_journey_id');
                $query->whereRaw("LOWER(REPLACE(jd.post_code, ' ', '')) = LOWER(REPLACE('".$postcodeFilterValue."', ' ', ''))");
            }

            //$result=$query->groupBy('j.id')->orderBy('start_time','desc')->get();
            $result=$query->groupBy('j.vehicle_id')->orderBy('start_time','desc')->first();
            $vehicleStatus = '';
            if($result) {
                $movingEvents = config('config-variables.moving_events');
                $startEvents = config('config-variables.start_events');
                $stoppedEvents = config('config-variables.stopped_events');
                $idlingEvents = config('config-variables.idling_events');
                if(in_array($result->telematics_ns,$movingEvents) || in_array($result->telematics_ns,$startEvents)){
                    $vehicleStatus='Driving';
                }else if(in_array($result->telematics_ns,$stoppedEvents)){
                    $vehicleStatus='Stopped';
                }else if(in_array($result->telematics_ns,$idlingEvents)){
                    $vehicleStatus='Idling';
                } 
                $result->vehicleStatus=$vehicleStatus;
            

                $speed="NA";
                $last_update = '';
                if($telematicsProvider=='webfleet'){
                    $last_update=Carbon::parse($result->telematics_latest_location_time)->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
                }else{

                    $last_update=Carbon::parse($result->telematics_latest_location_time)->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
                    /*$vehicle = Vehicle::where('registration', $result->registration)->first();
                    $latestTelematics = $vehicle->lastTelematicsJourneyDetails()->first(['speed', 'time']);
                    // $latestTelematics = TelematicsJourneyDetails::where('vrn',$result->registration)->whereNotNull('post_code')->orderBy('time','DESC')->first(['speed', 'time']);
                    if(isset($latestTelematics)) {
                        $speed=setMpsToMph($latestTelematics->speed).' MPH';
                        $last_update=Carbon::parse($latestTelematics->time)->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
                    }*/
                }
                $result->speed=$speed;
                $result->last_update=$last_update;
                if($result->engine_duration!= 0){
                    $result->total_driving_time = readableTimeFomat($result->engine_duration);
                }else{
                    $result->total_driving_time=0;
                }
            }
            return $result;
        }
    }
}