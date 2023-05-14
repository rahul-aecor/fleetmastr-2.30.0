<?php
namespace App\Services;

use File;
use Auth;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\TelematicsJourneyDetails;
use App\Models\Check;
use App\Models\Alerts;
use App\Models\AlertNotifications;
use App\Models\TelematicsJourneys;
use App\Repositories\TelematicsJourneysRepository;
use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\ZonesRepository;
use App\Repositories\ZoneAlertsRepository;
use App\Models\Zone;
use App\Models\ZoneVehicle;
use App\Models\ZoneVehicleType;
use App\Models\ZoneVehicleRegion;
use App\Jobs\TelematicsProcessZoneData;
use App\Repositories\AlertNotificationsRepository;
use App\Jobs\ProcessTelematicDataPush;
use App\Jobs\ProcessTeletracDataPush;
use App\Models\Vehicle;
use App\Repositories\ZoneVehiclesRepository;

class TelematicsService
{
    /**
     * Create a new vehicle service instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    public function bindUser($value){

        $telematicsJourney = new TelematicsJourneysRepository();
        \Log::info(json_encode($value));
        $driver_tag_key = (isset($value['tag_id']) && trim($value['tag_id']) != "" && trim($value['tag_id']) != 0) ? trim($value['tag_id']) : (isset($value['driver1_id']) && trim($value['driver1_id']) != "") ? trim($value['driver1_id']) : "";
        //$user = User::where(['driver_tag_key'=>$driver_tag_key])->first();
        $user_id = 1;//system user_id default

        /*
        * FLEE-6835 - Farzan
        */
        $vehicle = Vehicle::where('registration', $value['vrn'])->first();
        if(isset($vehicle) && $vehicle->nominated_driver) {
            $user_id = $vehicle->nominated_driver;
        }

        if ($driver_tag_key != "") {
            $user = User::where('is_disabled',0)->whereRaw("UPPER(driver_tag_key) = '". strtoupper($driver_tag_key)."'")->first();
            if ($user) {
                $user_id = $user->id;
            }
        }
        \Log::info("User id : " . $user_id);

        $telematicsJourney->bindUserWithJourney($user_id,$value['vrn'],$value['journey_id'],$driver_tag_key,$value['uid']);

