<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Check;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Services\TelematicsService;
use App\Models\UserTelematicsJourney;
use App\Events\TelematicsJourneyOngoing;
use App\Events\TelematicsJourneyIdling;
use App\Events\TelematicsJourneyStart;
use App\Events\TelematicsJourneyEnd;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TelematicsController extends APIController
{
    public function telematicsJourneyBindUser(Request $request)
    {
        try {
            $registration = $request->vehicle_id;
            $journey_id = $request->journey_id;
            $driver_tag_key = $request->driver_id;
            $user = User::where('driver_tag_key',$driver_tag_key)->first();
            $userTelematicsJourney = UserTelematicsJourney::where(['journey_id'=>$journey_id,'registration'=>$registration])->first();
            $userTelematicsJourney->user_id = $user->id;
            $userTelematicsJourney->save();

        } catch (\Exception $e) {
            \Log::error("ERROR :: Can not bind dallas key user in record at users_telematics_journey :: Error Message".$e->getMessage());
        }


    }
    public function telematicsJourneyStart(Request $request)
    {
        $vehicle = Vehicle::with('lastCheck')->where(['registration'=>$request->vehicle_id])->first();
        // \Log::info($vehicle->last_check);

        \Log::info($request->all());
        //array('vehicle_id'=>$vrn, 'journey_id'=>$journey_id, 'start_lat'=>$lat, 'start_lon' => $lon, 'start_time' => $time, 'start_street'=>$street, 'start_town'=>$town, 'start_post_code' => $post_code);
	    //$journey_user_id = $vehicle->lastCheck != null? $vehicle->lastCheck [0]->created_by : 1;
        try {
            //$journey_user_id = $vehicle->lastCheck[0]->created_by;
            $journey_user_id = 1;//consider 1 as System Id, actual user will be bound with user_id when telematicsJourneyBindUser is called
            $userTelematicsJourney = new UserTelematicsJourney();
            $userTelematicsJourney->user_id = $journey_user_id;
            $userTelematicsJourney->vehicle_id = $vehicle->id;
            $userTelematicsJourney->registration = $request->vehicle_id;
            $userTelematicsJourney->journey_id = $request->journey_id;
            $userTelematicsJourney->start_lat = $request->start_lat;
            $userTelematicsJourney->start_lon = $request->start_lon;
            $userTelematicsJourney->start_time = $request->start_time;
            $userTelematicsJourney->start_street = $request->start_street;
            $userTelematicsJourney->start_town = $request->start_town;
            $userTelematicsJourney->start_post_code = $request->start_post_code;
            $userTelematicsJourney->save();

            $payload = [
                'vehicle_id' => $request->vehicle_id,
                'lat' => $request->start_lat,
                'lng' => $request->start_lon,
            ];
            event(new TelematicsJourneyStart($payload));

        } catch (\Exception $e) {
            \Log::error("ERROR :: Can not add record in users_telematics_journey :: Error Message".$e->getMessage());
        }

    }
    public function telematicsJourneyEnd(Request $request)
    {
        $registration = $request->vehicle_id;
        $userTelematicsJourney = UserTelematicsJourney::where(['journey_id'=>$request->journey_id,'registration'=>$registration])->first();
        $userTelematicsJourney->end_lat = $request->end_lat; 
        $userTelematicsJourney->end_lon = $request->end_lon; 
        $userTelematicsJourney->end_time = $request->end_time; 
        $userTelematicsJourney->end_street = $request->end_street; 
        $userTelematicsJourney->end_town = $request->end_town; 
        $userTelematicsJourney->end_post_code = $request->end_post_code; 
        $userTelematicsJourney->save();

        $payload = [
            'vehicle_id' => $request->vehicle_id,
        ];
        \Log::info('event call');
        event(new TelematicsJourneyEnd($payload));
    }
    public function telematicsJourneyIdling(Request $request)
    {
        $payload = [
            'vehicle_id' => $request->vehicle_id,
        ];
        event(new TelematicsJourneyIdling($payload));
    }
    public function telematicsJourneyOngoing(Request $request)
    {

        //\Log::info($request->all());
        $payload = [
            'vehicle_id' => $request->vehicle_id,
            'lat' => $request->latitude,
            'lng' => $request->longitude,
        ];
        \Log::info($payload);
        \Log::info('Telematics Journey ongoing event called');
        event(new TelematicsJourneyOngoing($payload));
    }

/*    public function getLatestLocation(Request $request)
    {
        //print_r($vehicleId);exit;
        //Log::info($vehicleId);
        $payload = [
            'vehicle_id' => $vehicleId,
        ];
        //event(new VehicleReturn($payload));
    }
*/
    //Called from app
    public function fetchMyVehicleTrakm8Data(Request $request){
        $reg = $request->registration;
        $logged_in_user_id = $request->user_id;
        if ($reg == null || $logged_in_user_id == null) {
            return response()->json(['failure_message' => "Ensure parameter are passed correctly.", "status"=>"failure"], 200);
        }
        $vehicle = Vehicle::with('lastCheck','type')->where('registration',$reg)->first();
        if ($vehicle == null) {
            return response()->json(['failure_message' => "No such vehicle registred with the system", "status"=>"failure"], 200);
        }
       // print_r($vehicle);exit;
        /*$prevVehicleJourney = UserTelematicsJourney::where(['vehicle_id'=>$vehicle->id])
                        ->orderBy('start_time','DESC')->first();
	    $latestUserVehicleJourney = UserTelematicsJourney::where(['vehicle_id'=>$vehicle->id,'user_id'=>$logged_in_user_id])
                        ->orderBy('start_time','DESC')->first();*/
        $prevVehicleJourney = TelematicsJourneys::where(['vehicle_id'=>$vehicle->id])
                        ->orderBy('start_time','DESC')->first();


        // $latestUserVehicleJourney = TelematicsJourneyDetails::with('TelematicsJourneys')->whereHas('TelematicsJourneys', function($q) use($vehicle,$logged_in_user_id){
        //                                 $q->where(['vehicle_id'=>$vehicle->id,'user_id'=>$logged_in_user_id]);
        //                             })
        //                 ->orderBy('time','DESC')->first();

        $latestUserVehicleJourney = TelematicsJourneyDetails::join('telematics_journeys', 'telematics_journey_id', '=', 'telematics_journeys.id')->where(['vehicle_id'=>$vehicle->id,'user_id'=>$logged_in_user_id])
                        ->orderBy('time','DESC')->first(['telematics_journey_details.*']);

        
    	if ($vehicle->is_telematics_enabled == 0) {
                return response()->json(['failure_message' => "Telematics is not available for this vehicle.", "status"=>"failure"], 200);
            }

        /*$baseData = ['registration'=>$reg];
        if ($prevVehicleJourney != null && $prevVehicleJourney->user_id != $logged_in_user_id && $latestUserVehicleJourney != null) {
            //this condition is to disable showing last journey if that is not done by same user and fetch journey details based on that users last journey on that vehicle.
            //$trackm8_data->lastJourney = null;
		$baseData = ['registration'=>$reg,'journey_id'=>$latestUserVehicleJourney->journey_id];

        }
        $trackm8_data = $this->callTrakm8ServerAPI($baseData, 'FetchMyVehicleData');*/
        //$journeySummarySql = TelematicsJourneys::where('vehicle_id',$vehicle->id)->orderBy('end_time','DESC')->first();//where vrn = '".$registration."' ORDER BY end_time DESC limit 1";
        $trackm8_data = [];
        $trackm8_data['latest_lat'] = $vehicle->telematics_lat;
        $trackm8_data['latest_lon'] = $vehicle->telematics_lon;
        $trackm8_data['latest_street'] = $vehicle->telematics_street;
        $trackm8_data['latest_town'] = $vehicle->telematics_town;
        $trackm8_data['latest_post_code'] = $vehicle->telematics_postcode;
        $trackm8_data['make'] = $vehicle->type->vehicle_type;
        $trackm8_data['model'] = $vehicle->type->model;
        $trackm8_data['manufacturer'] = $vehicle->type->manufacturer;
        $trackm8_data['odometer'] = $vehicle->telematics_odometer;
        $trackm8_data['latest_time'] = Carbon::parse($vehicle->end_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        $trackm8_data['fuel_type'] = $vehicle->type->fuel_type;
        $summary_json = json_decode($prevVehicleJourney->raw_json,true);
        // $end_fuel_pc = $summary_json != null ? $summary_json['end_fuel_pc'] : 'N/A';
        $end_fuel_pc = $prevVehicleJourney->fuel != null ? $prevVehicleJourney->fuel : 'N/A';
        $trackm8_data['fuel_remaining'] = $end_fuel_pc;

        $latitudes = null;
        $longitudes = null;
        if(isset($latestUserVehicleJourney)) {
            $latestUserVehicleJourneyList = TelematicsJourneyDetails::where('telematics_journey_id',$latestUserVehicleJourney->telematics_journey_id)->get();

            if(isset($latestUserVehicleJourneyList)) {
                $latitudes = $latestUserVehicleJourneyList->pluck('lat');
                $longitudes = $latestUserVehicleJourneyList->pluck('lon');
            }
        }
        //$longitudes = [];
        /*while ($r = mysqli_fetch_array($resJournies,MYSQLI_ASSOC)) { 
            //print_r($r);exit;
            array_push($latitudes, $r['lat']);
            array_push($longitudes, $r['lon']);
        }*/

        $sDate2 = new \DateTime();
        $sDate1 = new \DateTime($prevVehicleJourney['end_time']);
        $sDate2->diff($sDate1);
        $dateIntervalObj = $sDate2->diff($sDate1);
        $minutes = $dateIntervalObj->days * 24 * 60;
        $minutes += $dateIntervalObj->h * 60;
        $minutes += $dateIntervalObj->i;
        $trackm8_data['latestUpdateMins'] = $minutes;

        //print_r($trackm8_data);exit;

        //$trackm8_data = json_encode($trackm8_data);
        //$journeyDetailSql = TelematicsJourneyDetails;
	    if ($latestUserVehicleJourney == null) {
            //this condition is to disable showing last journey if that is not done by same user.
            //$trackm8_data->lastJourney = null;
            $trackm8_data['lastJourney'] = null;
        }

        //if ( $trackm8_data->latest_lat == null && $trackm8_data->latest_lon == null ) {
        if ( $trackm8_data['latest_lat'] == null && $trackm8_data['latest_lon'] == null ) {
            return response()->json(['failure_message' => "Telematics device data not available for this vehicle.", "status"=>"failure"], 200);
        }
        if($vehicle->odometer_reading_unit == 'km'){
            //round($trackm8_data->odometer/1000, 0, PHP_ROUND_HALF_UP);
            round($trackm8_data['odometer']/1000, 0, PHP_ROUND_HALF_UP);
            $trackm8_data['odometer_unit'] = 'km';
            $trackm8_data['converted_odometer'] = round($trackm8_data['odometer']/1000, 0, PHP_ROUND_HALF_UP);
            /*round($trackm8_data->odometer/1000, 0, PHP_ROUND_HALF_UP);
            $trackm8_data->odometer_unit = 'km';
            $trackm8_data->converted_odometer = round($trackm8_data->odometer/1000, 0, PHP_ROUND_HALF_UP);;*/
        }
        elseif($vehicle->odometer_reading_unit == 'miles'){
            $trackm8_data['odometer_unit'] = 'miles';
            $trackm8_data['converted_odometer'] = round($trackm8_data['odometer']/1609.344, 0, PHP_ROUND_HALF_UP);
            //$trackm8_data['converted_odometer'] = round($trackm8_data->odometer/1609.344, 0, PHP_ROUND_HALF_UP);
            /*$trackm8_data->odometer_unit = 'miles';
            $trackm8_data->converted_odometer = round($trackm8_data->odometer/1609.344, 0, PHP_ROUND_HALF_UP);;*/
        }
        else{
            $trackm8_data['odometer_unit'] = 'metres';
            //$trackm8_data->odometer_unit = 'metres';
            // Note : no need of converted_odometer here, as default $trakm8->odometer value is in metres.
        }
        /*
        if ($trackm8_data->latest_time != null) {
            $trackm8_data->latest_time = Carbon::parse($trackm8_data->latest_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        }*/
        //print_r($latestUserVehicleJourney);exit;
        $lastJourney = null;
        if(isset($latestUserVehicleJourney)) {
            $lastJourney = ['start_lat'=>$latestUserVehicleJourney->telematicsJourneys->start_lat, 'start_lon'=>$latestUserVehicleJourney->telematicsJourneys->start_lon, 'start_time'=>$latestUserVehicleJourney->telematicsJourneys->start_time, 'end_lat'=>$latestUserVehicleJourney->telematicsJourneys->end_lat, 'end_lon'=>$latestUserVehicleJourney->telematicsJourneys->end_lon, 'end_time'=>$latestUserVehicleJourney->telematicsJourneys->end_time, 'end_street'=>$latestUserVehicleJourney->telematicsJourneys->end_street, 'end_town'=>$latestUserVehicleJourney->telematicsJourneys->end_town, 'end_post_code'=>$latestUserVehicleJourney->telematicsJourneys->end_post_code, 'start_street'=>$latestUserVehicleJourney->street, 'start_town'=>$latestUserVehicleJourney->town, 'start_post_code'=>$latestUserVehicleJourney->post_code, 'latitudes'=>$latitudes, 'longitudes'=>$longitudes];
        }

        $trackm8_data['lastJourney'] = $lastJourney;
	    if ($trackm8_data['lastJourney'] != null && $trackm8_data['lastJourney']['start_time'] != null) {
            $trackm8_data['lastJourney']['start_time'] = Carbon::parse($trackm8_data['lastJourney']['start_time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        }
        if ($trackm8_data['lastJourney'] != null && $trackm8_data['lastJourney']['end_time'] != null) {
            $trackm8_data['lastJourney']['end_time'] = Carbon::parse($trackm8_data['lastJourney']['end_time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        }
        /*if ($trackm8_data->lastJourney != null && $trackm8_data->lastJourney->start_time != null) {
            $trackm8_data->lastJourney->start_time = Carbon::parse($trackm8_data->lastJourney->start_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        }
        if ($trackm8_data->lastJourney != null && $trackm8_data->lastJourney->end_time != null) {
            $trackm8_data->lastJourney->end_time = Carbon::parse($trackm8_data->lastJourney->end_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        }*/
        //$data['trackm8_data'] = $trackm8_data;
        $responseArray = ['data'=>$trackm8_data, "status"=>"success"];

        return response()->json($responseArray, 200);
    }
    //Called from app
    public function fetchUserJourneyTrakm8Data_old(Request $request){
        $user_id = $request->user_id;
        $pageCount = 15;
        $page_number = $request->page_number;

        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            return $this->response->error('No such user registred with the system', 404);
        }

        $baseQuery = TelematicsJourneys::with('vehicle')
                        ->where(['user_id'=>$user_id])
                        ->whereNotNull('end_time')
                        ->orderBy('end_time','DESC');

        $totalCount = count($baseQuery->get());
        $totalPages = ceil($totalCount/$pageCount);
        if($page_number > $totalPages){
            $page_number = $totalPages;
        }
        if ($totalCount  == 0) {
            return response()->json(['failure_message' => "You have no journeys.", "status"=>"failure"], 200);
        }

        $user_journeys = $baseQuery->skip((($page_number-1)*$pageCount))
                                ->take($pageCount)->get();
        $data = [];
	    $last_journeys = [];
        
        foreach ($user_journeys as $user_journey) {
	    $start_time = Carbon::parse($user_journey->start_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
            $end_time = Carbon::parse($user_journey->end_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
            $latestJourney = ['start_lat'=>$user_journey->start_lat, 'start_lon'=>$user_journey->start_lon, 'start_time'=>$start_time, 'end_lat'=>$user_journey->end_lat, 'end_lon'=>$user_journey->end_lon, 'end_time'=>$end_time, 'end_street'=>$user_journey->end_street, 'end_town'=>$user_journey->end_town, 'end_post_code'=>$user_journey->end_post_code, 'start_street'=>$user_journey->start_street, 'start_town'=>$user_journey->start_town, 'start_post_code'=>$user_journey->start_post_code, 'journey_id'=>$user_journey->journey_id, 'vrn' => $user_journey->vehicle->registration];
        //  print_r($latestJourney);
	    array_push($last_journeys, $latestJourney);
        }
	    $data['last_journeys'] = $last_journeys;
        $data['totalPages'] = $totalPages;


        $responseArray = ['data'=>$data, "status"=>"success"];

        return response()->json($responseArray, 200);
       
    }
    //called from app
    public function fetchJourneyDetailsTrakm8Data(Request $request){
        $data = [];
        $journey_id = $request->journey_id;
        $registration = $request->registration;

        /*$baseData = ['journey_id'=>$journey_id, 'registration'=>$registration];
        $data = $this->callTrakm8ServerAPI($baseData, 'FetchJourneyDetails');*/
        $summaryData = TelematicsJourneys::where('journey_id',$journey_id)->where('vrn',$registration)->first();
        $journeyRawBaseSql = TelematicsJourneyDetails::where('telematics_journey_id',$summaryData->id)->where('vrn',$registration);
        $journeyRawBaseSqlResp = $journeyRawBaseSql->get();
        $latitudes = $journeyRawBaseSqlResp->pluck('lat');
        $longitudes = $journeyRawBaseSqlResp->pluck('lon');
        $baseData['latitudes'] = $latitudes;
        $baseData['longitudes'] = $longitudes;
        $journeyRawBaseSqlLatestResp = $journeyRawBaseSql->orderBy('time','DESC')->first();
        //print_r($latitudes);exit;
        $baseData['odometer'] = $journeyRawBaseSqlLatestResp->odometer;
        $baseData['journey_distance'] = $summaryData->gps_distance;
        $baseData['max_acceleration'] = $journeyRawBaseSqlResp->max('speed');


        $vehicle = Vehicle::where('registration',$registration)->first();
        if($vehicle->odometer_reading_unit == 'km'){
            round($baseData->odometer/1000, 0, PHP_ROUND_HALF_UP);
            $baseData['odometer_unit'] = 'km';
            $baseData['converted_odometer'] = round($baseData['odometer']/1000, 0, PHP_ROUND_HALF_UP);;
        }
        elseif($vehicle->odometer_reading_unit == 'miles'){
            $baseData['odometer_unit'] = 'miles';
            $baseData['converted_odometer'] = round($baseData['odometer']/1609.34, 0, PHP_ROUND_HALF_UP);;
        }
        else{
            $baseData['odometer_unit'] = 'metres';
            // Note : no need of converted_odometer here, as default $trakm8->odometer value is in metres.
        }
        $data[0] = $baseData;
       /* $data[0] = $baseData;


        $vehicle = Vehicle::where('registration',$registration)->first();
        if($vehicle->odometer_reading_unit == 'km'){
            round($data[0]->odometer/1000, 0, PHP_ROUND_HALF_UP);
            $data[0]->odometer_unit = 'km';
            $data[0]->converted_odometer = round($data[0]->odometer/1000, 0, PHP_ROUND_HALF_UP);;
        }
        elseif($vehicle->odometer_reading_unit == 'miles'){
            $data[0]->odometer_unit = 'miles';
            $data[0]->converted_odometer = round($data[0]->odometer/1609.34, 0, PHP_ROUND_HALF_UP);;
        }
        else{
            $data[0]->odometer_unit = 'metres';
            // Note : no need of converted_odometer here, as default $trakm8->odometer value is in metres.
        }*/
        

        $responseArray = ['data'=>$data, "status"=>"success"];

        return response()->json($responseArray, 200);
    }


/*    public function fetchScores(Request $request){
        $journeyIds = $request->journeyIds;
        $baseData = ['journeyIds'=>$journeyIds];
        return $this->callTrakm8ServerAPI($baseData, 'FetchScores');
    }


    public function fetchEfficiencyScoreData(Request $request){
        $journeyIds = $request->journeyIds;
        $baseData = ['journeyIds'=>$journeyIds];
        return $this->callTrakm8ServerAPI($baseData, 'FetchEfficiencyScoreData');
    }
*/
    /*
    public function fetchBehaviourData(Request $request){
        $journeyIds = $request->journeyIds;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $baseData = ['journeyIds'=>$journeyIds, 'start_date'=>$start_date, 'end_date'=>$end_date];
        return $this->callTrakm8ServerAPI($baseData, 'FetchBehaviourData');
    }

    public function fetchIncidentsData(Request $request){
        $registrations = $request->registrations;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $journeyIds = $request->journeyIds;
        $incidentTypeFilterValue = $request->incidentTypeFilterValue;
        $baseData = ['registrations'=>$registrations, 'start_date'=>$start_date, 'end_date'=>$end_date,'journeyIds' => $journeyIds,'incidentTypeFilterValue' => $incidentTypeFilterValue];
        return $this->callTrakm8ServerAPI($baseData, 'FetchIncidentsData');
    }

    public function fetchJourneyData(Request $request){
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $journeyIds = $request->journeyIds;
        $baseData = ['start_date'=>$start_date, 'end_date'=>$end_date,'journeyIds' => $journeyIds];
        return $this->callTrakm8ServerAPI($baseData, 'FetchJourneyData');
    }

    public function fetchJourneyDetails(Request $request){
        $journeyId = $request->journeyId;
        $baseData = ['journeyId' => $journeyId];
        return $this->callTrakm8ServerAPI($baseData, 'FetchJourneyTrakRawData');
    }*/

    /*private function callTrakm8ServerAPI($baseData, $api_name){
        //following URL will be URL of Trakm8 data server
        //print_r(json_encode($baseData));exit;
        ini_set('max_execution_time', '0');
        $url = env('TELEMATICS_SERVER_URL').$api_name.'.php';
        $username = 'trackm8';
        $password = 'trackm8';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($baseData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HEADER, 0);//0 = false
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output);
       
    }*/

    //////****Desktop related API********/////

/*    public function getMarkerDetails(Request $request)
    {
        $registration=['registration'=>$request->registration];
        $registration['time']=Carbon::today()->format('Y-m-d');
        if(isset($request->ns))
        {
            $registration['ns']=$request->ns;
        }
        return response()->json($this->callTrakm8ServerAPI($registration, 'getMarkerDetails'));
    }
    public function getActiveVehiclesOnFleet(Request $request)
    {
        return response()->json($this->callTrakm8ServerAPI([], 'getActiveVehiclesOnFleet'));
    }
    public function getPopulateVehiclesOnFleetArray(Request $request)
    {
        $registration=['registrations'=>$request->registrations];
        return response()->json($this->callTrakm8ServerAPI($registration, 'getPopulateVehiclesOnFleetArray'));
    }
    public function getTelematicsData(Request $request)
    {
        $vehicleIds = $request->vehicleIds;
        $data = ['vehicleIds' => $vehicleIds];
        return response()->json($this->callTrakm8ServerAPI($data, 'getTelematicsData'));
    }
*/
    //////******************************/////

    //////////***********new telematics  API start****************//////
    //remember to call events from new datapusher when journey start end,idle etc
    public function dataPusher(Request $request)
    {
        $dataFromTrakm8Array = $request->all();

        if(env('TO_AUTHENTICATE_TRAKM8') === true) {
            // check username & password for trakm8
            $trakm8Username = isset($request->username)?$request->username:null;
            $trakm8Password = isset($request->password)?$request->password:null;

            //get username & password from env
            if(env('TRAKM8_USERNAME') != $trakm8Username || env('TRAKM8_PASSWORD') != $trakm8Password) {
                return response()->json(abort(401));
            }
        }
        $dataForwardingFlag = env('DATA_FORWARDING_FLAG');
        
        if ($dataForwardingFlag == 1) {
            $username = 'trackm8';
            $password = 'trackm8';
            $dataForwardingURL = env('DATA_FORWARDING_URLS');
            $dataForwardingURLArray = explode(',',$dataForwardingURL);
            $baseData = json_encode($dataFromTrakm8Array);
            foreach($dataForwardingURLArray as $url){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url.'/api/v1/telematics/dataPush');
                //$url = 'https://qa2-api.fleetmastr.com/api/v1/telematics/dataPush';
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $baseData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $output = curl_exec($ch);
                $info = curl_getinfo($ch);
                // print_r($info);
                curl_close($ch);
            }
        }
                
        $file = "telematics/trackm8rawdata-".Carbon::today()->format("d-m-Y").".log";
        $log = new Logger('telematicsData');
        $log->pushHandler(new StreamHandler('../storage/logs/'.$file, Logger::INFO));
        $log->info(json_encode($dataFromTrakm8Array));

        $telematicsService = new TelematicsService();
        $telematicsService->dataPush($dataFromTrakm8Array);
         
    }

    ///called from app
    public function fetchUserJourneyTrakm8Data(Request $request){
        $user_id = $request->user_id;
        $pageCount = 15;
        $page_number = $request->page_number;

        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            return $this->response->error('No such user registred with the system', 404);
        }

        $baseQuery = TelematicsJourneys::with('vehicle')
                        ->where(['user_id'=>$user_id])
                        ->whereNotNull('end_time')
                        ->orderBy('end_time','DESC');

        $totalCount = count($baseQuery->get());
        $totalPages = ceil($totalCount/$pageCount);
        if($page_number > $totalPages){
            $page_number = $totalPages;
        }
        if ($totalCount  == 0) {
            return response()->json(['failure_message' => "You have no journeys.", "status"=>"failure"], 200);
        }

        $user_journeys = $baseQuery->skip((($page_number-1)*$pageCount))
                                ->take($pageCount)->get();
        $data = [];
        $last_journeys = [];
        
        foreach ($user_journeys as $user_journey) {
            $start_time = Carbon::parse($user_journey->start_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
            $end_time = Carbon::parse($user_journey->end_time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
            $latestJourney = ['start_lat'=>$user_journey->start_lat, 'start_lon'=>$user_journey->start_lon, 'start_time'=>$start_time, 'end_lat'=>$user_journey->end_lat, 'end_lon'=>$user_journey->end_lon, 'end_time'=>$end_time, 'end_street'=>$user_journey->end_street, 'end_town'=>$user_journey->end_town, 'end_post_code'=>$user_journey->end_post_code, 'start_street'=>$user_journey->start_street, 'start_town'=>$user_journey->start_town, 'start_post_code'=>$user_journey->start_post_code, 'journey_id'=>$user_journey->journey_id, 'vrn' => $user_journey->vehicle->registration];
            //  print_r($latestJourney);
            array_push($last_journeys, $latestJourney);
        }
        $data['last_journeys'] = $last_journeys;
        $data['totalPages'] = $totalPages;


        $responseArray = ['data'=>$data, "status"=>"success"];

        return response()->json($responseArray, 200);
       
    }

    //////////***********new telematics  API end****************//////    
}
