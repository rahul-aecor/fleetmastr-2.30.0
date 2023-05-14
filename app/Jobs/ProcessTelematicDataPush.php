<?php

namespace App\Jobs;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsTempJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Services\TelematicsService;
use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\TelematicsJourneysRepository;
use App\Repositories\RedisJourneysRepository;

use App\Events\TelematicsJourneyEnd;
use App\Events\TelematicsJourneyStart;
use App\Events\TelematicsJourneyIdling;
use App\Events\TelematicsJourneyOngoing;
use App\Jobs\ProcessStreetSpeed;

class ProcessTelematicDataPush extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $dataFromTrakm8Array;
    // protected $vehiclesMap;
    // protected $telematicsJourneysMap;
    public function __construct($dataFromTrakm8Array)
    {
        $this->dataFromTrakm8Array = $dataFromTrakm8Array;
        //$this->vehiclesMap = Vehicle::get()->keyBy('registration');
        /*$this->telematicsJourneysMap = TelematicsJourneys::whereNull('end_time')->select('id','journey_id','uid','vrn','vehicle_id')->get()->keyBy(function ($item) {
            return strtoupper($item['vehicle_id'].'_'.$item['uid'].'_'.$item['journey_id']);
        })->toArray();
        ///discuss following with Nitin bhai
        $redisJourneysRepository = new RedisJourneysRepository();
        foreach($this->telematicsJourneysMap as $tj) {
            $redisJourneysRepository->create($tj);
        }*/
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    private function fetchJourneyId($registration, $journey_id, $uid, $vehiclesMap){
        $redisJourneysRepository = new RedisJourneysRepository();
        //$vehiclesMap = Vehicle::select('id', 'registration', 'telematics_ns', 'telematics_lat', 'telematics_lon', 'telematics_odometer', 'telematics_latest_location_time', 'telematics_postcode', 'telematics_street', 'telematics_town', 'telematics_latest_journey_id', 'telematics_latest_journey_time')->get()->keyBy('registration');
        //$telematicsJourney = TelematicsJourneys::where(['vrn'=>$registration,'journey_id'=>$journey_id])->whereNull('end_time')->first();
        $vehicle = isset($vehiclesMap[$registration]) ? $vehiclesMap[$registration] : null;
        $key = ($vehicle->id.'_'.$uid.'_'.$journey_id);
        //$telematicsJourney = $this->telematicsJourneysMap[$key];
        $telematicsJourney = $redisJourneysRepository->getJourneyEntry($key);
        // print_r($telematicsJourney);
        return $telematicsJourney['id'];

    }



    public function handle()
    {
        $telematicsService = new TelematicsService();
        $redisJourneysRepository = new RedisJourneysRepository();
        $vehiclesMap = Vehicle::select('id', 'registration', 'telematics_ns', 'telematics_lat', 'telematics_lon', 'telematics_odometer', 'telematics_latest_location_time', 'telematics_postcode', 'telematics_street', 'telematics_town', 'telematics_latest_journey_id', 'telematics_latest_journey_time')->get()->keyBy('registration');
        //$nsToProcess = ['tm8.gps.ign.on', 'tm8.gps.jny.start', 'tm8.gps', 'tm8.dfb2.acc.l', 'tm8.gps.ign.off', 'tm8.jny.sum.ex1', 'tm8.gps.jny.end', 'tm8.dfb2.dec.l', 'tm8.gps.can.idle.start', 'tm8.gps.idle.start', 'tm8.gps.exces.idle', 'tm8.gps.idle.ongoing', 'tm8.gps.can.idle.end', 'tm8.gps.idle.end', 'tm8.dfb2.spd', 'tm8.dfb2.rpm','tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l', 'tm8.jny.score', 'tm8.jny.sum'];//, 'tm8.battery.profile.generated', 'tm8.gps.rfid.entry'];
        //$nsToProcess = ['tm8.gps.jny.start', 'tm8.gps', 'tm8.dfb2.acc.l',  'tm8.jny.sum.ex1', 'tm8.gps.jny.end', 'tm8.dfb2.dec.l', 'tm8.gps.can.idle.start', 'tm8.gps.idle.start', 'tm8.gps.exces.idle', 'tm8.gps.idle.ongoing', 'tm8.gps.can.idle.end', 'tm8.gps.idle.end', 'tm8.dfb2.spd', 'tm8.dfb2.rpm','tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l', 'tm8.jny.score', 'tm8.jny.sum'];
        $nsToProcess = ['tm8.fnol','tm8.gps.jny.start', 'tm8.gps', 'tm8.dfb2.acc.l',  'tm8.jny.sum.ex1', 'tm8.gps.jny.end', 'tm8.dfb2.dec.l', 'tm8.gps.can.idle.start', 'tm8.gps.idle.start', 'tm8.gps.exces.idle', 'tm8.gps.idle.ongoing', 'tm8.gps.can.idle.end', 'tm8.gps.idle.end', 'tm8.dfb2.spd', 'tm8.dfb2.rpm','tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l', 'tm8.jny.sum'];
        $nsSkippedForJourneyDetailsEntry = ['tm8.gps.jny.start','tm8.jny.sum','tm8.jny.score','tm8.gps.rfid.entry'];
        $latestVehicleEventInChunk_Map = [];
        $latestVehicleUpdateInChunk_Map = [];
        //foreach ($this->dataFromTrakm8Array as $key => $value) {
        $tempDataArraySize = 0;
       // $dataArraySizeToItterate = ;
        for ($i=0; $i < ((int)count($this->dataFromTrakm8Array)) ; $i++) { 
            $value = $this->dataFromTrakm8Array[$i];
            if(!is_array($value)) {
                continue;
            }
            $ns = isset($value['ns'])?$value['ns']:null;
            if (in_array($ns, $nsToProcess)) {
		        $value['vrn'] = trim(str_replace(' ', '', $value['vrn']));
                $vehicle = isset($vehiclesMap[$value['vrn']]) ? $vehiclesMap[$value['vrn']] : null;
                // $vehicle = $vehicles->where('registration' , $value['vrn'])->first();
                if ($vehicle != null) {
                    $telematicsJourneysMapKey = strtoupper($vehicle->id.'_'.$value['uid'].'_'.$value['journey_id']);
                    
                    //$telematicsJourney = array_key_exists($telematicsJourneysMapKey, $this->telematicsJourneysMap) ? $this->telematicsJourneysMap[$telematicsJourneysMapKey] : null;
                    $telematicsJourney = $redisJourneysRepository->isJourneyEntryExisting($telematicsJourneysMapKey) ? $redisJourneysRepository->getJourneyEntry($telematicsJourneysMapKey) : null;
                    if ($telematicsJourney == null && $ns != 'tm8.gps.jny.start') {
                        //// //print_r('not in open journeys map ie telematicsJourneysMapKey:'.$telematicsJourneysMapKey);
                        //Push data to temp table and continue
                        $telematicsTempJourneyDetails = new TelematicsTempJourneys();
                        $telematicsTempJourneyDetails->uid = $value['uid'];
                        $telematicsTempJourneyDetails->vrn = $vehicle->registration;
                        $telematicsTempJourneyDetails->journey_id = $value['journey_id'];
                        $telematicsTempJourneyDetails->raw_json = json_encode($value) ;
                        $telematicsTempJourneyDetails->save();
                        continue;
                    }
                    $vehicleEventDetails = [];
                   
                   if ($ns == 'tm8.gps.jny.start') {    
                        // //print_r('tm8.gps.jny.start');
                        $telematicsJourney = new TelematicsJourneysRepository();
                        $telematicsJourneyDetails = new TelematicsJourneyDetailsRepository();
                        $telematicsJourney = $telematicsJourney->create($value);
                        /*if($telematicsJourney) {
                            $telematicsJourneyDetails->updateJourneyIdForIgnon($value['vrn'],$telematicsJourney->id);
                        }*/
                        $telematics_temp_journeys = TelematicsTempJourneys::where(['vrn'=>$value['vrn'],'journey_id'=>$value['journey_id'],'uid'=>$value['uid']])->lists('raw_json');
                        
                        foreach($telematics_temp_journeys as $journey) {
                            array_push($this->dataFromTrakm8Array, json_decode($journey,true));
                            $tempDataArraySize = $tempDataArraySize + 1 ;
                        }
                        
                        TelematicsTempJourneys::where(['vrn'=>$value['vrn'],'journey_id'=>$value['journey_id'],'uid'=>$value['uid']])->delete();
                        //$this->telematicsJourneysMap[$telematicsJourneysMapKey] = $telematicsJourney;
                        $tjArray = ['id'=>$telematicsJourney->id,'journey_id'=>$telematicsJourney->journey_id,'uid'=>$telematicsJourney->uid,'vrn'=>$telematicsJourney->vrn,'vehicle_id'=>$telematicsJourney->vehicle_id];
                        $redisJourneysRepository->createJourney($tjArray);
                    }
                    else if ($ns == 'tm8.jny.sum') {
                        $telematicsJourney = new TelematicsJourneysRepository();
                        $telematicsJourney = $telematicsJourney->update($value);

                        if($telematicsJourney) {                            
                            
                            $telematicsService->processScore($telematicsJourney);
                            dispatch(new ProcessStreetSpeed($telematicsJourney->id));
                        }
                        //unset($this->telematicsJourneysMap[$telematicsJourneysMapKey]);
                    }
                    else{
                        if($ns == 'tm8.jny.sum.ex1'){
                            $telematicsService->bindUser($value);
                        }
                        $telematicsJourneyDetails = new TelematicsJourneyDetailsRepository();
                        if (isset($value['speed']) && $value['speed'] < 57) {
                            //data having speed greater than 57 mps is considered as junk so we skip that data 
                            $value['telematicsJourneyId'] = $this->fetchJourneyId($value['vrn'],$value['journey_id'],$value['uid'],$vehiclesMap);
                            $lastJourneyDetails = $telematicsJourneyDetails->create($value);
                            $value['journey_details_id'] = $lastJourneyDetails['id'];
                        }
                        else if (!isset($value['speed'])) {
                            // comes here incase like idle.ongoing etc
                            $value['telematicsJourneyId'] = $this->fetchJourneyId($value['vrn'],$value['journey_id'],$value['uid'],$vehiclesMap);
                            $lastJourneyDetails = $telematicsJourneyDetails->create($value);
                            $value['journey_details_id'] = $lastJourneyDetails['id'];
                        }

                    }

                    $idlingEvents = config('config-variables.idling_events');
                    $movingEvents = config('config-variables.moving_events');
                    $stoppedEvents = config('config-variables.stopped_events');
                    $startEvents = config('config-variables.start_events');

                    //conditions to populate events
                    //if ($ns == 'tm8.gps.jny.start') {   
                    if (in_array($ns, $startEvents)) {
                        $payload = [
                            'vehicle_id' => $value['vrn'],
                            'lat' => $value['lat'],
                            'lng' => $value['lon'],
                        ];
                        $vehicleEventDetails['event'] = 'TelematicsJourneyStart';
                        $vehicleEventDetails['payload'] = $payload;                   
                    }
                    //else if ($ns == 'tm8.jny.sum') {
                    if (in_array($ns, $stoppedEvents)) {
      
                        $payload = [
                            'vehicle_id' => $value['vrn'],
                        ];
                        $vehicleEventDetails['event'] = 'TelematicsJourneyEnd';
                        $vehicleEventDetails['payload'] = $payload;
                        
                    }
                    //else if ($ns == 'tm8.gps.idle.start' || $ns == 'tm8.gps.idle.end' || $ns == 'tm8.gps.can.idle.start' || $ns == 'tm8.gps.can.idle.end' || $ns == 'tm8.gps.exces.idle '|| $ns == 'tm8.gps.idle.ongoing') {
                    if (in_array($ns, $idlingEvents)) {
                        $payload = [
                            'vehicle_id' => $value['vrn'],
                        ];
                        $vehicleEventDetails['event'] = 'TelematicsJourneyIdling';
                        $vehicleEventDetails['payload'] = $payload;
                    }
                    //else{
                    if (in_array($ns, $movingEvents)) {
                        if (isset($value['vrn']) && isset($value['lat']) && isset($value['lon'])) {
                            // code...
                            $payload = [
                                'vehicle_id' => $value['vrn'],
                                'lat' => $value['lat'],
                                'lng' => $value['lon'],
                            ];
                        $vehicleEventDetails['event'] = 'TelematicsJourneyOngoing';
                        $vehicleEventDetails['payload'] = $payload;
                        }
                    }

                    $latestVehicleEventInChunk_Map[$value['vrn']]=$vehicleEventDetails;

                    //conditions to populate vehicles table
                    if ($value['ns'] != '') {
                        $latest_array = array_key_exists($value['vrn'], $latestVehicleUpdateInChunk_Map)?$latestVehicleUpdateInChunk_Map[$value['vrn']]:[];
                        $curr_last_location_time = isset($value['time'])?$value['time']:(isset($value['end_time'])?$value['end_time']:null);
                        //$prev_last_location_time = isset($latestVehicleUpdateInChunk_Map[$value['vrn']])?$latestVehicleUpdateInChunk_Map[$value['vrn']]['telematics_last_location_time']:null;
                        $prev_last_location_time = isset($latest_array['telematics_last_location_time'])?$latest_array['telematics_last_location_time']:null;
                        if ($prev_last_location_time == null || ($prev_last_location_time != null && strtotime($curr_last_location_time) >= strtotime($prev_last_location_time))) {
                            //$odometer = isset($value['odometer'])?$value['odometer']:null;
                            $odometer = isset($value['can_odo'])?$value['can_odo']:null;
                            if (!in_array($value['ns'], $nsSkippedForJourneyDetailsEntry)) {
                                $latest_array['telematics_ns']=$value['ns'];
                            }else{
                                if ($value['ns']=='tm8.gps.jny.start' || $value['ns']=='tm8.gps.rfid.entry') {
                                    $latest_array['telematics_ns'] = 'tm8.gps.ign.on';
                                }
                                elseif ($value['ns']=='tm8.jny.sum' || $value['ns']=='tm8.jny.score' || $value['ns']=='tm8.fnol') {
                                    $latest_array['telematics_ns'] = 'tm8.gps.ign.off';
                                }
                            }
                            //$latest_array['telematics_last_location_time']=$last_location_time;
                            
                            if ($value['ns'] == 'tm8.jny.sum') {
                              $telematicsJourney = TelematicsJourneys::where(['vrn'=>$value['vrn'], 'journey_id'=>$value['journey_id'], 'uid'=>$value['uid']])->withTrashed()->first();
                              if($telematicsJourney){
                                $latest_array['telematics_lat']=$value['end_lat'];
                                $latest_array['telematics_lon']=$value['end_lon'];
                                $latest_array['is_summary_in_chunk']=1;
                                $latest_array['telematics_last_journey_id']=$telematicsJourney->id;
                                $latest_array['telematics_last_journey_time']=$telematicsJourney->start_time;
                                $latest_array['telematics_odometer']=$odometer;
                                //$last_location_time = isset($value['end_time'])?$value['end_time']:null;
                                $latest_array['telematics_last_location_time']=$curr_last_location_time;
                              }
                            }
                            else{
                                if (isset($value['lat']) && $value['lat'] != '' && $value['lat'] != 0 && isset($value['lon']) && $value['lon'] != '' && $value['lon'] != 0) {
                                    // $latest_array['telematics_ns']=$value['ns'];
                                    $latest_array['telematics_lat']=$value['lat'];
                                    $latest_array['telematics_lon']=$value['lon'];
                                    // $latest_array['telematics_odometer']=$odometer;
                                    $latest_array['telematics_last_location_time']=$curr_last_location_time;
                                    $latest_array['is_summary_in_chunk']=(isset($latest_array['is_summary_in_chunk']) && $latest_array['is_summary_in_chunk'] == 1)?$latest_array['is_summary_in_chunk']:0;
                                    if (isset($value['postcode'])) {
                                        $latest_array['telematics_postcode']=$value['postcode'];
                                        $latest_array['telematics_street']=$value['street'];
                                        $latest_array['telematics_town']=$value['town'];
                                    }
                                }
                            }
                            $latestVehicleUpdateInChunk_Map[$value['vrn']] = $latest_array;
                        }

                        //$latestVehicleUpdateInChunk_Map[$value['vrn']]=['telematics_ns'=>$value['ns'],'telematics_lat'=>$value['lat'],'telematics_lon'=>$value['lon'], 'telematics_odometer'=>$odometer];
                    }
                    if($value['ns'] == 'tm8.gps'){
                        //dispatch(new TelematicsProcessZoneData($value, $this));
                        //$thisZoneData=new TelematicsService();
                        $telematicsService->processZoneData($value);
                    }
                }
                else {
                    //data coming for vrn that is not registered with the system
                }
            }
        }
    //// //print_r($latestVehicleUpdateInChunk_Map);exit;
        //loop to update vehicles
        foreach ($latestVehicleUpdateInChunk_Map as $key => $latestVehicleData) {  
            $vehicle = isset($vehiclesMap[$key]) ? $vehiclesMap[$key] : null;
            $vehicle->telematics_ns = $latestVehicleData['telematics_ns'];

            $vehicle->telematics_lat = $latestVehicleData['telematics_lat'];
            $vehicle->telematics_lon = $latestVehicleData['telematics_lon'];
            if (isset($latestVehicleData['telematics_odometer'])) {
                $vehicle->telematics_odometer = $latestVehicleData['telematics_odometer'];
            }
            if (isset($latestVehicleData['telematics_last_location_time'])){
                // $vehicle->telematics_latest_location_time = $latestVehicleData['telematics_last_location_time'];
                $vehicle->telematics_latest_location_time = Carbon::parse($latestVehicleData['telematics_last_location_time'])->setTimezone('UTC');
            }
            if (isset($latestVehicleData['telematics_postcode'])){
                $vehicle->telematics_postcode = $latestVehicleData['telematics_postcode'];
                $vehicle->telematics_street = $latestVehicleData['telematics_street'];
                $vehicle->telematics_town = $latestVehicleData['telematics_town'];
            }

            if ($latestVehicleData['is_summary_in_chunk'] == 1) {
                $vehicle->telematics_latest_journey_id = $latestVehicleData['telematics_last_journey_id'];
                $vehicle->telematics_latest_journey_time = $latestVehicleData['telematics_last_journey_time'];
            }
            $vehicle->save();
        }                 
        //loop to fire events
        foreach ($latestVehicleEventInChunk_Map as $key => $latestVehicleEvent) { 

            if (isset($latestVehicleEvent['event']) && isset($latestVehicleEvent['payload'])) {
                if ($latestVehicleEvent['event'] == 'TelematicsJourneyStart') {
                    event(new TelematicsJourneyStart($latestVehicleEvent['payload']));
                }
                else if ($latestVehicleEvent['event'] == 'TelematicsJourneyEnd') {
                    event(new TelematicsJourneyEnd($latestVehicleEvent['payload']));
                }
                else if ($latestVehicleEvent['event'] == 'TelematicsJourneyIdling') {
                    event(new TelematicsJourneyIdling($latestVehicleEvent['payload']));
                }
                else if ($latestVehicleEvent['event'] == 'TelematicsJourneyOngoing') {
                    event(new TelematicsJourneyOngoing($latestVehicleEvent['payload']));
                }
            }
            else {
                \Log::info("Event payload is not registered for key: ".$key);
                // \Log::info($key);
            }
        }

    }
}