        /*$value['user_id'] = $user_id;
        $journeyDetails = TelematicsJourneys::where('journey_id', $value['journey_id'])
                                                ->where('user_id', $user_id)
                                                ->latest()
                                                ->first();
        if(isset($journeyDetails)) {
            $value['journey_details_id'] = $journeyDetails->id;
            $value['vehicle_id'] = $journeyDetails->vehicle_id;
            $value['journey_start_date'] = $journeyDetails->start_time;
            $alert = Alerts::where('slug', 'vehicle_check_incomplete')->first();
            if($alert->is_active == 1) {
                $this->checkEntryAndCreateAlert($value, $alert);
            }
        }*/
    }

    public function dataPush($dataFromTrakm8Array) {
        /*$telematicsJourney = new TelematicsJourneysRepository();
        $telematicsJourneyDetails = new TelematicsJourneyDetailsRepository();*/
        if(env('TELEMATICS_PROVIDER') == 'teletrac' ){
            $job = (new ProcessTeletracDataPush($dataFromTrakm8Array))->onQueue('telematics');
            dispatch($job);
        }
        else{
            $job = (new ProcessTelematicDataPush($dataFromTrakm8Array))->onQueue('telematics');
            dispatch($job);
        }
    }

    // process zone data if ns = tm8.gps and checking if vehicle is in polygon
    public function processZoneData($value){
        if($value['ns'] == 'tm8.gps'){
            $zone = new ZonesRepository();
            // get all zones data where bound is not null
            $allZones = $zone->getAllZonesData();
            // checking vehicle in polygon
            $this->toCheckLatlongIsInsidePolygon($allZones, $value);
        }
    }

    // old data for zones checking (polygon)
    public function zonesToCheck($reg,$activezones){

        $vehicle = Vehicle::withTrashed()->with(['region','type'])->where('registration',$reg)->first();


    	if(is_array($activezones)){
            	$zoneIds = array_column($activezones,'id');
    	}
    	else{
            	$zoneIds = $activezones->lists('id');
    	}

        $zoneVehicle_zoneIds = ZoneVehicle::whereIn('zone_id', $zoneIds)->where('vehicle_id',$vehicle->id)->lists('zone_id');
        $zoneVehicleRegion_zoneIds = ZoneVehicleRegion::whereIn('zone_id', $zoneIds)->where('vehicle_region_id',$vehicle->region->id)->lists('zone_id');

        $zoneVehicleType_zoneIds = ZoneVehicleType::whereIn('zone_id', $zoneIds)->where('vehicle_type_id',$vehicle->type->id)->lists('zone_id');
        $allItems = collect();
        $allItems = $allItems->merge($zoneVehicle_zoneIds);
        $allItems = $allItems->merge($zoneVehicleRegion_zoneIds);
        $allItems = $allItems->merge($zoneVehicleType_zoneIds);
        $allItems = $allItems->unique();
        return Zone::whereIn('id',$allItems)->get();
    }

    // old data for zones checking (polygon)
    public function checkJourneyExistInZoneAlert($journeyDetailsId){
        $zone = new ZonesRepository();
        $zoneAlertSession = $zone->checkJourneyExistInZoneAlert($journeyDetailsId);
        $res['journeyExist'] = false;
        $res['journeyExistData'] = [];
        if (!empty($zoneAlertSession) ) {
            $res['journeyExist'] = true;
            $res['journeyExistData'] = [$zoneAlertSession];
        }
        return $res;
    }

    // old data for zones checking (polygon)
    public function getActiveZones(){
        $zone = new ZonesRepository();
        $activeZones = $zone->getActiveZones();
        return $activeZones;
    }

    public function toCheckLatlongIsInsidePolygon($activeZones, $value){
        $insidePolygon = false;
        $zoneService = new ZoneService();
        $zoneRepository = new ZonesRepository();
        $zoneVehiclesRepository = new ZoneVehiclesRepository();
        foreach ($activeZones as $zone) {
            // checking if vehicle is already available in zones
            //$checkZoneExistInZoneVehicle = $zoneRepository->checkZoneExistInZoneVehicle($zone['id'], $value['vrn']);
            $data['vrn'] = $value['vrn'];
            $data['zone_id'] = $zone['id'];
            $checkZoneExistInZoneVehicle = $zoneVehiclesRepository->isZoneVehicleEntryExisting($data);

            $polygon = json_decode($zoneService->makePolygonJson($zone), true);
            $insidePolygon = $zoneService->is_in_polygon2($value['lon'], $value['lat'], $polygon);

            if($insidePolygon) {
                //if(empty($checkZoneExistInZoneVehicle)) {
                if(!$checkZoneExistInZoneVehicle) {
                    $value['is_alert'] = 1;
                    // store the zone alert value if alert setting is on entry or entry and exit
                    if($zone['alert_setting'] != 0) {
                        $zoneAlert = $this->storeZoneAlert($value, $zone);
                    }
                    $this->storeZoneVehicle($value['vrn'], $zone->id);
                    $zoneAlertId = $zoneAlert->id;
                } /*else {
                    $zoneAlertId = $checkZoneExistInZoneVehicle->id;
                }*/
            } else {
                if($checkZoneExistInZoneVehicle) {
                //if(!empty($checkZoneExistInZoneVehicle)) {
                    // store the zone alert value if alert setting is on exit or entry and exit
                    if($zone['alert_setting'] != 1) {
                        $value['is_alert'] = 0;
                        $zoneAlert = $this->storeZoneAlert($value, $zone);
                    }
                    $this->deleteZoneVehicle($value['vrn'], $zone->id);
                }
            }
        }
    }

    public function storeZoneAlert($value, $zone) {
        $zoneAlertRepo = new ZoneAlertsRepository();
        $data = array();
        $vehicle = Vehicle::with('lastCheck')->where(['registration'=>$value['vrn']])->first();
        $driver_tag_key = isset($value['tag_id'])?$value['tag_id']:null;
        $user = User::where(['driver_tag_key'=>$driver_tag_key])->first();
        $user_id = 1;//system user_id default
        if ($user) {
            $user_id = $user->id;
        }

        $data['zone_id'] = $zone->id;
        $data['vehicle_id'] = (!empty($vehicle)) ? $vehicle->id : 1;
        $data['vrn'] = (!empty($vehicle)) ? $vehicle->registration : 1;
        $data['user_id'] = $user_id;
        $data['journey_details_id'] = $value['journey_details_id'];
        $data['is_alert'] = $value['is_alert'];
        $data['ns'] = $value['ns'];
        $data['speed'] =  $value['speed'];
        $data['max_acceleration'] = isset($value['street_speed'])?$value['street_speed']:null;
        $data['direction'] = $value['heading'];
        if(isset($value['street']) && isset($value['town']) && isset($value['postcode'])) {
            $data['address'] = $value['street'].', '.$value['town'].', '.$value['postcode'];
        }
        $data['latitude'] = $value['lat'];
        $data['longitude'] = $value['lon'];
        $data['start_time'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
        $data['created_at'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
        $data['updated_at'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;

        return $zoneAlertRepo->createZoneAlert($data);
    }

    public function storeZoneVehicle($vrn, $zoneId) {
        //$zoneAlertRepo = new ZoneAlertsRepository();
        $data['zone_id'] = $zoneId;
        $data['vrn'] = $vrn;
        //return $zoneAlertRepo->createZoneVehicle($data);
        $zoneVehiclesRepository = new ZoneVehiclesRepository();
        $zoneVehiclesRepository->createZoneVehicle($data);
    }

    public function deleteZoneVehicle($vrn, $zoneId) {
        //return ZoneVehicle::where('zone_id', $zoneId)->where('vrn', $vrn)->forceDelete();
        $data['zone_id'] = $zoneId;
        $data['vrn'] = $vrn;
        $zoneVehiclesRepository = new ZoneVehiclesRepository();
        $zoneVehiclesRepository->delZoneVehicle($data);
    }

    // old data for zones checking (polygon)
    public function toCheckLatlongIsInsidePolygonOld($activeZones, $value, $existJourney){
        //\Log::info('inside check lat/lng inside active polygon');
        $insidePolygon = false;
        $outsidePolygon = false;
        $zoneService = new ZoneService();
        // $zone = new ZonesRepository();
        foreach ($activeZones as $zone) {
            $existZoneAlert = $zone;
            $zone = ($existJourney['journeyExist'] == true) ? $zone->zone : $zone;

            $polygon = json_decode($zoneService->makePolygonJson($zone), true);
            if ($zone->is_tracking_inside) {
                $insidePolygon = $zoneService->is_in_polygon2($value['lon'], $value['lat'], $polygon);
                //\Log::info('lat/lng inside active polygon checked');
                if( $insidePolygon ) {
                    //\Log::info('inside zone area');
                    // To insert in alert session table
                    if ($existJourney['journeyExist'] == false) {
                        //\Log::info('create a new entry of zone alert session');
                        $zoneAlertSession = $this->createZoneAlertSession($value, $zone);
                        $zoneAlertSessionId = $zoneAlertSession->id;
                    } else {
                        //\Log::info('zone alert session already created just get id');
                        $zoneAlertSessionId = $existZoneAlert->id;
                    }
                    //\Log::info('Going to create zone alert entry');
                    $this->createZoneAlert($value, $zone, $zoneAlertSessionId);
                } else {
                    // check 
                    //\Log::info('safe zone area : outside');
                    $this->updateZoneAlertSession($value, $existZoneAlert);
                }


            }
            else{
                $outsidePolygon = !$zoneService->is_in_polygon2($value['lon'], $value['lat'], $polygon);
                //\Log::info('lat/lng outside active polygon checked');
                if( $outsidePolygon ) {
                    //\Log::info('outside zone area');
                    // To insert in alert session table
                    if ($existJourney['journeyExist'] == false) {
                        //\Log::info('create a new entry of zone alert session');
                        $zoneAlertSession = $this->createZoneAlertSession($value, $zone);
                        $zoneAlertSessionId = $zoneAlertSession->id;
                    } else {
                        //\Log::info('zone alert session already created just get id');
                        $zoneAlertSessionId = $existZoneAlert->id;
                    }
                    //\Log::info('Going to create zone alert entry');
                    $this->createZoneAlert($value, $zone, $zoneAlertSessionId);
                } else {
                    // check 
                    //\Log::info('safe zone area : inside');
                    $this->updateZoneAlertSession($value, $existZoneAlert);
                }
            }
            if( $outsidePolygon ) {
                //\Log::info('inside zone area');
                // To insert in alert session table
                if ($existJourney['journeyExist'] == false) {
                    //\Log::info('create a new entry of zone alert session');
                    $zoneAlertSession = $this->createZoneAlertSession($value, $zone);
                    $zoneAlertSessionId = $zoneAlertSession->id;
                } else {
                    //\Log::info('zone alert session already created just get id');
                    $zoneAlertSessionId = $existZoneAlert->id;
                }
                //\Log::info('Going to create zone alert entry');
                $this->createZoneAlert($value, $zone, $zoneAlertSessionId);
            } else {
                // check 
                //\Log::info('safe zone area');
                $this->updateZoneAlertSession($value, $existZoneAlert);
            }
        }
    }

    // old data for zones checking (polygon)
    public function createZoneAlertSession($value, $zone) {
        $zoneRepo = new ZonesRepository();
        $data = array();
        $vehicle = Vehicle::with('lastCheck')->where(['registration'=>$value['vrn']])->first();
        $driver_tag_key = isset($value['tag_id'])?$value['tag_id']:null;
        //$user = User::where(['driver_tag_key'=>$driver_tag_key])->first();
        $user = User::where(['driver_tag_key'=>$driver_tag_key])->first();
        $user_id = 1;//system user_id default
        if ($user) {
            $user_id = $user->id;
        }

        $data['zone_id'] = $zone->id;
        $data['vehicle_id'] = (!empty($vehicle)) ? $vehicle->id : 1;
        $data['user_id'] = $user_id;
        $data['journey_id'] = $value['journey_id'];
        $data['status'] = 'incomplete';
        $data['start_time'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
        $data['end_time'] = null;
        return $zoneRepo->createAlertSession($data);
    }

    // old data for zones checking (polygon)
    public function createZoneAlert($value, $zone, $zoneAlertSessionId) {
        $zoneAlertRepo = new ZoneAlertsRepository();
        $zoneAlertSessionCount = $zoneAlertRepo->getAlertCountBySessionId($zoneAlertSessionId);
        // Get total count of zone alerts for current sessions
        if( ($zone->alert_type == 'one_off' || $zone->alert_type == 'regular') && $zoneAlertSessionCount == 0) {
            $data = array();
            //\Log::info('zone type one off or zone type regular & first entry of zone alert');
            return $this->createZoneAlertSessionEntry($zoneAlertSessionId, $value);
        } else {
            // Get latest entry time and add interval time 
            if( $zoneAlertSessionCount > 0 ) {
                //\Log::info('zone alert type regular and second entry');
                $latestAlert = $zoneAlertRepo->getLatestAlertOfSessionId($zoneAlertSessionId);
                $lastCreatedOn = $latestAlert->created_at;

                $lastCreatedDateTime = Carbon::parse($lastCreatedOn);
                $cutOffTime = $lastCreatedDateTime->addMinutes($zone->alert_interval);

                $currentTime = Carbon::parse($value['time'])->setTimezone('UTC');
                if ($currentTime->gte($cutOffTime)) {
                    //\Log::info('create entry if alert interval condition satisfied');
                    return $this->createZoneAlertSessionEntry($zoneAlertSessionId, $value);
                }
            }
        }
    }

    public function processScore($telematicsJourney)
    {
        // \Log::info('processScore');
        // \Log::info('telematicsJourney->id ' . $telematicsJourney->id);
        $telematicsJourneyRepo = new TelematicsJourneysRepository();
        /*$incidentTypes = array_keys(config('config-variables.telematics_incidents'));

        $allIncidents = TelematicsJourneyDetails::where('telematics_journey_id', $telematicsJourney->id)
                                                ->whereIn('ns', $incidentTypes)
                                                ->get();
        $accelerationIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.acc.l')->count();
        // \Log::info('accelerationIncidentsCount ' . $accelerationIncidentsCount);

        $corneringIncidentsCount = $allIncidents->filter(function ($incident) {
                                        return in_array($incident->ns, ['tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l']);
                                    })->count();
        // \Log::info('corneringIncidentsCount ' . $corneringIncidentsCount);
        $speedingIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.spd')->count();
        // \Log::info('speedingIncidentsCount ' . $speedingIncidentsCount);
        $brakingIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.dec.l')->count();
        // \Log::info('brakingIncidentsCount ' . $brakingIncidentsCount);
        $rpmIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.rpm')->count();
        // \Log::info('rpmIncidentsCount ' . $rpmIncidentsCount);
        $idlingIncidentsCount = $allIncidents->where('ns', 'tm8.gps.idle.start')->count();
        // \Log::info('idlingIncidentsCount ' . $idlingIncidentsCount);

        $distanceFactorInMiles = setting('distance_factor_in_miles');
        $safetyScorePercentage = setting('safety_score_percentage');
        $efficiencyScorePercentage = setting('efficiency_score_percentage');
        $accelerationScorePercentage = setting('acceleration_score_percentage');
        $brakingScorePercentage = setting('braking_score_percentage');
        $corneringScorePercentage = setting('cornering_score_percentage');
        $speedingScorePercentage = setting('speeding_score_percentage');
        $rpmScorePercentage = setting('rpm_score_percentage');
        $idleTimeScorePercentage = setting('idle_time_score_percentage');
        $journeyDistance = number_format((float)($telematicsJourney->gps_distance * 0.00062137), 2, '.', '');
        $totalDriveTime = ((int) $telematicsJourney->engine_duration) / 60;
        $totalDriveTime = number_format((float) $totalDriveTime, 2, '.', '');
        $totalIdleTime = ((int) $telematicsJourney->gps_idle_duration) / 60;
        $totalIdleTime = number_format((float) $totalIdleTime, 2, '.', '');
        $timeDistanceFactor = ($journeyDistance > 0) ? (($distanceFactorInMiles * $totalDriveTime) / $journeyDistance) : 0;

        $accelerationScoreValue = ($safetyScorePercentage/100) * ($accelerationScorePercentage/100);
        $accelerationScore = $this->calculateBasedOnDistanceFactor($accelerationIncidentsCount, $distanceFactorInMiles, $journeyDistance, $accelerationScoreValue);
        $accelerationScore = number_format((float) $accelerationScore, 2, '.', '');

        $brakingScoreValue = ($safetyScorePercentage/100) * ($brakingScorePercentage/100);
        $brakingScore = $this->calculateBasedOnDistanceFactor($brakingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $brakingScoreValue);
        $brakingScore = number_format((float) $brakingScore, 2, '.', '');

        $corneringScoreValue = ($safetyScorePercentage/100) * ($corneringScorePercentage/100);
        $corneringScore = $this->calculateBasedOnDistanceFactor($corneringIncidentsCount, $distanceFactorInMiles, $journeyDistance, $corneringScoreValue);
        $corneringScore = number_format((float) $corneringScore, 2, '.', '');

        $speedingScoreValue = ($safetyScorePercentage/100) * ($speedingScorePercentage/100);
        $speedingScore = $this->calculateBasedOnDistanceFactor($speedingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $speedingScoreValue);
        $speedingScore = number_format((float) $speedingScore, 2, '.', '');

        $rpmScoreValue = ($efficiencyScorePercentage/100) * ($rpmScorePercentage/100);
        $rpmScore = $this->calculateBasedOnDistanceFactor($rpmIncidentsCount, $distanceFactorInMiles, $journeyDistance, $rpmScoreValue);
        $rpmScore = number_format((float) $rpmScore, 2, '.', '');

        $idlingScoreValue = ($efficiencyScorePercentage/100) * ($idleTimeScorePercentage/100);
        $idlingTimeFactor = ($journeyDistance > 0) ? (($idlingIncidentsCount * $distanceFactorInMiles) / $journeyDistance) : 0;
        $idleTimeFactor = ($totalDriveTime > 0) ? (($totalIdleTime * $timeDistanceFactor) / $totalDriveTime) : 0;
        $idlingScore = $this->calculateIdleScoreBasedOnTimeFactor($idlingScoreValue, $idlingTimeFactor, $idleTimeFactor);
        $idlingScore = number_format((float) $idlingScore, 2, '.', '');
        $idlingScore = $idlingScore < 0 ? 0 : $idlingScore;

        $scoreData = [
            'efficiency_score' => (($rpmScore + $idlingScore) / 2),
            'safety_score' => (($speedingScore + $corneringScore + $brakingScore + $accelerationScore) / 4),
            'rpm_score' => $rpmScore,
            'idle_score' => $idlingScore,
            'speeding_score' => $speedingScore,
            'cornering_score' => $corneringScore,
            'braking_score' => $brakingScore,
            'acceleration_score' => $accelerationScore,
        ];
        $telematicsJourneyRepo->updateScores($telematicsJourney, $scoreData);*/
        $telematicsJourneyRepo->updateScores($telematicsJourney);
    }

    // public function calculateJourneyScore($telematicsJournies)
    // {
    //     $telematicsJourneyRepo = new TelematicsJourneysRepository();
    //     $incidentTypes = array_keys(config('config-variables.telematics_incidents'));
    //     $totalCount = $telematicsJournies->count();

    //     $telematicsJourneyIds = $telematicsJournies->pluck('id');
    //     $allIncidents = TelematicsJourneyDetails::whereIn('telematics_journey_id', $telematicsJourneyIds)
    //                                             ->whereIn('ns', $incidentTypes)
    //                                             ->get();
    //     $accelerationIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.acc.l')->count();

    //     $corneringIncidentsCount = $allIncidents->filter(function ($incident) {
    //                                     return in_array($incident->ns, ['tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l']);
    //                                 })->count();
    //     $speedingIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.spd')->count();
    //     $brakingIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.dec.l')->count();
    //     $rpmIncidentsCount = $allIncidents->where('ns', 'tm8.dfb2.rpm')->count();
    //     $idlingIncidentsCount = $allIncidents->where('ns', 'tm8.gps.idle.start')->count();

    //     $distanceFactorInMiles = setting('distance_factor_in_miles');
    //     $safetyScorePercentage = setting('safety_score_percentage');
    //     $efficiencyScorePercentage = setting('efficiency_score_percentage');
    //     $accelerationScorePercentage = setting('acceleration_score_percentage');
    //     $brakingScorePercentage = setting('braking_score_percentage');
    //     $corneringScorePercentage = setting('cornering_score_percentage');
    //     $speedingScorePercentage = setting('speeding_score_percentage');
    //     $rpmScorePercentage = setting('rpm_score_percentage');
    //     $idleTimeScorePercentage = setting('idle_time_score_percentage');

    //     $totalGPSDistance = $telematicsJournies->sum('gps_distance');
    //     $journeyDistance = number_format((float)($totalGPSDistance * 0.00062137), 2, '.', '');

    //     $totalEngineDuration = $telematicsJournies->sum('engine_duration');
    //     $totalDriveTime = ((int) $totalEngineDuration) / 60;
    //     $totalDriveTime = number_format((float) $totalDriveTime, 2, '.', '');

    //     $totalGPSIdleDuration = $telematicsJournies->sum('gps_idle_duration');
    //     $totalIdleTime = ((int) $totalGPSIdleDuration) / 60;
    //     $totalIdleTime = number_format((float) $totalIdleTime, 2, '.', '');
    //     $timeDistanceFactor = ($journeyDistance > 0) ? (($distanceFactorInMiles * $totalDriveTime) / $journeyDistance) : 0;

    //     $accelerationScoreValue = ($safetyScorePercentage/100) * ($accelerationScorePercentage/100);
    //     $accelerationScore = $this->calculateBasedOnDistanceFactor($accelerationIncidentsCount, $distanceFactorInMiles, $journeyDistance, $accelerationScoreValue);
    //     $accelerationScore = number_format((float) $accelerationScore, 2, '.', '');

    //     $brakingScoreValue = ($safetyScorePercentage/100) * ($brakingScorePercentage/100);
    //     $brakingScore = $this->calculateBasedOnDistanceFactor($brakingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $brakingScoreValue);
    //     $brakingScore = number_format((float) $brakingScore, 2, '.', '');

    //     $corneringScoreValue = ($safetyScorePercentage/100) * ($corneringScorePercentage/100);
    //     $corneringScore = $this->calculateBasedOnDistanceFactor($corneringIncidentsCount, $distanceFactorInMiles, $journeyDistance, $corneringScoreValue);
    //     $corneringScore = number_format((float) $corneringScore, 2, '.', '');

    //     $speedingScoreValue = ($safetyScorePercentage/100) * ($speedingScorePercentage/100);
    //     $speedingScore = $this->calculateBasedOnDistanceFactor($speedingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $speedingScoreValue);
    //     $speedingScore = number_format((float) $speedingScore, 2, '.', '');

    //     $rpmScoreValue = ($efficiencyScorePercentage/100) * ($rpmScorePercentage/100);
    //     $rpmScore = $this->calculateBasedOnDistanceFactor($rpmIncidentsCount, $distanceFactorInMiles, $journeyDistance, $rpmScoreValue);
    //     $rpmScore = number_format((float) $rpmScore, 2, '.', '');

    //     $idlingScoreValue = ($efficiencyScorePercentage/100) * ($idleTimeScorePercentage/100);
    //     $idlingTimeFactor = ($journeyDistance > 0) ? (($idlingIncidentsCount * $distanceFactorInMiles) / $journeyDistance) : 0;
    //     $idleTimeFactor = ($totalDriveTime > 0) ? (($totalIdleTime * $timeDistanceFactor) / $totalDriveTime) : 0;
    //     $idlingScore = $this->calculateIdleScoreBasedOnTimeFactor($idlingScoreValue, $idlingTimeFactor, $idleTimeFactor);
    //     $idlingScore = number_format((float) $idlingScore, 2, '.', '');
    //     $idlingScore = $idlingScore < 0 ? 0 : $idlingScore;

    //     $fuel = $telematicsJournies->sum('fuel');
    //     $co2 = $telematicsJournies->sum('co2');

    //     $scoreData = [
    //         'acceleration' => $accelerationScore,
    //         'braking' => $brakingScore,
    //         'cornering' => $corneringScore,
    //         'speeding' => $speedingScore,
    //         'safety' => (($speedingScore + $corneringScore + $brakingScore + $accelerationScore) / 4),
    //         'idle' => $idlingScore,
    //         'rpm' => $rpmScore,
    //         'efficiency' => (($rpmScore + $idlingScore) / 2),
    //         'fuel' => $fuel,
    //         'co2' => $co2,
    //         'gps_distance' => $totalGPSDistance,
    //         'engine_duration' => $totalEngineDuration,
    //     ];

    //     return $scoreData;
    // }

    public function calculateJourneyScore($journeySummary)
    {
        $accelerationIncidentsCount = $journeySummary->harsh_acceleration_count;
        $corneringIncidentsCount = $journeySummary->harsh_cornering_count;
        $speedingIncidentsCount = $journeySummary->speeding_count;
        $brakingIncidentsCount = $journeySummary->harsh_breaking_count;
        $rpmIncidentsCount = $journeySummary->rpm_count;
        $idlingIncidentsCount = $journeySummary->idling_count;

        $distanceFactorInMiles = setting('distance_factor_in_miles');
        $safetyScorePercentage = setting('safety_score_percentage');
        $efficiencyScorePercentage = setting('efficiency_score_percentage');
        $accelerationScorePercentage = setting('acceleration_score_percentage');
        $brakingScorePercentage = setting('braking_score_percentage');
        $corneringScorePercentage = setting('cornering_score_percentage');
        $speedingScorePercentage = setting('speeding_score_percentage');
        $rpmScorePercentage = setting('rpm_score_percentage');
        $idleTimeScorePercentage = setting('idle_time_score_percentage');

        $totalGPSDistance = $journeySummary->gps_distance;
        $journeyDistance = number_format((float)($totalGPSDistance * 0.00062137), 2, '.', '');

        $totalEngineDuration = $journeySummary->engine_duration;
        $totalDriveTime = ((int) $totalEngineDuration) / 60;
        $totalDriveTime = number_format((float) $totalDriveTime, 2, '.', '');

        $totalGPSIdleDuration = $journeySummary->gps_idle_duration;
        $totalIdleTime = ((int) $totalGPSIdleDuration) / 60;
        $totalIdleTime = number_format((float) $totalIdleTime, 2, '.', '');
        $timeDistanceFactor = ($journeyDistance > 0) ? (($distanceFactorInMiles * $totalDriveTime) / $journeyDistance) : 0;

        $accelerationScoreValue = ($safetyScorePercentage/100) * ($accelerationScorePercentage/100);
        $accelerationScore = $this->calculateBasedOnDistanceFactor($accelerationIncidentsCount, $distanceFactorInMiles, $journeyDistance, $accelerationScoreValue);
        $accelerationScore = number_format((float) $accelerationScore, 2, '.', '');

        $brakingScoreValue = ($safetyScorePercentage/100) * ($brakingScorePercentage/100);
        $brakingScore = $this->calculateBasedOnDistanceFactor($brakingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $brakingScoreValue);
        $brakingScore = number_format((float) $brakingScore, 2, '.', '');

        $corneringScoreValue = ($safetyScorePercentage/100) * ($corneringScorePercentage/100);
        $corneringScore = $this->calculateBasedOnDistanceFactor($corneringIncidentsCount, $distanceFactorInMiles, $journeyDistance, $corneringScoreValue);
        $corneringScore = number_format((float) $corneringScore, 2, '.', '');

        $speedingScoreValue = ($safetyScorePercentage/100) * ($speedingScorePercentage/100);
        $speedingScore = $this->calculateBasedOnDistanceFactor($speedingIncidentsCount, $distanceFactorInMiles, $journeyDistance, $speedingScoreValue);
        $speedingScore = number_format((float) $speedingScore, 2, '.', '');

        $rpmScoreValue = ($efficiencyScorePercentage/100) * ($rpmScorePercentage/100);
        $rpmScore = $this->calculateBasedOnDistanceFactor($rpmIncidentsCount, $distanceFactorInMiles, $journeyDistance, $rpmScoreValue);
        $rpmScore = number_format((float) $rpmScore, 2, '.', '');

        $idlingScoreValue = ($efficiencyScorePercentage/100) * ($idleTimeScorePercentage/100);
        $idlingTimeFactor = ($journeyDistance > 0) ? (($idlingIncidentsCount * $distanceFactorInMiles) / $journeyDistance) : 0;
        $idleTimeFactor = ($totalDriveTime > 0) ? (($totalIdleTime * $timeDistanceFactor) / $totalDriveTime) : 0;
        $idlingScore = $this->calculateIdleScoreBasedOnTimeFactor($idlingScoreValue, $idlingTimeFactor, $idleTimeFactor);
        $idlingScore = number_format((float) $idlingScore, 2, '.', '');
        $idlingScore = $idlingScore < 0 ? 0 : $idlingScore;

        $fuel = $journeySummary->fuel;
        $co2 = $journeySummary->co2;

        $accelerationScore = $accelerationScore == null? 100 : $accelerationScore;
        $brakingScore = $brakingScore == null? 100 : $brakingScore;
        $corneringScore = $corneringScore == null? 100 : $corneringScore;
        $speedingScore = $speedingScore == null? 100 : $speedingScore;
        $idlingScore = $idlingScore == null? 100 : $idlingScore;
        $rpmScore = $rpmScore == null? 100 : $rpmScore;
        $fuel = $fuel == null? 100 : $fuel;
        $co2 = $co2 == null? 100 : $co2;
        $totalGPSDistance = $totalGPSDistance == null? 100 : $totalGPSDistance;
        $totalEngineDuration = $totalEngineDuration == null? 100 : $totalEngineDuration;

        $scoreData = [
            'acceleration' => $accelerationScore,
            'braking' => $brakingScore,
            'cornering' => $corneringScore,
            'speeding' => $speedingScore,
            'safety' => (($speedingScore + $corneringScore + $brakingScore + $accelerationScore) / 4),
            'idle' => $idlingScore,
            'rpm' => $rpmScore,
            'efficiency' => (($rpmScore + $idlingScore) / 2),
            'fuel' => $fuel,
            'co2' => $co2,
            'gps_distance' => $totalGPSDistance,
            'engine_duration' => $totalEngineDuration,
        ];

        return $scoreData;
    }

    public function calculateBasedOnDistanceFactor($incidentsCount, $distanceFactorInMiles, $journeyDistance, $scoreValue)
    {
        $distanceTimeFactor = ($journeyDistance > 0) ? ($incidentsCount * $distanceFactorInMiles) / $journeyDistance : 0;
        return (100 - ($scoreValue * $distanceTimeFactor));
    }

    public function calculateIdleScoreBasedOnTimeFactor($idlingScoreValue, $idlingTimeFactor, $idleTimeFactor)
    {
        return (100 - (($idlingTimeFactor + $idleTimeFactor) * $idlingScoreValue));
    }

    public function checkEntryAndCreateAlert($value, $alert)
    {
        if(isset($value['journey_id']) && $value['journey_id'] != 0) {
            $journeyId = $value['journey_id'];
            // $userId = $value['user_id'];
            $userId = env('SYSTEM_USER_ID');//this system user will be updated by a different script if user is bound by sum.ex1 bind user call
            $vehicleId = $value['vehicle_id'];
            $journeyDetailsId = $value['journey_details_id'];
            $reportedAtDate = null;

            $check = Check::where('vehicle_id', $vehicleId)
                            ->whereDate('created_at', '=', Carbon::today())
                            ->where('type', '=', 'Vehicle Check')
                            ->first();

            if($check) {
                $reportedAtDate = Carbon::createFromFormat('Y-m-d H:i:s', $check->report_datetime);
            }
            $journeyStartDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $value['journey_start_date']);

            if(!isset($check) || ($reportedAtDate->gt($journeyStartDateTime))) {
                //first check alert exists or not for same journey id, vehicle id and user id

                $count = AlertNotifications::where('journey_id', $journeyDetailsId)
                                                //->where('user_id', $userId)
                                                ->where('alerts_id', $alert->id)
                                                ->where('vehicle_id', $vehicleId)
                                                ->count();

                if($count == 0) {
                    $alertId = $alert->id;
                    // $alertNotification = new AlertNotificationsRepository();
                    $alertNotification = new AlertNotifications();
                    $alertNotification->alerts_id = $alertId;
                    $alertNotification->user_id = $userId;
                    $alertNotification->vehicle_id = $vehicleId;
                    $alertNotification->journey_id = $journeyDetailsId;
                    $alertNotification->alert_date_time = $value['journey_start_date'];
                    $alertNotification->save();
                }
            }
        }
    }

    public function updateZoneAlertSession($value, $existZoneAlert) {
        $zoneAlertSessionId = $existZoneAlert->id;
        $zoneAlertRepo = new ZoneAlertsRepository();
        $zoneAlertSessionCount = $zoneAlertRepo->getAlertCountBySessionId($zoneAlertSessionId);
        //\Log::info('check zone alert entry inside table & status inomplete & end time is null');
        if($zoneAlertSessionCount > 0 && $existZoneAlert->status == 'incomplete' && $existZoneAlert->end_time == null) {
            //\Log::info('update end time because lat/lng outside polygon');
            $zoneRepo = new ZonesRepository();
            $endTime = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
            $zoneRepo->updateEndTimeAlertSession($zoneAlertSessionId, $endTime);
        }
    }

    public function createZoneAlertSessionEntry($zoneAlertSessionId, $value) {
        $zoneAlertRepo = new ZoneAlertsRepository();
        $data = array();
        $data['zone_alert_session_id'] = $zoneAlertSessionId;
        $data['speed'] =  $value['speed'];
        $data['max_acceleration'] = 1;
        $data['direction'] = $value['heading'];
        if(isset($value['street']) && isset($value['town']) && isset($value['postcode'])) {
            $data['address'] = $value['street'].', '.$value['town'].', '.$value['postcode'];
        }
        $data['latitude'] = $value['lat'];
        $data['longitude'] = $value['lon'];
        $data['created_at'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
        $data['updated_at'] = isset($value['time'])?Carbon::parse($value['time'])->setTimezone('UTC')->format('Y-m-d H:i:s'):null;
        return $zoneAlertRepo->createAlertSession($data);
    }
}
