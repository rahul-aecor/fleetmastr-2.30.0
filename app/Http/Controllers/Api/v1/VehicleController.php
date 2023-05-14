<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\MaintenanceEvents;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Check;
use App\Models\Defect;
use App\Services\VehicleService;
use App\Transformers\CheckTransformer;
use App\Transformers\DefectTransformer;
use App\Transformers\VehicleTransformer;
use App\Transformers\AllVehicleTransformer;

class VehicleController extends APIController
{
    /**
     * Vehicle service instance.
     *
     * @var object
     */
    protected $vehicleService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function check($action, Request $request)
    {
	    ini_set("memory_limit", "512M");
        $statusList = ['VOR','VOR - MOT','VOR - Bodyshop'];
        $registration_no = $request->get('registration_no');
        
        if(empty($registration_no)){
            return $this->response->errorBadRequest("Bad request. Please provide the vechile registration number.");
        }

        $vehicle = Vehicle::with(['type','lastCheck'])->withTrashed()->where('registration','=',$registration_no)->first();

        if(empty($vehicle)){
            return $this->response->error('Vehicle does not exist', 404);
        }

        if(($action!="vehiclehistory" && $action!="resolvedefect") && ($vehicle->status == "VOR" || $vehicle->status == "VOR - MOT" || $vehicle->status == "VOR - Bodyshop" || $vehicle->status == "VOR - Bodybuilder" || $vehicle->status == "VOR - Service" || $vehicle->status == "VOR - Quarantined")) {
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

        if(!empty($vehicle)) {
            if (in_array($vehicle->status, $statusList) && ($action=="checkout" || $action=="on-call" || $action=="checkin")) {
                return $this->response->error('This registration belongs to a vehicle that is marked VOR (Vehicle Off Road). This vehicle is unsafe to use at present and MUST NOT be taken onto the highway. Please contact a member of the Transport Team for further advice.', 202);
            } else {
                if ($action == "vehiclehistory"){
                    $action = "checkout";
                }
                $survey_ques = \DB::table('survey_master')
                    ->select('id','vehicle_type', 'action')
                    ->where('vehicle_category',$vehicle->type->vehicle_category)
                    ->whereIn('action',['checkin','checkout','defect'])->get();

                $survey_ques_id = 0;
                $checkout_survey_ques_id = 0;
                $checkin_survey_ques_id = 0;
                $defect_survey_ques_id = 0;
                foreach ($survey_ques as $survey_que) {
                    $vtypeArr = explode(',', $survey_que->vehicle_type);
                    if(in_array($vehicle->type->id, $vtypeArr)){
                        if ($survey_que->action == 'checkin') {
                            $checkin_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'checkout') {
                            $checkout_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'defect') {
                            $defect_survey_ques_id = $survey_que->id;
                        }
                        if($survey_que->action == $action) {
                            $survey_ques_id = $survey_que->id;
                        }
                        continue;
                    }
                    
                }

                $pre_existing_defect=false;
                $defects_list = $this->vehicleService->getDefectList($vehicle->id);
                if(count($defects_list)>0){
                    $pre_existing_defect = true;
                }

                $totalCheckCount = Check::where('vehicle_id','=',$vehicle->id)
                                        ->whereIn('type',['Return Check','Vehicle Check'])
                                        ->count();

                $returnValue = $this->response->item($vehicle, new VehicleTransformer)
                                ->addMeta('survey_master_id', $survey_ques_id)
                                ->addMeta('checkin_survey_ques_id', $checkin_survey_ques_id)
                                ->addMeta('checkout_survey_ques_id', $checkout_survey_ques_id)
                                ->addMeta('defect_survey_ques_id', $defect_survey_ques_id)
                                ->addMeta('pre_existing_defect',$pre_existing_defect);
                    $returnValue = $returnValue->addMeta('defects_list_title','Defects Alert')->addMeta('defects_list_message','This vehicle has defects')->addMeta('defects_list',$defects_list)->addMeta('check_count', $totalCheckCount);

                return $returnValue;
            }
        } else {
            return $this->response->errorNotFound("Vehicle not found in fleet database.");
        }
    }

    public function history(Request $request)
    {
        $pageCount = 15;
        $vehicleId = $request->get('vehicle_id');
        $page = $request->get('page', 1);

        if(empty($vehicleId)){
            return $this->response->errorBadRequest("Bad request. Please provide vehicle Id.");
        }

        $totalCount = $vehicleCheck = Check::where('vehicle_id','=',$vehicleId)->where('type', '<>', 'Defect Report')->count();
        $totalPages = ceil($totalCount/$pageCount);

        if($page > $totalPages){
            $page = $totalPages;
        }

        $vehicleCheck = Check::where('vehicle_id','=',$vehicleId)
            ->where('type', '<>', 'Defect Report')
            ->orderBy('report_datetime', 'desc')
            ->skip((($page-1)*$pageCount))
            ->take($pageCount)
            ->get();
        
        $meta = ['pagination'=> ['total'=>$totalCount, 'per_page'=>$pageCount, 'current_page'=>$page, 'total_pages'=> ceil($totalCount/$pageCount)]];

        if(!empty($vehicleCheck)){
            return $this->response->collection($vehicleCheck, new CheckTransformer)->setMeta($meta);
        } else {
            return $this->response->errorNotFound("Vehicle check not found.");
        }
    }

    public function allVehicles(Request $request)
    {
        $last_updated_timestamp = null;
        if (isset($request['last_updated_timestamp']) && $request['last_updated_timestamp'] != null && !empty($request['last_updated_timestamp'])) {
            $last_updated_timestamp = Carbon::createFromFormat(config('config-variables.apiTimeFormat'), $request['last_updated_timestamp'], config('config-variables.displayTimezone'));
            $last_updated_timestamp->setTimezone('UTC');
        }

        return $this->vehicleService->allVehicles($last_updated_timestamp);
    }

    public function getVehicleDetails($id)
    {
        $vehicle = Vehicle::with(['maintenanceHistories','type', 'defects' => function($query){$query->where('status','<>','Resolved');}, 'defects.defectMaster','defects.media'])->where('vehicles.id', $id)->first();
        if (!empty($vehicle)){
            $survey_quesMaster = DB::table('survey_master')
                ->select('id','vehicle_type','action','vehicle_category')
                ->whereIn('action',['checkin','checkout','defect'])->get();
            $survey_quesMaster = collect($survey_quesMaster)->groupBy('vehicle_category');

            $countsMastr = DB::table('checks')
                ->select('vehicle_id', \DB::raw('count(*) as count'))
                ->groupBy('vehicle_id')
                ->get();

            $countsMastr = collect($countsMastr)->groupBy('vehicle_id');

            $distanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
            $survey_ques = isset($survey_quesMaster[$vehicle->type->vehicle_category]) ? $survey_quesMaster[$vehicle->type->vehicle_category]->toArray() : [];
            $checkout_survey_ques_id = 0;
            $checkin_survey_ques_id = 0;
            $defect_survey_ques_id = 0;
            foreach ($survey_ques as $survey_que) {
                $vtypeArr = explode(',', $survey_que->vehicle_type);
                if(in_array($vehicle->type->id, $vtypeArr)){
                    if ($survey_que->action == 'checkin') {
                        $checkin_survey_ques_id = $survey_que->id;
                    }
                    if ($survey_que->action == 'checkout') {
                        $checkout_survey_ques_id = $survey_que->id;
                    }
                    if ($survey_que->action == 'defect') {
                        $defect_survey_ques_id = $survey_que->id;
                    }                        
                }
            }
            $vehicleFormatted = $vehicle->format();            
            if ($vehicle->type->service_interval_type == 'Distance' && $vehicle->next_service_inspection_distance && $vehicle->maintenanceHistories && count($vehicle->maintenanceHistories) > 0) {
                $lastInspectionDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);

                $past = collect($vehicle->maintenanceHistories->toArray())->where('event_type_id',$distanceEvent->id)
                    ->where('event_planned_distance',(string)$lastInspectionDistance)
                    ->where('event_status','Incomplete')->first();

                if ($past) {
                    $vehicleFormatted['data']['is_next_service_distance_exceeded'] = true;
                    $vehicleFormatted['data']['previous_next_service_distance'] = $lastInspectionDistance;
                }
            }
            $vehicleFormatted["meta"]["pre_existing_defect"] = false;
            $defects_list = $this->vehicleService->getDefectList($vehicle->id, $vehicle);
            if(count($defects_list)>0){
                $vehicleFormatted["meta"]["pre_existing_defect"] = true;
            }
            $totalCheckCount = (isset($countsMastr[$vehicle->id]) && isset($countsMastr[$vehicle->id][0]->count)) ? $countsMastr[$vehicle->id][0]->count: 0;
            $vehicleFormatted["meta"]["defects_list"] = $defects_list;
            $vehicleFormatted["meta"]["checkin_survey_ques_id"] = $checkin_survey_ques_id;
            $vehicleFormatted["meta"]["checkout_survey_ques_id"] = $checkout_survey_ques_id;
            $vehicleFormatted["meta"]["defect_survey_ques_id"] = $defect_survey_ques_id;
            $vehicleFormatted["meta"]["check_count"] = $totalCheckCount;
            return $vehicleFormatted;
            array_push($vehicleList, $vehicleFormatted);                
        } else {
            return $this->response->errorNotFound("Vehicle not found.");
        }
    }

