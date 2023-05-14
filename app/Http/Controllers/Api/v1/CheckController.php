<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Log;
use Storage;
use DB;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\DefectHistory;
use App\Models\DefectMaster;
use App\Models\PreexistingDefectAcknowledgement;
use App\Models\UserLogoutState;
use App\Events\PushNotification;
use App\Events\TelematicsJourneyStart;
use App\Events\TelematicsJourneyEnd;
use App\Transformers\CheckDetailsTransformer;
use App\Traits\ProcessCheckJson;
use Illuminate\Support\Facades\Config;


class CheckController extends APIController
{
    use ProcessCheckJson;

    private $user_id;
    private $vehicle_id;
    private $check_id;

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id, Request $request)
    public function show(Request $request)
    {
        $id = $request->get('id');
        if(empty($id)){
            return $this->response->errorBadRequest("Bad Request. Requied check ID is missing.");
        }
        $check = Check::find($id);
        \Log::info("**************");
        \Log::info($check);
        if(!empty($check)){
            if(trim($check->json) != "") {
                $checkJson = json_decode($check->json);
                $preexistingDefectIds = PreexistingDefectAcknowledgement::where('check_id',$id)->where('status','1')->select('defect_id')->get()->toArray();
                $preExistingDefectObjects = Defect::with('updater')->whereIn('id',$preexistingDefectIds)->get();
                //$preexistingDefects = $preExistingDefectObjects->toArray();
                $preexistingDefectArrayIds = [];
                $preexistingDefectMasterIds = [];
                $preexistingDefectMasterId_DefectId_map = [];
                $newly_added_defects = Defect::with('updater')->where('check_id',$id)->get();
                foreach ($preExistingDefectObjects as $defectObj) {
                    $defect = $defectObj->toArray();
                    array_push($preexistingDefectMasterIds,$defect['defect_master_id'] );
                    array_push($preexistingDefectArrayIds,$defect['id']);
                    $preexistingDefectMasterId_DefectId_map[$defect['defect_master_id']] = $defect['id'];
                    $preexisting_DefectId_status_map[$defect['id']] = $defect['status'];
                    $preexisting_DefectId_reportdatetime_map[$defect['id']] = $defect['report_datetime'];
                    $preexisting_DefectId_updatedby_map[$defect['id']] = $defect['updater']['first_name'].' '.$defect['updater']['last_name'];
                    $preexisting_DefectId_updatedat_map[$defect['id']] = $defect['updated_at'] != null ? (Carbon::createFromFormat('Y-m-d H:i:s', $defect['updated_at'])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y')) : "";

                    $media = $defectObj->getMedia();
                    $mediaUrl = "";
                    
                    if($media->count() > 0) {
                        foreach ($media as $singleMedia) {
                            $mediaUrl = ($mediaUrl == '') ? $singleMedia->getUrl() : $mediaUrl . '|' . $singleMedia->getUrl();
                        }
                    }
                    $preexisting_DefectId_imagestring_map[$defectObj->id] = $mediaUrl;
                }
                foreach ($newly_added_defects as $newdefect) {
                    $new_DefectId_status_map[$newdefect['id']] = $newdefect['status'];
                    $new_DefectId_reportdatetime_map[$newdefect['id']] = $newdefect['report_datetime'];
                    $new_DefectId_updatedby_map[$newdefect['id']] = $newdefect['updater']['first_name'].' '.$newdefect['updater']['last_name'];
                    $new_DefectId_updatedat_map[$newdefect['id']] = $newdefect['updated_at'] != null ? Carbon::createFromFormat('Y-m-d H:i:s', $newdefect['updated_at'])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y') : "";
;
                }
                if($check->type == "Return Check"){
                    foreach ($checkJson->screens->screen as $screen) {
                        if(isset($screen->options->optionList)){
                            foreach ($screen->options->optionList as $option) {
                                if(isset($option->defects) && isset($option->defects->defect)){
                                    $defectsArray = $option->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->defect_status = isset($preexisting_DefectId_status_map[$defect->defect_id]) ? $preexisting_DefectId_status_map[$defect->defect_id] : "";
                                            $defect->report_datetime = isset($preexisting_DefectId_reportdatetime_map[$defect->defect_id]) ? Carbon::createFromFormat('Y-m-d H:i:s', $preexisting_DefectId_reportdatetime_map[$defect->defect_id])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y') : "";
                                            $defect->last_updated_by = isset($preexisting_DefectId_updatedby_map[$defect->defect_id]) ? $preexisting_DefectId_updatedby_map[$defect->defect_id] : "";
                                            $defect->last_updated_at = $preexisting_DefectId_updatedat_map[$defect->defect_id];
                                            $defect->imageString = $preexisting_DefectId_imagestring_map[$defect->defect_id];
                                        }
                                        if ($defect->selected === 'yes' && $defect->is_pre_existing_defect == 0 && isset($defect->defect_id) && $defect->defect_id != null) {
                                            $defect->defect_status =  isset($new_DefectId_status_map[$defect->defect_id]) ? $new_DefectId_status_map[$defect->defect_id] : "";
                                            $defect->report_datetime = isset($new_DefectId_reportdatetime_map[$defect->defect_id]) ? Carbon::createFromFormat('Y-m-d H:i:s', $new_DefectId_reportdatetime_map[$defect->defect_id])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y') : "";
                                            $defect->last_updated_by = isset($new_DefectId_updatedby_map[$defect->defect_id]) ? $new_DefectId_updatedby_map[$defect->defect_id] : "";
                                            $defect->last_updated_at = $new_DefectId_updatedat_map[$defect->defect_id];
                                        }
                                    }
                                }
                            }
                        }  
                    }      

                }
                elseif($check->type == "Vehicle Check"){
                    foreach ($checkJson as $check_json) {
                        if(isset($check_json->screen)){
                            foreach ($check_json->screen as $screen) {
                                if(isset($screen->defects) && isset($screen->defects->defect)){
                                    $defectsArray = $screen->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->defect_status =  isset($preexisting_DefectId_status_map[$defect->defect_id]) ? $preexisting_DefectId_status_map[$defect->defect_id] : "";
                                            $defect->report_datetime = isset($preexisting_DefectId_reportdatetime_map[$defect->defect_id]) ? Carbon::createFromFormat('Y-m-d H:i:s', $preexisting_DefectId_reportdatetime_map[$defect->defect_id])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y') : "";
                                            $defect->last_updated_by = isset($preexisting_DefectId_updatedby_map[$defect->defect_id]) ? $preexisting_DefectId_updatedby_map[$defect->defect_id] : "";
                                            $defect->last_updated_at = isset($preexisting_DefectId_updatedat_map[$defect->defect_id]) ? $preexisting_DefectId_updatedat_map[$defect->defect_id] : "";
                                            $defect->imageString = isset($preexisting_DefectId_imagestring_map[$defect->defect_id]) ? $preexisting_DefectId_imagestring_map[$defect->defect_id] : "";
                                        }
                                        if ($defect->selected === 'yes' && $defect->is_pre_existing_defect == 0 && isset($defect->defect_id) && $defect->defect_id != null) {
                                            $defect->defect_status =  isset($new_DefectId_status_map[$defect->defect_id]) ? $new_DefectId_status_map[$defect->defect_id] : "";
                                            $defect->report_datetime = isset($new_DefectId_reportdatetime_map[$defect->defect_id]) ? Carbon::createFromFormat('Y-m-d H:i:s', $new_DefectId_reportdatetime_map[$defect->defect_id])->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y') : "";
                                            $defect->last_updated_by = isset($new_DefectId_updatedby_map[$defect->defect_id]) ? $new_DefectId_updatedby_map[$defect->defect_id] : "";
                                            $defect->last_updated_at = isset($new_DefectId_updatedat_map[$defect->defect_id]) ? $new_DefectId_updatedat_map[$defect->defect_id] : "";
                                        }
                                    }
                                }
                            }
                        }  
                    }      
                    
                }

                
                $check->json = json_encode($checkJson);
            }
            $check->json = replaceWithPreSignedUrl($check->type,$check->json);
            return $this->response->item($check, new CheckDetailsTransformer);
        }
        else{
            return $this->response->errorNotFound("Check for the vehicle Not Found.");
        }
    }
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($action, Request $request)
    {
        $actionList = ["checkout"=>"Vehicle Check","on-call"=>"Vehicle Check On-call", "checkin"=>"Return Check", "defect"=>"Defect Report"];
        // $statusList = ["roadworthy"=>"Roadworthy", "safetooperate"=>"Safe to operate", "unsafetooperate"=>"Unsafe to operate"];


        Log::info("========================================================================");
        Log::info($request->get('vehicle_id'));
        Log::info($request->get('user_id'));
        Log::info($request->get('odometer_reading'));
        Log::info($request->get('defect_report_type'));
        Log::info($request->get('json'));
        if($request->has('apiId')){
            Log::info('apiId: '.$request->get('apiId'));
        }
        if($request->has('location')){
            Log::info($request->get('location'));
        }
        Log::info("========================================================================");
        $this->vehicle_id = $request->get('vehicle_id');

        $this->user_id = $request->get('user_id');
        $odometerReading = null;
        $checkDuration = null;
        $checkLocation = null;

        $odometerReading = trim($request->get('odometer_reading'));

        if($request->has('apiId') && !empty($request->get('apiId'))) {
            Log::info("Checking apiId: ".$request->get('apiId'). " exists or not");
            $checkExists = DB::table(DB::raw('checks force index (checks_created_at_index)'))
                                        ->where('apiId', $request->get('apiId'))
                                        ->where('created_at', '>=', Carbon::now()->subDays(env('VEHICLE_CHECK_API_DURATION_IN_DAYS', 1))->startOfDay())
                                        ->where('created_at', '<=', Carbon::now()->endOfDay())
                                        ->first(['apiId', 'id']);
            if(isset($checkExists)) {
                Log::info("ApiId: ".$request->get('apiId'). " exists and return with error");
                return response()->json(['message' => 'Check already exists', "status_code" => 200, 'check_id' => $checkExists->id], 200);
            } else {
                Log::info("ApiId: ".$request->get('apiId'). " does not exist and go ahead");
            }
        }
        
        if(!empty($request->get('check_duration'))){
            $checkDuration = $request->get('check_duration');
        }
        if($request->has('location')){
            $checkLocation = $request->get('location');
        }
        $json = trim($request->get('json'));

        if ($this->vehicle_id == ""){
            return $this->response->error('Error adding Check. Vehicle ID missing.', 404);
        }
        if ($this->user_id == ""){
            return $this->response->error('Error adding Check. User ID missing.', 404);
        }
        if ($json == ""){
            Log::info("Error adding Check. json missing.");
            return $this->response->error('Error adding Check. json missing.', 404);
        }
        if(!$this->isJsonValid($json)){
            Log::info("Error adding Check. json valid.");
            return $this->response->error('Error adding Check. json valid', 404);
        }
        $decode_json = json_decode($json);
        if(trim($decode_json->status) == ""){
            Log::info("Error adding Check. Status is missing.");
            return $this->response->error('Error adding Check. Status is missing', 404);
        }
        $vehicle = Vehicle::find($this->vehicle_id);

        if(!$request->has('is_sync_call') || $request->get('is_sync_call') !== true) {
            if(isset($odometerReading) && $vehicle->last_odometer_reading > $odometerReading){
                return $this->response->error('Invalid odometer reading', 404);
            }
            if($vehicle->status == "VOR" || $vehicle->status == "VOR - MOT" || $vehicle->status == "VOR - Bodyshop"){
                    return $this->response->error(config('config-variables.flashMessages.vehicleOffRoad'), 404);
            }
            if($vehicle->status == "Archived"){
                    return $this->response->error('Vehicle no longer in use (Archived)', 404);
            }
            if($vehicle->status == "Archived - De-commissioned"){
                    return $this->response->error('Vehicle no longer in use (Archived - De-commissioned)', 404);
            }
            if($vehicle->status == "Archived - Written off"){
                    return $this->response->error('Vehicle no longer in use (Archived - Written off)', 404);
            }
        }
        
        $reportDatetime = $request->has('report_datetime') ? $request->get('report_datetime') : Carbon::now();
        $check = new Check();
        $check->vehicle_id = $this->vehicle_id;
        $check->type = $actionList[$action];
        $check->odometer_reading = $odometerReading;
        $check->check_duration = $checkDuration;
        $check->location = $checkLocation;
        $check->created_by = $this->user_id;
        $check->updated_by = $this->user_id;
        $check->report_datetime = $reportDatetime;
        $check->json = " ";
        if($request->has('apiId')) {
            $check->apiId = $request->get('apiId');
        }
        $check->status = " ";

        if (!empty($request->get('defect_report_type'))) {
            $defect_report_type = $request->get('defect_report_type');
            $check->defect_report_type = $defect_report_type;
        }
        if ($check->save()){
            $kept_preexisting_defects = isset($decode_json->kept_preexisting_defects) ? $decode_json->kept_preexisting_defects : null;
            if($action == "checkout" || $action == "checkin"){
                //this condition is #1118 related implementation
                $preexistingDefectQuery = Defect::where(['vehicle_id'=>$check->vehicle_id])->where('status', '!=' , 'Resolved');
                $isTrailerAttachedFlag = "no";
                foreach ($decode_json->screens->screen as $screen) {
                    if($screen->_type == "confirm_with_input") {
                        $isTrailerAttached = $screen->answer;
                        if (strtolower($isTrailerAttached) === "yes") {
                            $isTrailerAttachedFlag = "yes";
                            break;
                        }
                    }
                    if ($screen->_type == "yesno") {
                        if(isset($screen->defects->defect)) {
                            foreach ($screen->defects->defect as $defect) {
                                if ($defect->selected == "yes" && (isset($defect->read_only) && trim($defect->read_only) == "yes" )) {
                                    if (($kept_preexisting_defects == null && $defect->id != null) || ($kept_preexisting_defects != null && $defect->id != null && $kept_preexisting_defects->ids != null && !in_array($defect->id, $kept_preexisting_defects->ids))) {
                                        $defect->selected = "no";
                                        $defect->read_only = "no";
                                    }
                                    elseif ($kept_preexisting_defects != null && isset($defect->temp_id) && $defect->temp_id != null && !in_array($defect->temp_id, $kept_preexisting_defects->tempids)) {
                                        $defect->selected = "no";
                                        $defect->read_only = "no";
                                    }
                                }
                            }
                        }
                    }
                    if ($screen->_type == "list") {
                        foreach ($screen->options->optionList as $option) {
                            if(isset($option->defects->defect)) {
                                foreach ($option->defects->defect as $defect) {
                                    if ($defect->selected == "yes" && (isset($defect->read_only) && trim($defect->read_only) == "yes" )) {
                                        if (($kept_preexisting_defects == null && $defect->id != null) || ($kept_preexisting_defects != null && isset($kept_preexisting_defects->ids) && $kept_preexisting_defects->ids != null && $defect->id != null && !in_array($defect->id, $kept_preexisting_defects->ids))) {
                                            $defect->selected = "no";
                                            $defect->read_only = "no";
                                        }
                                        elseif ($kept_preexisting_defects != null && isset($kept_preexisting_defects->tempids) && $kept_preexisting_defects->tempids != null && isset($defect->temp_id) && $defect->temp_id != null && !in_array($defect->temp_id, $kept_preexisting_defects->tempids)) {
                                            $defect->selected = "no";
                                            $defect->read_only = "no";
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                } 
                if ($isTrailerAttachedFlag == "no") {
                    $defectMasterIds = DefectMaster::whereIn('order',explode(',', env('TRAILER_QUESTIONS_ORDER')))->lists('id')->toArray();
                    $preexistingDefectQuery = $preexistingDefectQuery
                                              ->whereNotIn('defect_master_id',$defectMasterIds);

                }

                
                $preExistingDefects = $preexistingDefectQuery->get();

                foreach ($preExistingDefects as $preExistingDefect) {
                    $preexistingDefectAcknowledgement = new PreexistingDefectAcknowledgement();
                    $preexistingDefectAcknowledgement->defect_id = $preExistingDefect->id;
                    $preexistingDefectAcknowledgement->check_id = $check->id;
                    $preexistingDefectAcknowledgement->status = 0;
                    if ($kept_preexisting_defects != null && ($kept_preexisting_defects->ids !== null && in_array($preExistingDefect->id, $kept_preexisting_defects->ids) || (!empty($preExistingDefect->temp_id) && $kept_preexisting_defects->tempids != null && in_array($preExistingDefect->temp_id, $kept_preexisting_defects->tempids)))){                        
                            $preexistingDefectAcknowledgement->status = 1;
                        }
                    $preexistingDefectAcknowledgement->save();
                }
                //fetch pre-existing defects
                //loop PD from db -> entry in new table with status 1: for ids in removed_preexisting_defects  make status as 0
            }
            $this->check_id = $check->id;

            $processedJsonResponse = $this->processJson($json, $check, $reportDatetime);
            $check_json = json_decode($processedJsonResponse['json']);
            $check->json = $processedJsonResponse['json'];
            // $check->status = $statusList[strtolower($check_json->status)];
            $check->status = $check_json->status;
            $check->is_trailer_attached = strtolower($processedJsonResponse['is_trailer_attached']) === "yes" ? 1 : 0;
            $check->trailer_reference_number = $processedJsonResponse['trailer_reference_number'];
            $check->save();

            $data = [
                'location' => $check->location,
                'action' => $action,
                'check_id' => $check->id
            ];

            if(in_array($action, ['checkout', 'on-call']) && (floatval($check->location[0]) > 0 || floatval($check->location[0]) < 0)) {
                $url = env('API_URL_SCHEME'). "/api/v1/checks/mapImage";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $result = curl_exec($ch);
            }

            if(strtolower($check_json->status) == "unsafetooperate"){
                $vehicle->status = "VOR";
                $vehicle->on_road = 0;
            }
            else if(strtolower($check_json->status) == "roadworthy" || strtolower($check_json->status) == "safetooperate"){
                $status = "Roadworthy";
                if($check_json->total_defect > 0 || $check_json->preexisting_defect > 0) {
                    $status = "Roadworthy (with defects)";
                }
                $vehicle->status = $status;
                if($action == "checkout" || $action == "on-call"){
                    $vehicle->on_road = 1;
                }
                elseif($action == "checkin"){
                    $vehicle->on_road = 0;
                }
            }
            $vehicle->last_odometer_reading = $odometerReading;
            $vehicle->save();
            event(new PushNotification(config('config-variables.pushNotification.messages.vehicle_updated')));
            /* ---- */
            if($action == "checkout"){
                $this->vehicleTakeoutTelematics($vehicle->id,$checkLocation,$check->id);
            }
            if ($action == "checkin" || ($action == "defect" && $vehicle->status == "VOR")) {
                if($action == "checkin") {
                    $this->vehicleReturnTelematics($vehicle->id);
                }

                $userLogoutStateTodel = UserLogoutState::where(['user_id'=>$this->user_id,'vehicle_id'=>$vehicle->id,'action'=>'takeout'])->delete();
            }

            // Message change while adding new Defect from the web app
            $resMessage = "Check has been saved successfully";
            if ($request->path() == "api/v1/checkd/defect") {
                $resMessage = "Defect created successfully";
            }
            return response()->json(['message' => $resMessage, "status_code" => 201], 201); //Vehicle Check has been added successfully.
        }
        else{
            return $this->response->error('This is an error added an Vehicle Check', 404);
        }
    }

    private function vehicleTakeoutTelematics($vehicleId, $checkLocation, $checkid)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $check = Check::with('creator')->findOrFail($checkid);
        $position = explode(',', $checkLocation);
        $payload = [
                'vehicle_id' => $vehicleId,
                'registration' => $vehicle->registration,
                'driver_name' => $check->creator->first_name,
                'lat' => $position[0],
                'lng' => $position[1],
            ];
        event(new TelematicsJourneyStart($payload));
    }
    private function vehicleReturnTelematics($vehicle_id)
    {
        $payload = [
            'vehicle_id' => $vehicle_id,
        ];
        event(new TelematicsJourneyEnd($payload));
    }

    private function isJsonValid($jsonString)
    {
        json_decode($jsonString);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function checkMapImage(Request $request) {
        $locationArray = explode(",", $request['location']);
        $checkMapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?center:" .$locationArray[0]. ',' .$locationArray[1]."&format=png&zoom=15&scale=2&size=1000x250&maptype=roadmap&markers=color:red%7C" .$locationArray[0] . ',' .$locationArray[1]. "&key=".config('config-variables.google_map_key');
        $desFolder = public_path('img/checks/gmap/');
        $imageName = 'check_map_image_'.$request['check_id'].'.png';
        $imagePath = $desFolder.$imageName;            
        file_put_contents($imagePath, file_get_contents($checkMapImageUrl));

        $check = Check::find($request['check_id']);
        $checkMapImage = $check->addMedia($imagePath)
                                ->toCollectionOnDisk('checkMapImage', 'S3_uploads');
    }

    public function getTrailerReferenceNumber(Request $request) {
        $trailerReferenceNumbers = Check::whereNotNull('trailer_reference_number')->lists('trailer_reference_number')->unique()->values()->all();
        return response()->json($trailerReferenceNumbers);
    }
}
