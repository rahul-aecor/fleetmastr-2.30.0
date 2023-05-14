<?php

namespace App\Jobs;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use \Illuminate\Support\Facades\DB;

use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsTempJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Models\TeletrackJourneyDetailsMapping;

use App\Services\TelematicsService;

use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\TelematicsJourneysRepository;

use App\Events\TelematicsJourneyEnd;
use App\Events\TelematicsJourneyStart;
use App\Events\TelematicsJourneyIdling;
use App\Events\TelematicsJourneyOngoing;

use App\Jobs\ProcessStreetSpeed;

use App\Custom\Client\GoogleMap;

class ProcessTeletracDataPush extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $dataFromTeletrack;
    
    public function __construct($dataFromTeletrack)
    {
        $this->dataFromTeletrack = $dataFromTeletrack;
    }

    private function populateTelematicsJourneyDetailsArray($ns, $data, $vehiclesMap){

        $telematics_journey_id = 0;
        $value = [];
        $vrn = '';
        $googleClient = new GoogleMap();
        if (!empty($data)){
            if (isset($data['vehicle'])) {
                $vrn = $data['vehicle']['registration'];
                $vrn = str_replace(' ', '', $vrn);
                $value['vrn'] = $vrn;
                $vehicle = isset($vehiclesMap[$vrn]) ? $vehiclesMap[$vrn] : null;
                if ($vehicle != null) {
                    $value['vehicle_id'] = $vehicle->id;
                }
            }
        }
        $value['ns'] = $ns;
        if (isset($data['GPS'])) {
            //$vehicle->telematics_ns = 'tm8.gps';
            $value['lat'] = $data['GPS']['Lat'];
            $value['lon'] = $data['GPS']['Lng'];
            $value['speed'] = $data['GPS']['Spd'];
            $value['heading'] = $data['GPS']['Dir'];
            if (isset($data['result']) && isset($data['result']['location2'])) {
                $value['town'] = isset($data['result']['location2']['city'])?$data['result']['location2']['city']:NULL;
                $value['street'] = isset($data['result']['location2']['street'])?$data['result']['location2']['street']:NULL;
                $value['postcode'] = isset($data['result']['location2']['postcode'])?$data['result']['location2']['postcode']:NULL;
            }
        }
        elseif(isset($data['ignOffGPS'])){
            $value['lat'] = $data['ignOffGPS']['Lat'];
            $value['lon'] = $data['ignOffGPS']['Lng'];
            $value['speed'] = $data['ignOffGPS']['Spd'];
            $value['heading'] = $data['ignOffGPS']['Dir'];
            if (isset($data['result']) && isset($data['result']['location2'])) {
                $value['town'] = isset($data['result']['location2']['city'])?$data['result']['location2']['city']:NULL;
                $value['street'] = isset($data['result']['location2']['street'])?$data['result']['location2']['street']:NULL;
                $value['postcode'] = isset($data['result']['location2']['postcode'])?$data['result']['location2']['postcode']:NULL;
            }
            else {
                $address = $googleClient->getAddressFromll($value['lat'],$value['lon']);
                $value['town'] = $address['town'];
                $value['street'] = $address['street'];
                $value['postcode'] = $address['postal_code'];
            }
        }
        if(isset($data['eventAt'])){
            $value['time'] = Carbon::parse($data['eventAt'])->setTimezone('UTC');
        }
        $value['dallas_key'] = 0 ;
        //$value['raw_json'] = json_encode($data);
        $value['journey_id'] = isset($data['tripId'])?$data['tripId']:0;
        return $value;
                  
        //TODO :          $value['odometer'] = int(11) DEFAULT NULL,
        //TODO :          $value['gps_odo'] = int(11) DEFAULT NULL,
        //TODO :          $value['street_speed'] = int(11) DEFAULT NULL,

        // remove        $value['idle_duration'] = int(11) DEFAULT NULL,
        // remove        $value['gps_distance'] = int(11) DEFAULT NULL,
        // remove        $value['mile'] = varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        // remove        $value['vin'] = varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
        // remove        $value['ex_idle_threshold'] = int(11) DEFAULT NULL,
        // remove        $value['idle_threshold'] = int(11) DEFAULT NULL,
        // remove        $value['num_stats'] = int(11) DEFAULT NULL,

    }

    private function callJourneysApi($vehicleId)
    {
        
                $ch = curl_init();
                $from1 = Carbon::now()->subMinutes(30)->format('Y-m-d');
                $from2 = Carbon::now()->subMinutes(30)->format('H:i:s');
                // $from = '2022-10-11T00:00:01';
                $from = $from1.'T'.$from2;
                // $url = 'https://api-uk.nextgen.teletracnavman.net/v1/trips?vehicleId=16503&from=2022-10-13T00:00:09&event_types=IOR,VPM_IT,SPEED,GEOFENCE,VPM_HC,VPM_IM,VPM_HB,VPM_OR,VPM_EA,VPM_EOP,VPM_ECT,VPM_EOT,ALARM,PRETRIP,FORM,ALERT,PTO,CAMERA,DRIVER,MASS,FATIGUE,GPIO&embed=meters,events';
                $baseURL = 'https://api-uk.nextgen.teletracnavman.net/v1/';
                $url = $baseURL.'trips?vehicleId='.$vehicleId;
                // $url = $url.'&from='.$from;
                $url = $url.'&event_types=IOR,VPM_IT,SPEED,GEOFENCE,VPM_HC,VPM_IM,VPM_HB,VPM_OR,VPM_EA,VPM_EOP,VPM_ECT,VPM_EOT,ALARM,PRETRIP,FORM,ALERT,PTO,CAMERA,DRIVER,MASS,FATIGUE,GPIO&embed=meters,events';
// print_r($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Authorization: Token token="'.env('TELETRAC_KEY').'"' ,
                            'Content-Type: application/json'
                            ));
                $output = curl_exec($ch);
                $resp = json_decode($output,true);
                curl_close($ch);
                return $resp;
    }

    private function fetchAndMatchJourneys($vehicle) {
            $vehicleId = $vehicle->webfleet_object_id;
            $resp = $this->callJourneysApi($vehicleId);
            $googleClient = new GoogleMap();
            // $journeyIds = TelematicsJourneys::where('start_time','>=',Carbon::now()->subDay(8))->select('jounery_id','vrn')->get()->keyBy('registration');
            $journeyIds = TelematicsJourneys::where('start_time','>=',Carbon::now()->subDay(8))->select('id','journey_id','vrn')->get()->keyBy(function ($item) {
                return strtoupper($item['vrn'].'_'.$item['journey_id']);
            })->toArray();
            
            foreach($resp as $journey){
                $journeyToStore = new TelematicsJourneys();
                //if (isset($journey['id']) && !array_key_exists(strtoupper($vehicle->registration.'_'.$journey['id']),$journeyIds)) {                
                if (isset($journey['tripId']) && !array_key_exists(strtoupper($vehicle->registration.'_'.$journey['tripId']),$journeyIds) && isset($journey['IgnOffGPS'])) {                
                    //$journeyToStore->journey_id = $journey['id'];
                    if (!isset($journey['IgnOffGPS'])) {
                        \Log::info("journey======");
                        \Log::info($journey);
                    }
                    $journeyToStore->journey_id = $journey['tripId'];

                    /*
                    * FLEE-6835 - Farzan
                    */
                    $journeyToStore->user_id = $vehicle->nominated_driver ? $vehicle->nominated_driver : env('SYSTEM_USER_ID');

                    $journeyToStore->start_lat = $journey['IgnOnGPS']['Lat'];
                    $journeyToStore->start_lon = $journey['IgnOnGPS']['Lng'];

                    $journeyToStore->end_lat = isset($journey['IgnOffGPS'])?$journey['IgnOffGPS']['Lat']:null;
                    $journeyToStore->end_lon = isset($journey['IgnOffGPS'])?$journey['IgnOffGPS']['Lng']:null;
                    $journeyToStore->gps_distance = $journey['distanceEnd']-$journey['distanceStart'];

                    $journeyToStore->engine_duration = $journey['calculatedGpsDiffEngineHour'];
                    $journeyToStore->fuel = $journey['calculatedFuelConsumption'];
                    $journeyToStore->dallas_key = '';//NA
                    $journeyToStore->co2 = null;//NA
                    //$journeyToStore->start_street = $journey['startLocation'];
                    if($journey['startLocation'] && $journey['startLocation'] != '') {
                        $start_address = $googleClient->setJourneyAddress($journey['startLocation']);
                    }
                    else {
                        $start_address = $googleClient->getAddressFromll($journey['IgnOnGPS']['Lat'],$journey['IgnOnGPS']['Lng']);
                    }
                    $journeyToStore->start_street = $start_address['street'];
                    $journeyToStore->start_town = $start_address['town'];
                    $journeyToStore->start_post_code = $start_address['postal_code'];
                    //$journeyToStore->end_street = $journey['endLocation'];
                    if($journey['endLocation'] && $journey['endLocation'] != '') {
                        $end_address = $googleClient->setJourneyAddress($journey['endLocation']);
                    }
                    else {
                        $start_address = $googleClient->getAddressFromll($journey['IgnOffGPS']['Lat'],$journey['IgnOffGPS']['Lng']);
                    }
                    $journeyToStore->end_street = $end_address['street'];
                    $journeyToStore->end_town = $end_address['town'];
                    $journeyToStore->end_post_code = $end_address['postal_code'];
                    
                    $journeyToStore->odometer_start = $journey['gpsOdoStart'] *1609.344;
                    $journeyToStore->odometer_end = $journey['gpsOdoEnd']*1609.344;
                    $journeyToStore->odometer = $journey['gpsOdoEnd']*1609.344;
                    $journeyToStore->gps_distance = $journeyToStore->odometer_end - $journeyToStore->odometer_start;
                    $journeyToStore->raw_json = json_encode($journey);

                    $journeyToStore->vehicle_id = $vehicle->id;//$journey['vehicle']['id'];
                    $journeyToStore->vrn = $vehicle->registration;
                    
                    $journeyToStore->start_time = date('Y-m-d H:i:s', $journey['ignitionOn']/1000);// -> convert to readable;
                    // $journeyToStore->start_time = Carbon::now();// -> convert to readable;
                    $journeyToStore->end_time = date('Y-m-d H:i:s', $journey['ignitionOff']/1000);// -> convert to readable;

                    $journeyToStore->is_details_added = 0;
                    $journeyToStore->save();

                    $telematics_journey_id = $journeyToStore->id;
                    $journeyDetailsIds = TeletrackJourneyDetailsMapping::where('teletrack_journey_id',$journeyToStore->journey_id)
                                                                        ->where('vrn',$vehicle->registration)->lists('telematics_journey_details_id')->toArray();
                    $telematicsJourneyDetails = TelematicsJourneyDetails::whereIn('id',$journeyDetailsIds)->update(['telematics_journey_id' => $telematics_journey_id]);
                    $journeyDetailsMappingIds = TeletrackJourneyDetailsMapping::where('teletrack_journey_id',$journeyToStore->journey_id)
                    ->where('vrn',$vehicle->registration)->lists('id')->toArray();
                    
                    TeletrackJourneyDetailsMapping::find($journeyDetailsMappingIds)->each(function ($mapping, $key) {
                        $mapping->delete();
                    });

                }

            }
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


    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle()
    {
        $vehicleMap = [];
        $data = $this->dataFromTeletrack;
        $vehiclesMap = Vehicle::select('id', 'registration', 'telematics_ns', 'telematics_lat', 'telematics_lon', 'telematics_odometer', 'telematics_latest_location_time', 'telematics_postcode', 'telematics_street', 'telematics_town', 'telematics_latest_journey_id', 'telematics_latest_journey_time', 'webfleet_object_id')->get()->keyBy('registration');

        if (!empty($data)){
            if (isset($data['vehicle'])) {
                $vrn = $data['vehicle']['registration'];
                $vrn = str_replace(' ', '', $vrn);
                $ns = '';
                $lat = '';
                $lon = '';
                $vehicle = isset($vehiclesMap[$vrn]) ? $vehiclesMap[$vrn] : null;
                if ($vehicle != null) {
                    //$ns = '';
                    if(isset($data['ignOffGPS'])){
                        $ns = 'tm8.jny.sum';
                    }
                    else if (isset($data['ignition']) && $data['ignition'] == 'OFF') {
                        $ns = 'tm8.gps.jny.end';
                    }
                    else if (($vehicle->telematics_ns == 'tm8.gps.jny.end' || $vehicle->telematics_ns == 'tm8.jny.sum') && isset($data['ignition']) && $data['ignition'] == 'ON') {
                            $ns = 'tm8.gps.jny.start';
                    }
                    else if (isset($data['type']) && !empty($data['type'])){                        
                        if (isset($data['type']) && $data['type'] == 'accelerate') {
                            $ns = 'tm8.dfb2.acc.l';
                        }
                        elseif (isset($data['type']) && $data['type'] == 'corner') {
                            if ($data['direction'] == 'Left'){
                                $ns = 'tm8.dfb2.cnrl.l';
                            }
                            else{
                                $ns = 'tm8.dfb2.cnrr.l';
                            }
                        }
                        elseif (isset($data['type']) && $data['type'] == 'brake') {
                            $ns = 'tm8.dfb2.dec.l';
                        }
                        elseif (isset($data['type']) && ($data['type'] == 'idle' || $data['type'] == 'stationary')) {
                            $ns = 'tm8.gps.idle.end';
                        }
                        elseif (isset($data['type']) && $data['type'] == 'overrev') {
                            $ns = 'tm8.dfb2.rpm';
                        }
                        else {
                            $ns = 'tm8.gps';
                        }

                    }
                    else {
                            $ns = 'tm8.gps';
                        }
                    $teletrak_journey_id = isset($data['tripId'])?$data['tripId']:0;
                    // if ($ns == 'tm8.gps.jny.start') {
                      //  $telematicsJourney = new TelematicsJourneysRepository();
                        //$value = $this->populateTelematicsJourneyArray($ns, $vehiclesMap);
                        //$telematicsJourney = $telematicsJourney->create($value);
                    //}
                    //elseif ($ns == 'tm8.gps.jny.end') {
                    //elseif ($ns == 'tm8.jny.sum') {
                    $telematicsJourney = null;
                    if ($ns == 'tm8.jny.sum') {
                        
                        $vehicleId = $vehicle->webfleet_object_id;
                        if($vehicleId != null && !empty($vehicleId)){
                            $this->fetchAndMatchJourneys($vehicle);
                        }
                        else{
                            \Log::info('object id not registered for vehicle : '.$vehicle->registration);
                        }
                        
                        $telematicsJourney = TelematicsJourneys::where('vrn',$vehicle->registration)->where('journey_id',$teletrak_journey_id)->first();
                        if($telematicsJourney) {
                            //if($telematicsJourney->fuel == 0 && $telematicsJourney->gps_idle_duration == 0 && $telematicsJourney->gps_distance == 0){
                            if($telematicsJourney->gps_distance == 0){
                                \Log::info("inside delete. Deleting telematicsJourney-->id:");
                                \Log::info($telematicsJourney->id);
                                $telematicsJourney->delete();
                                $del = TelematicsJourneyDetails::where('telematics_journey_id', $telematicsJourney->id)->delete();
                            }
                            else{                                
                                $telematicsService = new TelematicsService();
                                $telematicsService->processScore($telematicsJourney);
                                dispatch(new ProcessStreetSpeed($telematicsJourney->id));
                                $calculatedFields = $this->getCalculatedFieldsOfJourney($telematicsJourney->id);
                                $telematicsJourney->max_speed = $calculatedFields['maxspeed'];
                                $telematicsJourney->avg_speed = $calculatedFields['avgspeed'];
                                $telematicsJourney->incident_count = $calculatedFields['incidentCount'];
                                $telematicsJourney->harsh_breaking_count = $calculatedFields['harsh_breaking'];
                                $telematicsJourney->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
                                $telematicsJourney->harsh_cornering_count = $calculatedFields['harsh_cornering'];
                                $telematicsJourney->speeding_count = $calculatedFields['speeding'];
                                $telematicsJourney->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                                $telematicsJourney->rpm_count = $calculatedFields['rpm'];
                                $telematicsJourney->idling_count = $calculatedFields['idling'];
                                $telematicsJourney->save();
                            }


                        }
                    }
                    else{
                        $telematicsJourneyDetails = new TelematicsJourneyDetailsRepository();
                        $value = $this->populateTelematicsJourneyDetailsArray($ns, $data, $vehiclesMap);
                        // $value['telematicsJourneyId'] = $this->fetchJourneyId($value['vrn'],$value['journey_id'], $vehiclesMap);
                        $value['raw_json'] = $data;
                        $lastJourneyDetails = null;
                        if (isset($value['speed']) && $value['speed'] < 57) {
                            //data having speed greater than 130 mph is considered as junk so we skip that data 
                            $lastJourneyDetails = $telematicsJourneyDetails->create($value);
                        }
                        else if (!isset($value['speed'])) {
                            $lastJourneyDetails = $telematicsJourneyDetails->create($value);
                        }
                        //$lastJourneyDetails = $telematicsJourneyDetails->create($value);
                        
                        if($lastJourneyDetails && $lastJourneyDetails->id){
                            $teletrackJourneyDetailsMapping = new TeletrackJourneyDetailsMapping();
                            $teletrackJourneyDetailsMapping->vrn = $vrn;
                            $teletrackJourneyDetailsMapping->telematics_journey_details_id = $lastJourneyDetails->id;
                            //$teletrak_journey_id = (isset($data['result']) && isset($data['result']['tripId']))?$data['result']['tripId']:0;
                            //$teletrak_journey_id = isset($data['tripId'])?$data['tripId']:0;
                            $teletrackJourneyDetailsMapping->teletrack_journey_id = $teletrak_journey_id;
                            $teletrackJourneyDetailsMapping->save();
                        }
                    }
                    /*
                     following needs to be asked
                    tm8.dfb2.spdinc //calculated
                    tm8.fnol
                    tm8.gps.jny.end
                    */
                    // =====
                    //code to save to vehicles table and send pushnotifications --- start
                    $vehicle->telematics_ns = $ns;
                    if (isset($data['GPS'])) {
                        //$vehicle->telematics_ns = 'tm8.gps';
                        $lat = $data['GPS']['Lat'];
                        $lon = $data['GPS']['Lng'];
                        $vehicle->telematics_lat = $lat;
                        $vehicle->telematics_lon = $lon;
                        if (isset($data['result']) && isset($data['result']['location2'])) {
                            $vehicle->telematics_town = isset($data['result']['location2']['city'])?$data['result']['location2']['city']:$vehicle->telematics_town;
                            $vehicle->telematics_street = isset($data['result']['location2']['street'])?$data['result']['location2']['street']:$vehicle->telematics_street;
                            $vehicle->telematics_postcode = isset($data['result']['location2']['postcode'])?$data['result']['location2']['postcode']:$vehicle->telematics_postcode;
                            //{"result":{"location2":{"number":"7-3","country":"GB","city":"Argyll and Bute Council","street":"Ferry Road","postcode":"G84 0RR","suburb":"Rosneath","state":"Scotland"},"location":"7-3 Ferry Rd, Rosneath, Helensburgh G84 0RR, UK","id":486627192,"networks":""},"eventAt":"2022-09-12T06:49:31.000+01:00","GPS":{"valid":true,"Lng":-4.80002,"Spd":18,"Dir":320,"NSat":11,"HDOP":1.2,"Lat":56.01013},"device":{"companyId":325,"serialNumber":"5131118208","name":"CA_5131118208","externalId":"f810e972-a08a-4b51-b01c-c1006d54404a","imei":"CA_5131118208","company":{"features":[],"id":325},"model":{"id":320},"id":87817,"type":{"id":38},"version":5},"when":1662961771000,"ignition":"ON","vehicle":{"externalId":"461dca47-6cd7-4571-9c9a-0e85be93c11b","type":{"id":775},"version":5,"externalReference":"","companyId":325,"name":"BT71OFL","company":{"features":[],"id":325},"registration":"BT71OFL","vin":"","model":"","id":16538,"registrationState":"","make":"","status":"ENABLED"}}
                        }                        
                    }
                    if ($telematicsJourney != null) {
                        $vehicle->telematics_latest_journey_id = $telematicsJourney->id;
                        $vehicle->telematics_latest_journey_time = $telematicsJourney->start_time;
                        $vehicle->telematics_odometer = $telematicsJourney->odometer_end;
                    }
                    // if(isset($data['ignition'])){
                        if(isset($data['eventAt'])){
                            $vehicle->telematics_latest_location_time = Carbon::parse($data['eventAt'])->setTimezone('UTC');
                            //$vehicle->telematics_latest_location_time = $data['eventAt'];
                        }
                        if($ns == 'tm8.gps.jny.start'){
                        //if($data['ignition']=='ON'){
                            // $ns = 'tm8.gps.jny.start';
                            // $vehicle->telematics_ns = $ns;
                            if ($lat != '' && $lon != ''){
                                $payload = [
                                    'vehicle_id' => $vrn,
                                    'lat' => $lat,
                                    'lng' => $lon,
                                ];
                                event(new TelematicsJourneyStart($payload));
                            }
                        }
                        if ($ns = 'tm8.gps.jny.end' || $ns = 'tm8.jny.sum') {
                        //if($data['ignition']=='OFF'){
                            //$vehicle->telematics_ns = 'tm8.gps.jny.end';
                            $payload = [
                                'vehicle_id' => $vrn,
                            ];
                            event(new TelematicsJourneyEnd($payload));
                        }
                    // }
                                        
                    //if(isset($data['type'])){
                        if ($ns == 'tm8.gps.idle.end') {
                        //if ($data['type'] == 'stationary') {
                            //$vehicle->telematics_ns = 'tm8.gps.idle.start';
                            $payload = [
                                'vehicle_id' => $vrn,
                            ];
                            event(new TelematicsJourneyIdling($payload));
                        }
                        if ($ns == 'tm8.gps') {
                        //if ($data['type'] == 'moving') {
                            //$vehicle->telematics_ns = 'tm8.gps';
                            $payload = [
                                'vehicle_id' => $vrn,
                                'lat' => $lat,
                                'lng' => $lon,
                            ];
                            event(new TelematicsJourneyOngoing($payload));
                        }
                    //}
                    $vehicle->save();
                    //code to save to vehicles table and send pushnotifications --- end
                }
                else{
                    \Log::info("vehicle data not available in System :".$vrn);
                }
            }
            else{
                \Log::info("vehicle data not available in pushed json");
            }

        }

    }
}