    public function getVehiclesDetail(Request $request)
    {
        $vehicleList = [];
        $vehicles = Vehicle::with(['maintenanceHistories','type', 'defects' => function($query){
                                $query->where('status','<>','Resolved');
                            }, 'defects.defectMaster','defects.media'])
                            ->whereIn('vehicles.id', $request->vehicle_ids);
        if ($request['last_updated_timestamp'] == null || empty($request['last_updated_timestamp'])) {
            $vehicles = $vehicles->get();
        } else {
            $last_updated_timestamp = Carbon::createFromFormat(config('config-variables.apiTimeFormat'), $request['last_updated_timestamp'], config('config-variables.displayTimezone'));            
            $last_updated_timestamp->setTimezone('UTC');

            $vehicles = $vehicles->where('vehicles.updated_at', '>=', $last_updated_timestamp)
                                ->get();
        }
        if (!empty($vehicles)){
            $survey_quesMaster = DB::table('survey_master')
                ->select('id','vehicle_type','action','vehicle_category')
                ->whereIn('action',['checkin','checkout','defect'])->get();
            $survey_quesMaster = collect($survey_quesMaster)->groupBy('vehicle_category');

            $countsMastr = DB::table('checks')
                ->select('vehicle_id', \DB::raw('count(*) as count'))
                ->groupBy('vehicle_id')
                ->get();

            $countsMastr = collect($countsMastr)->groupBy('vehicle_id');

            $distanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
            foreach ($vehicles as $vehicle) {
                $survey_ques = isset($survey_quesMaster[$vehicle->type->vehicle_category]) ? $survey_quesMaster[$vehicle->type->vehicle_category]->toArray() : [];
                $checkout_survey_ques_id = 0;
                $checkin_survey_ques_id = 0;
                $defect_survey_ques_id = 0;
                foreach ($survey_ques as $survey_que) {
                    $vtypeArr = explode(',', $survey_que->vehicle_type);
                    if(in_array($vehicle->type->id, $vtypeArr)){
                        if ($survey_que->action == 'checkin') {
                            $checkin_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'checkout') {
                            $checkout_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'defect') {
                            $defect_survey_ques_id = $survey_que->id;
                        }                        
                    }
                }
                $vehicleFormatted = $vehicle->format();            
                if ($vehicle->type->service_interval_type == 'Distance' && $vehicle->next_service_inspection_distance && $vehicle->maintenanceHistories && count($vehicle->maintenanceHistories) > 0) {
                    $lastInspectionDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);

                    $past = collect($vehicle->maintenanceHistories->toArray())->where('event_type_id',$distanceEvent->id)
                        ->where('event_planned_distance',(string)$lastInspectionDistance)
                        ->where('event_status','Incomplete')->first();

                    if ($past) {
                        $vehicleFormatted['data']['is_next_service_distance_exceeded'] = true;
                        $vehicleFormatted['data']['previous_next_service_distance'] = $lastInspectionDistance;
                    }
                }
                $vehicleFormatted["meta"]["pre_existing_defect"] = false;
                $defects_list = $this->vehicleService->getDefectList($vehicle->id, $vehicle);
                if(count($defects_list)>0){
                    $vehicleFormatted["meta"]["pre_existing_defect"] = true;
                }
                $totalCheckCount = (isset($countsMastr[$vehicle->id]) && isset($countsMastr[$vehicle->id][0]->count)) ? $countsMastr[$vehicle->id][0]->count: 0;
                $vehicleFormatted["meta"]["defects_list"] = $defects_list;
                $vehicleFormatted["meta"]["checkin_survey_ques_id"] = $checkin_survey_ques_id;
                $vehicleFormatted["meta"]["checkout_survey_ques_id"] = $checkout_survey_ques_id;
                $vehicleFormatted["meta"]["defect_survey_ques_id"] = $defect_survey_ques_id;
                $vehicleFormatted["meta"]["check_count"] = $totalCheckCount;
                array_push($vehicleList, $vehicleFormatted);                
            }
        } else {
            return $this->response->errorNotFound("Vehicles not found.");
        }
        return $vehicleList;
    }
}
