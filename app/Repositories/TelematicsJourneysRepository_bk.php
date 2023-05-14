<?php
namespace App\Repositories;

use Carbon\Carbon as Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use Auth;

class TelematicsJourneysRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null, $data = null)
    {
        if ($request != null) {
            // code...
            //$this->Database = DB::table('telematics_journeys');
            if(isset($request->filters)) {
                $data = json_decode($request->filters, true);
            } else {
                $data = $request->all();
            }
            $userFilterValue = '';
            if (isset($data['userFilterValue']) && $data['userFilterValue']) {
                $userFilterValue = $data['userFilterValue'];
            }
            $registrationFilterValue = '';
            if (isset($data['registrationFilterValue']) && $data['registrationFilterValue']) {
                $registrationFilterValue = $data['registrationFilterValue'];
            }

            $regionFilterValue = '';
            if (isset($data['regionFilterValue']) && $data['regionFilterValue']) {
                $regionFilterValue = $data['regionFilterValue'];
            }

            if (isset($data['startDate']) && isset($data['endDate'])) {
                $startDate = $data['startDate'].' 00:00:00';
                $endDate = $data['endDate'].' 23:59:59';
            } else {
                $startDate = Carbon::now()->toDateString().' 00:00:00';
                $endDate = Carbon::now()->toDateString().' 23:59:59';
            }

            $postcodeFilterValue = '';
            if (isset($data['postcode']) && $data['postcode']) {
                $postcodeFilterValue = $request->get('postcode');
            }
            
            $telematicsProvider = env("TELEMATICS_PROVIDER");

            $this->Database = DB::table(DB::raw('telematics_journeys as j force index (telematics_journeys_start_time_index)'))
            //$this->Database = DB::table('telematics_journeys as j')
            ->selectRaw('"'.$telematicsProvider.'" as provider,j.id,j.gps_idle_duration,j.fuel,
            j.co2,j.gps_distance,j.incident_count,v.vehiclefuelsum,v.vehicledistancesum
            ,max_speed AS mxmph,avg_speed AS avgmph,odometer_start AS journeyStart,odometer_end AS journeyEnd,
            CASE WHEN u.id = 1 THEN "Driver Unknown" ELSE CONCAT(first_name, " ", last_name) END AS user,j.vrn AS registraion,
            DATE_FORMAT(start_time,"%H:%i:%s %d %b %Y") as start_time_edited,
            DATE_FORMAT(end_time,"%H:%i:%s %d %b %Y") AS end_time_edited')
            ->join('users as u','u.id','=','j.user_id')
            ->join('vehicles as v','v.id','=','j.vehicle_id')
            ->whereBetween("start_time",[$startDate,$endDate])
            ->where('v.is_telematics_enabled','=','1');

            if ($userFilterValue != '') {
                $this->Database->where("j.user_id",$userFilterValue);
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

            $this->visibleColumns = ['"'.$telematicsProvider.'" as provider', 'j.id', 'j.gps_idle_duration', 'j.fuel', 'j.co2', 'j.gps_distance', 'j.incident_count', 'v.vehiclefuelsum', 'v.vehicledistancesum', 'max_speed AS mxmph', 'avg_speed AS avgmph', 'odometer_start AS journeyStart', 'odometer_end AS journeyEnd', 'CASE WHEN u.id = 1 THEN "Driver Unknown" ELSE CONCAT(first_name, " ", last_name) END AS user,j.vrn AS registraion',
                'DATE_FORMAT(start_time,"%H:%i:%s %d %b %Y") as start_time_edited',
                'DATE_FORMAT(end_time,"%H:%i:%s %d %b %Y") AS end_time_edited'
            ];

            $this->orderBy = [['start_time', 'DESC']];
            $this->Database->take(25000);
        }
    }

    public function create($telematicsJourneysData) {
        $telematicsJourney = TelematicsJourneys::where('vrn',$telematicsJourneysData['vrn'])->where('journey_id',$telematicsJourneysData['journey_id'])->first();
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
        $telematicsJourney->gps_distance = isset($telematicsJourneysData['gps_distance'])?$telematicsJourneysData['gps_distance']:'0';
        $telematicsJourney->gps_odo = isset($telematicsJourneysData['gps_odo'])?$telematicsJourneysData['gps_odo']:'0';
        $telematicsJourney->end_street = isset($telematicsJourneysData['end_street'])?$telematicsJourneysData['end_street']:null;
        $telematicsJourney->end_town = isset($telematicsJourneysData['end_town'])?$telematicsJourneysData['end_town']:null;
        $telematicsJourney->end_post_code = isset($telematicsJourneysData['end_postcode'])?$telematicsJourneysData['end_postcode']:null;
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
        $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$telematicsJourneysData['vrn'],'journey_id'=>$telematicsJourneysData['journey_id']])->first();
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
        if (isset($telematicsJourneysData['gps_distance'])) {
            $telematicsJourneys->gps_distance = $telematicsJourneysData['gps_distance'];
        }
        $telematicsJourneys->gps_odo = isset($telematicsJourneysData['gps_odo'])?$telematicsJourneysData['gps_odo']:'0';
        $telematicsJourneys->end_street = isset($telematicsJourneysData['end_street'])?$telematicsJourneysData['end_street']:null;
        $telematicsJourneys->end_town = isset($telematicsJourneysData['end_town'])?$telematicsJourneysData['end_town']:null;
        $telematicsJourneys->end_post_code = isset($telematicsJourneysData['end_postcode'])?$telematicsJourneysData['end_postcode']:null;
        $telematicsJourneys->uid = isset($telematicsJourneysData['uid'])?$telematicsJourneysData['uid']:null;
        $telematicsJourneys->make = isset($telematicsJourneysData['make'])?$telematicsJourneysData['make']:null;
        $telematicsJourneys->model = isset($telematicsJourneysData['model'])?$telematicsJourneysData['model']:null;

        $telematicsJourneys->incident_count = $this->getTotalIncidentCountOfJourney($telematicsJourneys->id);
        $calculatedFields = $this->getCalculatedFieldsOfJourney($telematicsJourneys->id);
        $telematicsJourneys->max_speed = $calculatedFields['maxspeed'];
        $telematicsJourneys->avg_speed = $calculatedFields['avgspeed'];
        $telematicsJourneys->odometer_start = $calculatedFields['odoStart'];
        $telematicsJourneys->odometer_end = $calculatedFields['odoEnd'];
        $telematicsJourneys->save();
        

        $this->updateVehicleTotalfuelSumAndDistanceSum($telematicsJourneys->vrn);
        return $telematicsJourneys;
    }

    public function bindUserWithJourney($user_id,$vrn,$journey_id,$driver_tag_key) {
        $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$vrn,'journey_id'=>$journey_id])->first();
        if ($journey_id == 0 || $telematicsJourneys == null) {
            $telematicsJourneys = TelematicsJourneys::where(['vrn'=>$vrn])->orderBy('start_time','DESC')->first();
        }
	if($telematicsJourneys){
	        $telematicsJourneys->user_id = $user_id;
        	$telematicsJourneys->dallas_key = $driver_tag_key;
	        $telematicsJourneys->save();
	}
    }

    public function updateScores($telematicsJourney, $scoreData) {
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
            ->selectRaw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 
            WHEN ns = "tm8.dfb2.acc.l" THEN 1 
            WHEN ns = "tm8.dfb2.spd" THEN 1 
            WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 
            WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 
            WHEN ns = "tm8.dfb2.rpm" THEN 1 
            WHEN ns = "tm8.gps.heartbeat" THEN 1 
            WHEN ns = "tm8.gps.idle.start" THEN 1 
            ELSE 0 END) AS incident_count,max(speed) as maxspeed, avg(speed) as avgspeed, min(odometer) as odoStart, max(odometer) as odoEnd')
            ->where('telematics_journey_id','=',$journey_id)
            ->first();

            $data = [];
            $data['incidentCount'] = ($incidentData->incident_count != '') ? $incidentData->incident_count : 0;
            $data['maxspeed'] = ($incidentData->maxspeed != '') ? $incidentData->maxspeed : 0;
            $data['avgspeed'] = ($incidentData->avgspeed != '') ? $incidentData->avgspeed : 0;
            $data['odoStart'] = ($incidentData->odoStart != '') ? $incidentData->odoStart : 0;
            $data['odoEnd'] = ($incidentData->odoEnd != '') ? $incidentData->odoEnd : 0;
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
