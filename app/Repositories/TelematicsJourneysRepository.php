<?php
namespace App\Repositories;

use Carbon\Carbon as Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Helper\Common;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Models\ZoneAlerts;
use App\Services\TelematicsService;
use App\Models\Alerts;
use Auth;

class TelematicsJourneysRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null, $data = null)
    {
        $commonHelper = new Common();
        if ($request != null) {
            if(isset($request->filters)) {
                $filters = json_decode($request->filters, true);
                $userFilterValue = isset($filters['userFilterValue']) ? $filters['userFilterValue'] : '';
                $registrationFilterValue = isset($filters['registrationFilterValue']) ? $filters['registrationFilterValue'] : '';
                $regionFilterValue = isset($filters['regionFilterValue']) ? $filters['regionFilterValue'] : '';
                $startDate = isset($filters['startDate']) ? $filters['startDate'] : '';
                $endDate = isset($filters['endDate']) ? $filters['endDate'] : '';
                $postcodeFilterValue = isset($filters['postcode']) ? $filters['postcode'] : '';
            } else {
                $data = $request->all();
                $userFilterValue = isset($data['userFilterValue']) ? $data['userFilterValue'] : '';
                $registrationFilterValue = isset($data['registrationFilterValue']) ? $data['registrationFilterValue'] : '';
                $regionFilterValue = isset($data['regionFilterValue']) ? $data['regionFilterValue'] : '';
                $startDate = isset($data['startDate']) ? $data['startDate'] : '';
                $endDate = isset($data['endDate']) ? $data['endDate'] : '';
                $postcodeFilterValue = isset($data['postcode']) ? $data['postcode'] : '';
            }

            if ($startDate != '' && $endDate != '') {
                $startDate = $commonHelper->convertBstToUtc($startDate);
                $endDate = $commonHelper->convertBstToUtc($endDate);
            } else {
                $startDate = Carbon::now()->toDateString().' 00:00:00';
                $endDate = Carbon::now()->toDateString().' 23:59:59';
            }
            $telematicsProvider = env("TELEMATICS_PROVIDER");
            $systemUserId = env("SYSTEM_USER_ID");

            $this->Database = DB::table(DB::raw('telematics_journeys as j force index (telematics_journeys_start_time_index)'))
            //$this->Database = DB::table('telematics_journeys as j')
            ->selectRaw('"'.$telematicsProvider.'" as provider,j.id,j.dallas_key,
                SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration,
                CASE WHEN j.incident_count IS NULL OR j.incident_count = 0 THEN 0 ELSE j.incident_count END AS incident_count,
                CASE WHEN (j.fuel = 0 and j.gps_distance < 1609.34) THEN "< '.env('MIN_JOURNEY_FUEL').' " ELSE fuel END AS fuel,
                CASE WHEN (j.co2 = 0 and j.gps_distance < 1609.34) THEN "< '.env('MIN_JOURNEY_CO2').' " ELSE co2 END AS co2,
                CASE WHEN j.gps_distance is null THEN 0 ELSE cast((j.gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance,
                CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph,
                CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph,
                CASE WHEN odometer_start is null THEN 0 ELSE cast((odometer_start/ 1609.344) as decimal(10,0)) END AS journeyStart,
                CASE WHEN odometer_end is null THEN 0 ELSE cast((odometer_end/ 1609.344) as decimal(10,0)) END AS journeyEnd,
                CASE WHEN j.fuel is null THEN 0 WHEN j.fuel = "0.00" THEN 0 ELSE cast(((j.gps_distance* 0.00062137)/ (j.fuel* 0.264172)) as decimal(10,2)) END AS mpg,
                CASE WHEN v.vehiclefuelsum is null THEN 0 WHEN v.vehiclefuelsum = "0.00" THEN 0 ELSE cast(((v.vehicledistancesum * 0.00062137)/ (v.vehiclefuelsum * 0.264172)) as decimal(10,2)) END AS mpgExpected,
            j.id AS journey,v.vehiclefuelsum,v.vehicledistancesum,

            CASE WHEN j.end_time IS NULL OR u.id = "'.$systemUserId.'" THEN "'.config('config-variables.telematicsSystemUserVisibleName.FULL').'" 
            ELSE CONCAT(u.first_name, " ", u.last_name) END AS user,

            j.vrn AS registraion,
            DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as start_time_edited,
            DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") AS end_time_edited')
            ->join('users as u','u.id','=','j.user_id')
            ->join('vehicles as v','v.id','=','j.vehicle_id')
            ->leftjoin('users as nominatedDriver', 'v.nominated_driver', '=', 'nominatedDriver.id')
            ->whereNull('j.deleted_at')
            ->whereBetween("start_time",[$startDate,$endDate])
            ->where('v.is_telematics_enabled','=','1')
            // ->where('j.fuel','!=',0)
            // ->where('j.gps_distance','!=',0)
            // ->where('j.gps_idle_duration','!=',0)
            ;
            if($telematicsProvider == 'webfleet') {
                $this->Database = $this->Database->where('is_details_added','=',1);
            }

            if ($userFilterValue != '') {
                if($userFilterValue==$systemUserId){
                    $this->Database->where("j.user_id",$userFilterValue)->whereNull('v.nominated_driver');
                }else{
                    //$this->Database->where("v.nominated_driver",$userFilterValue);
                    $this->Database->where(function ($query) use($userFilterValue,$systemUserId) {
                                        $query->where("j.user_id","=",$userFilterValue)
                                              ->orWhere(function ($query) use($userFilterValue,$systemUserId) {
                                                    $query->where("j.user_id","=",$systemUserId)
                                                          ->Where('v.nominated_driver', '=', $userFilterValue);
                                                });
                                    });
                    /*$this->Database->where(function ($query) use($userFilterValue) {
                        $query->where("j.user_id","=",$userFilterValue)
                              ->orWhere('v.nominated_driver', '=', $userFilterValue);
                    });*/
                }
            }
            if ($registrationFilterValue != '') {
                $this->Database->where("j.vrn", $registrationFilterValue);
            }

            if ($regionFilterValue != '') {
                $this->Database->where("v.vehicle_region_id", $regionFilterValue);
            } else {
                if(Auth::check()) {
                    $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
                    $this->Database->whereIn("v.vehicle_region_id", $regionFilterValue);
                }
            }

            if ($postcodeFilterValue != '') {
                $this->Database->join('telematics_journey_details as jd','j.id','=','jd.telematics_journey_id');
                $this->Database->whereRaw("LOWER(REPLACE(jd.post_code, ' ', '')) = LOWER(REPLACE('".$postcodeFilterValue."', ' ', ''))");
            }

            $this->Database->groupBy('j.id');
            $this->orderBy = [['start_time', 'DESC']];
            $this->Database->take(25000);
        }
    }

    public function fetchVehicleJourneyDetail($request){
        $commonHelper = new Common();
        $regionFilterValue='';
        $filterOpt=$request->filter;
        if($request->startDate && $request->endDate){
            $startDate = $commonHelper->convertBstToUtc($request->startDate);
            $endDate = $commonHelper->convertBstToUtc($request->endDate);
        } else {
            $startDate = Carbon::now()->subYear(5)->firstOfMonth()->format('Y-m-d H:i:s');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
        }
        $telematicsProvider = env("TELEMATICS_PROVIDER");

        $query = DB::table(DB::raw('telematics_journeys as j force index (telematics_journeys_start_time_index)'))
        //$this->Database = DB::table('telematics_journeys as j')
        ->selectRaw('"'.$telematicsProvider.'" as provider,j.id,v.registration as vehicleRegistration,count(j.id) as journeyCount,
        CASE WHEN j.incident_count IS NULL OR j.incident_count = 0 THEN 0 ELSE j.incident_count END AS incident_count,
            CASE WHEN j.gps_distance is null THEN 0 ELSE cast((j.gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance,j.id AS journey,DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as start_time_edited,DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") AS end_time_edited,CASE WHEN j.engine_duration is null THEN 0 WHEN j.engine_duration = 0 THEN 0 ELSE SUM(j.engine_duration) END AS engine_duration,cast(SUM(j.fuel) as decimal(10,2)) as fuel,SUM(j.co2) as co2')
        ->join('users as u','u.id','=','j.user_id')
        ->join('vehicles as v','v.id','=','j.vehicle_id')
        ->whereNull('j.deleted_at')
        ->whereBetween("start_time",[$startDate,$endDate])
        ->where('v.is_telematics_enabled','=','1')
        ->where('j.vehicle_id',$request->vehicleId);

        if($telematicsProvider == 'webfleet') {
            $query= $query->where('is_details_added','=',1);
        }

        if ($regionFilterValue != '') {
            $query->where("v.vehicle_region_id", $regionFilterValue);
        } else {
            if(Auth::check()) {
                $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
                $query->whereIn("v.vehicle_region_id", $regionFilterValue);
            }
        }
        
        $result=$query->groupBy('j.id')->orderBy('start_time','asc')->limit(25000)->get();
        return $result;
    }

    public function fetchVehicleJourneyDetailLatLong($request){
        $telematicsJourneyDetail=TelematicsJourneyDetails::where('telematics_journey_id',$request->journey_id)->select('id','vrn','lat','lon')->get();
        return $telematicsJourneyDetail;
    }

    public function create($telematicsJourneysData) {
        if (env('TELEMATICS_PROVIDER') == 'teletrac') {
            $telematicsJourney = TelematicsJourneys::where('vrn',$telematicsJourneysData['vrn'])->where('journey_id',$telematicsJourneysData['journey_id'])->first();
        }
        else{
            $telematicsJourney = TelematicsJourneys::where('vrn',$telematicsJourneysData['vrn'])->where('journey_id',$telematicsJourneysData['journey_id'])->where('uid',$telematicsJourneysData['uid'])->first();
        }
        //$telematicsJourney = TelematicsJourneys::where('vrn',$telematicsJourneysData['vrn'])->where('journey_id',$telematicsJourneysData['journey_id'])->where('uid',$telematicsJourneysData['uid'])->first();
        if($telematicsJourney){
            return $telematicsJourney;
        }
        $telematicsJourney = new TelematicsJourneys();
        //print_r($telematicsJourney);exit;
        //$telematicsJourneysData->ns = isset($telematicsJourneysData['ns'])?$telematicsJourneysData['ns']:null;
        $telematicsJourney->vrn = isset($telematicsJourneysData['vrn'])?$telematicsJourneysData['vrn']:null;
        $telematicsJourney->user_id = 1;
        $vehicle = Vehicle::where('registration',$telematicsJourney->vrn)->first();
        $telematicsJourney->vehicle_id = $vehicle->id;

        /*
        * FLEE-6835 - Farzan
        */
        if($vehicle->nominated_driver) {
            $telematicsJourney->user_id = $vehicle->nominated_driver;
        }

        $telematicsJourney->journey_id = isset($telematicsJourneysData['journey_id'])?$telematicsJourneysData['journey_id']:null;
        $telematicsJourney->raw_json = json_encode($telematicsJourneysData);
        $telematicsJourney->end_lat = isset($telematicsJourneysData['end_lat'])?$telematicsJourneysData['end_lat']:null;
        $telematicsJourney->end_lon = isset($telematicsJourneysData['end_lon'])?$telematicsJourneysData['end_lon']:null;
        $telematicsJourney->end_time = isset($telematicsJourneysData['end_time'])?Carbon::parse($telematicsJourneysData['end_time'])->setTimezone('UTC'):null;
        $telematicsJourney->odometer = isset($telematicsJourneysData['odometer'])?$telematicsJourneysData['odometer']:null;
        // $telematicsJourney->odo_source = isset($telematicsJourneysData['odo_source'])?$telematicsJourneysData['odo_source']:null;
        $telematicsJourney->start_lat = isset($telematicsJourneysData['start_lat'])?$telematicsJourneysData['start_lat']:(isset($telematicsJourneysData['lat'])?$telematicsJourneysData['lat']:null);
        $telematicsJourney->start_lon = isset($telematicsJourneysData['start_lon'])?$telematicsJourneysData['start_lon']:(isset($telematicsJourneysData['lon'])?$telematicsJourneysData['lon']:null);
        $telematicsJourney->start_time = isset($telematicsJourneysData['start_time'])?Carbon::parse($telematicsJourneysData['start_time'])->setTimezone('UTC'):(isset($telematicsJourneysData['time'])?Carbon::parse($telematicsJourneysData['time'])->setTimezone('UTC'):null);
        $telematicsJourney->engine_duration = isset($telematicsJourneysData['engine_duration'])?$telematicsJourneysData['engine_duration']:'0';
        $telematicsJourney->gps_idle_duration = isset($telematicsJourneysData['gps_idle_duration'])?$telematicsJourneysData['gps_idle_duration']:'0';
        $telematicsJourney->fuel = isset($telematicsJourneysData['fuel'])?$telematicsJourneysData['fuel']:null;
        $telematicsJourney->co2 = isset($telematicsJourneysData['co2'])?$telematicsJourneysData['co2']:null;
        $telematicsJourney->gps_distance = isset($telematicsJourneysData['can_distance'])?$telematicsJourneysData['can_distance']:'0';
        $telematicsJourney->gps_odo = isset($telematicsJourneysData['gps_odo'])?$telematicsJourneysData['gps_odo']:'0';
        $telematicsJourney->end_street = isset($telematicsJourneysData['end_street'])?$telematicsJourneysData['end_street']:null;
        $telematicsJourney->end_town = isset($telematicsJourneysData['end_town'])?$telematicsJourneysData['end_town']:null;
        $telematicsJourney->end_post_code = isset($telematicsJourneysData['end_postcode'])?$telematicsJourneysData['end_postcode']:null;
        $telematicsJourney->start_street = isset($telematicsJourneysData['street'])?$telematicsJourneysData['street']:null;
        $telematicsJourney->start_town = isset($telematicsJourneysData['town'])?$telematicsJourneysData['town']:null;
        $telematicsJourney->start_post_code = isset($telematicsJourneysData['postcode'])?$telematicsJourneysData['postcode']:null;
        $telematicsJourney->uid = isset($telematicsJourneysData['uid'])?$telematicsJourneysData['uid']:null;
        $telematicsJourney->make = isset($telematicsJourneysData['make'])?$telematicsJourneysData['make']:null;
        $telematicsJourney->model = isset($telematicsJourneysData['model'])?$telematicsJourneysData['model']:null;
                //print_r($telematicsJourney);exit;
        $telematicsJourney->save();
        return $telematicsJourney;
    }

    public function update($telematicsJourneysData)
    {

       //dd($telematicsJourneysData);

       // dd(Carbon::parse($data['next_regular_check']));
        if (env('TELEMATICS_PROVIDER') == 'teletrac') {
            $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$telematicsJourneysData['vrn'],'journey_id'=>$telematicsJourneysData['journey_id']])->first();
        }
        else{
            $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$telematicsJourneysData['vrn'],'journey_id'=>$telematicsJourneysData['journey_id'],'uid'=>$telematicsJourneysData['uid']])->first();
        }
        if(!$telematicsJourneys) {
            return $telematicsJourneys;
        }

       // $telematicsJourneys->ns = isset($telematicsJourneysData['ns'])?$telematicsJourneysData['ns']:null;
        $telematicsJourneys->vrn = isset($telematicsJourneysData['vrn'])?$telematicsJourneysData['vrn']:null;
        $telematicsJourneys->journey_id = isset($telematicsJourneysData['journey_id'])?$telematicsJourneysData['journey_id']:null;
        $telematicsJourneys->raw_json = json_encode($telematicsJourneysData);
        $telematicsJourneys->end_lat = isset($telematicsJourneysData['end_lat'])?$telematicsJourneysData['end_lat']:(isset($telematicsJourneysData['lat'])?$telematicsJourneysData['lat']:null);
        $telematicsJourneys->end_lon = isset($telematicsJourneysData['end_lon'])?$telematicsJourneysData['end_lon']:(isset($telematicsJourneysData['lon'])?$telematicsJourneysData['lon']:null);
        if (isset($telematicsJourneysData['end_time'])) {
            $telematicsJourneys->end_time = Carbon::parse($telematicsJourneysData['end_time'])->setTimezone('UTC');
        }
        $telematicsJourneys->odometer = isset($telematicsJourneysData['odometer'])?$telematicsJourneysData['odometer']:null;
        // $telematicsJourneys->odo_source = isset($telematicsJourneysData['odo_source'])?$telematicsJourneysData['odo_source']:null;
        $telematicsJourneys->start_lat = isset($telematicsJourneysData['start_lat'])?$telematicsJourneysData['start_lat']:(isset($telematicsJourneysData['lat'])?$telematicsJourneysData['lat']:null);
        $telematicsJourneys->start_lon = isset($telematicsJourneysData['start_lon'])?$telematicsJourneysData['start_lon']:(isset($telematicsJourneysData['lon'])?$telematicsJourneysData['lon']:null);
        $telematicsJourneys->start_time = isset($telematicsJourneysData['start_time'])?Carbon::parse($telematicsJourneysData['start_time'])->setTimezone('UTC'):(isset($telematicsJourneysData['time'])?Carbon::parse($telematicsJourneysData['time'])->setTimezone('UTC'):null);
        $telematicsJourneys->engine_duration = isset($telematicsJourneysData['engine_duration'])?$telematicsJourneysData['engine_duration']:'0';
        if (isset($telematicsJourneysData['gps_idle_duration'])) {
            $telematicsJourneys->gps_idle_duration = $telematicsJourneysData['gps_idle_duration'];
        }
        if (isset($telematicsJourneysData['fuel'])) {
            $telematicsJourneys->fuel = $telematicsJourneysData['fuel'];
        }
        if (isset($telematicsJourneysData['co2'])) {
            $telematicsJourneys->co2 = $telematicsJourneysData['co2'];
        }
        if (isset($telematicsJourneysData['can_distance'])) {
            // $telematicsJourneys->gps_distance = $telematicsJourneysData['gps_distance'];
            $telematicsJourneys->gps_distance = $telematicsJourneysData['can_distance'];
        }
        $telematicsJourneys->gps_odo = isset($telematicsJourneysData['gps_odo'])?$telematicsJourneysData['gps_odo']:'0';
        $telematicsJourneys->end_street = isset($telematicsJourneysData['end_street'])?$telematicsJourneysData['end_street']:null;
        $telematicsJourneys->end_town = isset($telematicsJourneysData['end_town'])?$telematicsJourneysData['end_town']:null;
        $telematicsJourneys->end_post_code = isset($telematicsJourneysData['end_postcode'])?$telematicsJourneysData['end_postcode']:null;
        $telematicsJourneys->uid = isset($telematicsJourneysData['uid'])?$telematicsJourneysData['uid']:null;
        $telematicsJourneys->make = isset($telematicsJourneysData['make'])?$telematicsJourneysData['make']:null;
        $telematicsJourneys->model = isset($telematicsJourneysData['model'])?$telematicsJourneysData['model']:null;

        // $telematicsJourneys->incident_count = $this->getTotalIncidentCountOfJourney($telematicsJourneys->id);
        $calculatedFields = $this->getCalculatedFieldsOfJourney($telematicsJourneys->id);
        $telematicsJourneys->max_speed = $calculatedFields['maxspeed'];
        $telematicsJourneys->avg_speed = $calculatedFields['avgspeed'];
        //$telematicsJourneys->odometer_start = $calculatedFields['odoStart'];
        //$telematicsJourneys->odometer_end = $calculatedFields['odoEnd'];
        if (isset($telematicsJourneysData['odo_source']) && ($telematicsJourneysData['odo_source'] == 1 || (isset($telematicsJourneysData['can_odo']) && $telematicsJourneysData['can_odo'] != '' && $telematicsJourneysData['can_odo'] > 0))) {
            /*$telematicsJourneys->odometer_start = $telematicsJourneysData['odometer'] - $telematicsJourneysData['can_distance'];
            $telematicsJourneys->odometer_end = $telematicsJourneysData['odometer'];*/
            $telematicsJourneys->odometer_start = $telematicsJourneysData['can_odo'] - $telematicsJourneysData['can_distance'];
            $telematicsJourneys->odometer_end = $telematicsJourneysData['can_odo'];
        }
        /*else{
            if (isset($telematicsJourneys['can_odo']) && $telematicsJourneysData['can_odo'] > 0) {
                $telematicsJourneys->odometer_start = $telematicsJourneysData['can_odo'] - $telematicsJourneysData['can_distance'];
                $telematicsJourneys->odometer_end = $telematicsJourneysData['can_odo'];
            }
            else{
                $telematicsJourneys->odometer_start = $calculatedFields['odoStart'];
                $telematicsJourneys->odometer_end = $calculatedFields['odoEnd'];
            }
        }
        */

        $telematicsJourneys->incident_count = $calculatedFields['incidentCount'];
        $telematicsJourneys->harsh_breaking_count = $calculatedFields['harsh_breaking'];
        $telematicsJourneys->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
        $telematicsJourneys->harsh_cornering_count = $calculatedFields['harsh_cornering'];
        $telematicsJourneys->speeding_count = $calculatedFields['speeding'];
        $telematicsJourneys->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
        $telematicsJourneys->rpm_count = $calculatedFields['rpm'];
        $telematicsJourneys->idling_count = $calculatedFields['idling'];

        $telematicsJourneys->save();
        

        $this->updateVehicleTotalfuelSumAndDistanceSum($telematicsJourneys->vrn);
        if(isset($telematicsJourneys)) {
            $checkFlag = false;
            $summary_json = json_decode($telematicsJourneys->raw_json);
            //if ($summary_json->ns == 'tm8.jny.sum') {
                if (isset($summary_json->odo_source) && ($summary_json->odo_source == 1 && $summary_json->can_distance > 50)) {
                    $checkFlag = true;
                }
                elseif(isset($summary_json->gps_distance) && $summary_json->gps_distance > 50){
                    $checkFlag = true;
                }               
            //}
            if ($checkFlag) {
                $telematicsService = new TelematicsService();
                $telematicsJourneysData['journey_details_id'] = $telematicsJourneys->id;
                $telematicsJourneysData['vehicle_id'] = $telematicsJourneys->vehicle_id;
                $telematicsJourneysData['journey_start_date'] = $telematicsJourneys->start_time;
                $alert = Alerts::where('slug', 'vehicle_check_incomplete')->first();
                if($alert->is_active == 1) {
                    $telematicsService->checkEntryAndCreateAlert($telematicsJourneysData, $alert);
                }
            }
            if($telematicsJourneys->fuel == 0 && $telematicsJourneys->gps_idle_duration == 0 && $telematicsJourneys->gps_distance == 0){
            //if($telematicsJourneys->fuel == 0 && $telematicsJourneys->idle_duration == 0){
                // print_r("inside if");
                $telematicsJourneys->delete();
                $del = TelematicsJourneyDetails::where('telematics_journey_id', $telematicsJourneys->id)->delete();
            }
        }
        return $telematicsJourneys;
    }

    public function bindUserWithJourney($user_id,$vrn,$journey_id,$driver_tag_key,$uid) {
        $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$vrn,'journey_id'=>$journey_id,'uid'=>$uid])->first();

        if ($journey_id == 0 || $telematicsJourneys == null) {
            $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$vrn])->orderBy('start_time','DESC')->first();
        }
        if($telematicsJourneys){
                $telematicsJourneys->user_id = $user_id;
                $telematicsJourneys->dallas_key = $driver_tag_key;
                $telematicsJourneys->save();
        }

        //Updating userId for zone alerts, as we are getting userId only here at the end of the journey only.
        if ($journey_id!=null && $journey_id>0 && $user_id!=null && $user_id!=1){
            $telematics_journeys_pk_id=$telematicsJourneys->id; //first pick TelematicsJourneys table's pkId (journeyId)
            $telematicsJourneyDetailsArray=TelematicsJourneyDetails::where(['telematics_journey_id'=>$telematics_journeys_pk_id,'vrn'=>$vrn,'ns'=>'tm8.gps'])->get()->pluck('id'); //fetch all details id base on that pkId
            if(!empty($telematicsJourneyDetailsArray)){
                //update all userId column for the "ZoneAlerts" table for those records whose journey_details_id are available into "$telematicsJourneyDetailsArray"
                ZoneAlerts::whereIn('journey_details_id',$telematicsJourneyDetailsArray)->update(['user_id'=>$user_id]);
            }
        }
        
    }
    public function updateScores($telematicsJourneysData) {
        //print_r($telematicsJourneysData['journey_id']);exit;
        /*$telematicsJourneys = TelematicsJourneys::where(['vrn'=>$telematicsJourneysData['vrn'],'journey_id'=>$telematicsJourneysData['journey_id']])->first();
        //print_r($telematicsJourneys);exit;
        $telematicsJourneys->efficiency_score = isset($telematicsJourneysData['report_metric_514'])?$telematicsJourneysData['report_metric_514']:null;
        $telematicsJourneys->rpm_score = isset($telematicsJourneysData['report_metric_513'])?$telematicsJourneysData['report_metric_513']:null;
        $telematicsJourneys->idle_score = isset($telematicsJourneysData['report_metric_512'])?$telematicsJourneysData['report_metric_512']:null;
        $telematicsJourneys->safety_score = isset($telematicsJourneysData['report_metric_511'])?$telematicsJourneysData['report_metric_511']:null;
        $telematicsJourneys->speeding_score = isset($telematicsJourneysData['report_metric_510'])?$telematicsJourneysData['report_metric_510']:null;
        $telematicsJourneys->cornering_score = isset($telematicsJourneysData['report_metric_508'])?$telematicsJourneysData['report_metric_508']:null;
        $telematicsJourneys->braking_score = isset($telematicsJourneysData['report_metric_507'])?$telematicsJourneysData['report_metric_507']:null;
        $telematicsJourneys->acceleration_score = isset($telematicsJourneysData['report_metric_506'])?$telematicsJourneysData['report_metric_506']:null;
        $telematicsJourneys->save();*/

    }
    public function updateScores_calculated($telematicsJourney, $scoreData) {
        // //print_r($telematicsJourneysData['journey_id']);exit;
        // $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$telematicsJourneysData['vrn'],'journey_id'=>$telematicsJourneysData['journey_id']])->first();
        // //print_r($telematicsJourneys);exit;
        // $telematicsJourneys->efficiency_score = isset($telematicsJourneysData['report_metric_514'])?$telematicsJourneysData['report_metric_514']:null;
        // $telematicsJourneys->rpm_score = isset($telematicsJourneysData['report_metric_513'])?$telematicsJourneysData['report_metric_513']:null;
        // $telematicsJourneys->idle_score = isset($telematicsJourneysData['report_metric_512'])?$telematicsJourneysData['report_metric_512']:null;
        // $telematicsJourneys->safety_score = isset($telematicsJourneysData['report_metric_511'])?$telematicsJourneysData['report_metric_511']:null;
        // $telematicsJourneys->speeding_score = isset($telematicsJourneysData['report_metric_510'])?$telematicsJourneysData['report_metric_510']:null;
        // $telematicsJourneys->cornering_score = isset($telematicsJourneysData['report_metric_508'])?$telematicsJourneysData['report_metric_508']:null;
        // $telematicsJourneys->braking_score = isset($telematicsJourneysData['report_metric_507'])?$telematicsJourneysData['report_metric_507']:null;
        // $telematicsJourneys->acceleration_score = isset($telematicsJourneysData['report_metric_506'])?$telematicsJourneysData['report_metric_506']:null;
        // $telematicsJourneys->save();

        $telematicsJourney->efficiency_score = isset($scoreData['efficiency_score']) ? $scoreData['efficiency_score'] : null;
        $telematicsJourney->rpm_score = isset($scoreData['rpm_score']) ? $scoreData['rpm_score'] : null;
        $telematicsJourney->idle_score = isset($scoreData['idle_score']) ? $scoreData['idle_score'] : null;
        $telematicsJourney->safety_score = isset($scoreData['safety_score']) ? $scoreData['safety_score'] : null;
        $telematicsJourney->speeding_score = isset($scoreData['speeding_score']) ? $scoreData['speeding_score'] : null;
        $telematicsJourney->cornering_score = isset($scoreData['cornering_score']) ? $scoreData['cornering_score'] : null;
        $telematicsJourney->braking_score = isset($scoreData['braking_score']) ? $scoreData['braking_score'] : null;
        $telematicsJourney->acceleration_score = isset($scoreData['acceleration_score']) ? $scoreData['acceleration_score'] : null;
        $telematicsJourney->save();
    }

    public function getTotalIncidentCountOfJourney($journey_id) {
        $incidentCount = 0;
        if($journey_id != null) {
           $incidentData =  DB::table('telematics_journey_details')
            ->selectRaw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 
            WHEN ns = "tm8.dfb2.acc.l" THEN 1 
            WHEN ns = "tm8.dfb2.spd" THEN 1 
            WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 
            WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 
            WHEN ns = "tm8.dfb2.rpm" THEN 1 
            WHEN ns = "tm8.gps.heartbeat" THEN 1 
            WHEN ns = "tm8.gps.idle.start" THEN 1 
            ELSE 0 END) AS incident_count')
            ->where('telematics_journey_id','=',$journey_id)
            ->first();

            $incidentCount = ($incidentData->incident_count != '') ? $incidentData->incident_count : 0;
        }
        return $incidentCount;
    }

    public function getCalculatedFieldsOfJourney($journey_id) {
        $incidentCount = 0;
        if($journey_id != null) {
           $incidentData =  DB::table('telematics_journey_details')
            ->selectRaw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count,
            SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 ELSE 0 END) AS harsh_breaking,
            SUM(CASE WHEN ns = "tm8.dfb2.acc.l" THEN 1 ELSE 0 END) AS harsh_acceleration,
            SUM(CASE WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1  ELSE 0 END) AS harsh_cornering,
            SUM(CASE WHEN ns = "tm8.dfb2.spd" THEN 1 ELSE 0 END) AS speeding,
            SUM(CASE WHEN ns = "tm8.dfb2.spdinc" THEN 1 ELSE 0 END) AS new_speeding_incidents,
            SUM(CASE WHEN ns = "tm8.dfb2.rpm" THEN 1 ELSE 0 END) AS rpm,
            SUM(CASE WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS idling,
            max(speed) as maxspeed, avg(speed) as avgspeed')
            ->where('telematics_journey_id','=',$journey_id)
            ->whereNull('deleted_at')
            ->first();
            /* $incidentData =  DB::table('telematics_journey_details')
            ->selectRaw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count,
            SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 ELSE 0 END) AS harsh_breaking,
            SUM(CASE WHEN ns = "tm8.dfb2.acc.l" THEN 1 ELSE 0 END) AS harsh_acceleration,
            SUM(CASE WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1  ELSE 0 END) AS harsh_cornering,
            SUM(CASE WHEN ns = "tm8.dfb2.spd" THEN 1 ELSE 0 END) AS speeding,
            SUM(CASE WHEN ns = "tm8.dfb2.spdinc" THEN 1 ELSE 0 END) AS new_speeding_incidents,
            SUM(CASE WHEN ns = "tm8.dfb2.rpm" THEN 1 ELSE 0 END) AS rpm,
            SUM(CASE WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS idling,
            max(speed) as maxspeed, avg(speed) as avgspeed, min(gps_odo) as odoStart, max(gps_odo) as odoEnd')
            ->where('telematics_journey_id','=',$journey_id)
            ->first();*/

            $data = [];
            $data['incidentCount'] = ($incidentData->incident_count != '') ? $incidentData->incident_count : 0;
            $data['maxspeed'] = ($incidentData->maxspeed != '') ? $incidentData->maxspeed : 0;
            $data['avgspeed'] = ($incidentData->avgspeed != '') ? $incidentData->avgspeed : 0;
            //$data['odoStart'] = ($incidentData->odoStart != '') ? $incidentData->odoStart : 0;
            //$data['odoEnd'] = ($incidentData->odoEnd != '') ? $incidentData->odoEnd : 0;
            $data['harsh_breaking'] = $incidentData->harsh_breaking;
            $data['harsh_acceleration'] = $incidentData->harsh_acceleration;
            $data['harsh_cornering'] = $incidentData->harsh_cornering;
            $data['speeding'] = $incidentData->speeding;
            $data['new_speeding_incidents'] = $incidentData->new_speeding_incidents;
            $data['rpm'] = $incidentData->rpm;
            $data['idling'] = $incidentData->idling;

            \Log::info('inside  getCalculatedFieldsOfJourney');
            \Log::info('journey_id ' . $journey_id);
            \Log::info('data :');
            \Log::info($data);
        }
        return $data;
    }

    public function updateVehicleTotalfuelSumAndDistanceSum($vrn) {
        if($vrn != '') {
            // fetch total dum from telematics journeys
            $vehicleJourneySum = DB::table('telematics_journeys')
            ->select(DB::raw('(SUM(fuel)) AS vehiclefuelsum'),DB::raw('(SUM(gps_distance)) AS vehicledistancesum'))
            ->where('vrn','=', $vrn)
            ->first();

            $vehiclefuelsum = ($vehicleJourneySum->vehiclefuelsum != '') ? $vehicleJourneySum->vehiclefuelsum : 0;
            $vehicledistancesum = ($vehicleJourneySum->vehicledistancesum != '') ? $vehicleJourneySum->vehicledistancesum : 0;

            // update into vehicles
            $vehicle = Vehicle::where('registration',$vrn)->first();
            $vehicle->vehiclefuelsum = $vehiclefuelsum;
            $vehicle->vehicledistancesum = $vehicledistancesum;
            $vehicle->save();
        }
    }

}
