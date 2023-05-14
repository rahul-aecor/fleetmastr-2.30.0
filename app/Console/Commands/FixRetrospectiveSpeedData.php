<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;


class FixRetrospectiveSpeedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:FixRetrospectiveSpeedData {--startDate=null} {--endDate=null} {--journeyId=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $startDate = $this->option('startDate');
       $endDate = $this->option('endDate');
       $journeyId = $this->option('journeyId');
       $telematicsJourneysSql = TelematicsJourneys::with('user');
       if($startDate != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('start_time','>=',$startDate);
       }
       if($endDate != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('start_time','<=',$endDate);
       }
       if($journeyId != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('id','=',$journeyId);
       }
       //print_r($telematicsJourneysSql->toSql());
       //print_r($telematicsJourneysSql->getBindings());exit;
       $telematicsJourneys = $telematicsJourneysSql->get();
       // print_r($telematicsJourneys);exit;
       foreach($telematicsJourneys as $journey){
            $this->fixJourneyData($journey->id);
       }

      // $journeyDetails = TelematicsJourneyDetails::where('telematics_journey_id',$telematicsJourneyId)->get();
       //print_r($startDate);
       //print_r($endDate);
       //print_r($journeyId);exit();

    }


    private function fixJourneyData($telematicsJourneyId){
        
        $journeyDetails = TelematicsJourneyDetails::where('telematics_journey_id',$telematicsJourneyId)->get();
        //print_r($journeyDetails);exit;
        $local_street_speed_map = [];
        foreach($journeyDetails as $detail){
            if ( $detail->street_speed != 0 || $detail->street_speed != null ) {
                $local_street_speed_map[$detail->street] = $detail->street_speed;
            }
        }
        $hereApiCallPoints = [];
        foreach($journeyDetails as $detail){
            if ( $detail->street_speed == 0 || $detail->street_speed == null ) {
                if (array_key_exists($detail->street,$local_street_speed_map)) {
                    $detail->street_speed = $local_street_speed_map[$detail->street];
                    $detail->save();
                }
                else{
                    array_push($hereApiCallPoints,['lat'=>$detail->lat,'lon'=>$detail->lon]);
                }
                
            }
        }
        //print_r($hereApiCallPoints);exit;
        $hereDataArray = $this->getMultiplePointsHereData($hereApiCallPoints); // MPH to Meter Per Seconds
        //\Log::info(implode($hereDataArray,'|'));
        foreach($journeyDetails as $detail){
            if ( $detail->street_speed == 0 || $detail->street_speed == null ) {
                $preciseLatLon = $this->trimLatlon($detail->lat,$detail->lon);
                $hereKey = $preciseLatLon['preciselat'].','.$preciseLatLon['preciselon'];
                if (array_key_exists($hereKey,$hereDataArray)) {
                    //$detail->street_speed = $hereDataArray[$hereKey]['roadSpeed'];
                    // $detail->street_speed = number_format($hereDataArray[$hereKey]['roadSpeed'] * 0.277778, 2); // KPH to Meter Per Seconds;
                    $detail->street_speed = round($hereDataArray[$hereKey]['roadSpeed']); // Meter Per Seconds;
                    if ($detail->street == null || $detail->street == "") {
                        $detail->street = $hereDataArray[$hereKey]['streetName'];
                    }
                }
                $detail->save();
            }
        }

        /** 
         * Check speed and street speed and update ns
         */

        if(env('TELEMATICS_PROVIDER') != 'webfleet') {
            $journeyDetails = TelematicsJourneyDetails::where('telematics_journey_id', $telematicsJourneyId)
                                                    ->where('ns', 'tm8.gps')
                                                    ->whereRaw('speed > street_speed+(street_speed*0.2)')
                                                    ->where('street_speed', '>', 4.47)
                                                    ->get();

            $telematicsJourneyRepo = new TelematicsJourneysRepository();
            $updatedJourneyArr = [];


            $allJourneyDetails = TelematicsJourneyDetails::where('telematics_journey_id', $telematicsJourneyId)
                    ->where('ns', 'tm8.gps')
                    ->select('id','telematics_journey_id','ns','speed','street_speed','time')->get();

            foreach($journeyDetails as $journey) {
                 if(env('CHECK_SPEEDING_RULES')){
                    $isSpeeding = $this->checkSpeedingRules($allJourneyDetails,$journey);
                    if (!$isSpeeding) {
                        continue;
                    }
                }
                $journey->ns = 'tm8.dfb2.spdinc';
                $journey->save();
                if(!in_array($journey->telematics_journey_id, $updatedJourneyArr)) {
                    $updatedJourneyArr[] = $journey->telematics_journey_id;
                }
            }

            $telematicsJourneys = TelematicsJourneys::whereIn('id', $updatedJourneyArr)->get();
            foreach($telematicsJourneys as $journey) {
                $calculatedFields = $telematicsJourneyRepo->getCalculatedFieldsOfJourney($journey->id);
                $journey->incident_count = $calculatedFields['incidentCount'];
                $journey->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                $journey->save();
            }
        }

        if(env('ENABLE_HERE_API_LOGS')){
            \Log::info("processStreetSpeedJob end at :");
            \Log::info(Carbon::now());
        }
    }

    private function checkSpeedingRules($allJourneyDetails, $journeyDetails){
        $isSpeedingFlag = true;
        
        $sortedJourneyDetails = $allJourneyDetails->sortBy('time')->values();
        $position = 0;
        foreach($sortedJourneyDetails as $jd){
            if($jd->id == $journeyDetails->id) break;
            $position++;
        }
        $beforeJourneyDetails = $sortedJourneyDetails->splice($position-2,2)->all();
        $afterJourneyDetails = $sortedJourneyDetails->splice($position+1,2)->all();
        $beforeAndAfterJourneyDetails = array_merge($beforeJourneyDetails,$afterJourneyDetails);
        
        if (count($beforeAndAfterJourneyDetails) == 4) {
            if ($beforeAndAfterJourneyDetails[0]->street_speed == $beforeAndAfterJourneyDetails[1]->street_speed && $beforeAndAfterJourneyDetails[1]->street_speed == $beforeAndAfterJourneyDetails[2]->street_speed && $beforeAndAfterJourneyDetails[2]->street_speed == $beforeAndAfterJourneyDetails[3]->street_speed) {
                if($beforeAndAfterJourneyDetails[0]->street_speed > $journeyDetails->street_speed){
                    //skip tagging this as speeding incident
                    $isSpeedingFlag = false;
                }
            }
        }
        return $isSpeedingFlag;
    }

    private function trimLatlon($lat,$lon){
        $lat1 = $lat;
        $lon1 = $lon;
        $decimalplaceslat = strlen(substr(strrchr($lat1, "."), 1));
        $decimalplaceslon = strlen(substr(strrchr($lon1, "."), 1));
        $precisionlat = 6;
        $precisionlon = 6;
        /*if ($decimalplaceslat < 6) {
            $precisionlat = $decimalplaceslat;
        }if ($decimalplaceslon < 6) {
            $precisionlon = $decimalplaceslon;
        }*/
        $preciseLat = number_format($lat1,$precisionlat);
        $preciseLon = number_format($lon1,$precisionlon);
        return ['preciselat'=>$preciseLat,'preciselon'=>$preciseLon];
    }

    //private function getMultiplePointsSpeedLimit($lat,$lon){
    private function getMultiplePointsHereData($hereApiCallPoints){
        //\Log::info('getSpeedLimit ' . $lat . ',' . $lon);
        $param = "";
        $hereDataMap = [];
        foreach(array_chunk($hereApiCallPoints, 150) as $chunk ) { 
            $param = "";
            foreach($chunk as $key=>$point) { 
                //print_r($chunk);
                if ($key == 0) {
                    $param .= '&origin='.$point['lat'].','.$point['lon'];
                }
                //if ($key != 0 && $key != count($chunk)-1) {
                if ($key != 0) {
                    $param .= '&via='.$point['lat'].','.$point['lon'];

                }
                if ($key == count($chunk)-1) {
                    $param .= '&destination='.$point['lat'].','.$point['lon'];
                }
            }
            //$hereParams = $this->constructHereParams($hereApiCallPoints);
            $url = 'https://router.hereapi.com/v8/routes?transportMode=car'.$param.'&apiKey='.env('HERE_API_KEY').'&spans=maxSpeed,names&return=polyline';
            if(env('ENABLE_HERE_API_LOGS')){
                \Log::info($url);
            }
            //print_r($url);
            $resp = file_get_contents($url);
            $data = json_decode($resp, true);
            //print_r($data);exit;
            $sections = $data['routes'][0]['sections'];
            foreach($sections as $section){
                //print_r($section);exit;
                if(isset($section['spans']) && isset($section['spans'][0]) && isset($section['spans'][0]['maxSpeed'])){
                    $originalLocation = $section['departure']['place']['originalLocation'];
                    $lat1 = $originalLocation['lat'];
                    $lon1 = $originalLocation['lng'];
                    $decimalplaceslat = strlen(substr(strrchr($lat1, "."), 1));
                    $decimalplaceslon = strlen(substr(strrchr($lon1, "."), 1));
                    $precisionlat = 6;
                    $precisionlon = 6;
                    if ($decimalplaceslat < 6) {
                        $precisionlat = $decimalplaceslat;
                    }if ($decimalplaceslon < 6) {
                        $precisionlon = $decimalplaceslon;
                    }
                    $preciseLat = number_format($lat1,$precisionlat);
                    $preciseLon = number_format($lon1,$precisionlon);
                    $roadSpeed = isset($section['spans'][0]['maxSpeed'])?$section['spans'][0]['maxSpeed']:0;
                    $streetName = isset($section['spans'][0]['names']) && isset($section['spans'][0]['names'][0]['value'])?$section['spans'][0]['names'][0]['value']:"";
                    $hereDataMap[$preciseLat.','.$preciseLon] = ['roadSpeed'=>$roadSpeed, 'streetName'=>$streetName];
                    if(env('ENABLE_HERE_API_LOGS')){
                        \Log::info('hereDataMap['.$preciseLat.','.$preciseLon.']=[roadspeed=>'.$roadSpeed.',streetName=>'.$streetName.']');
                    }
                }
            }
            sleep(1);
        }
        return $hereDataMap;
    }
    
}
