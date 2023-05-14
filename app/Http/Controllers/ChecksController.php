<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Input;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Check;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\VehicleVORLog;
use App\Models\ColumnManagements;
use Spatie\MediaLibrary\Media;
use JavaScript;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ChecksRepository;
use Illuminate\Support\Facades\Config;
use App\Custom\Facades\GridEncoder;
use PDF;
use App\Traits\ProcessCheckJson;
use View;
use DB;
use App\Services\VehicleService;
use App\Models\PreexistingDefectAcknowledgement;

class ChecksController extends Controller
{
    use ProcessCheckJson;

    private $user_id;
    private $vehicle_id;
    private $check_id;

    public $title= 'Vehicle Checks';

    public function __construct(VehicleService $vehicleService) {
        View::share ( 'title', $this->title );
        $this->vehicleService = $vehicleService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = [];
        $filtersFields = [];        
        if ($request->has('show')) {
            $filtersFields = $this->getChecksFilters($request->get('show'));
            $filters = $filtersFields['filters'];            
        }
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        //$vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))->get();
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicles.vehicle_region_id', $userRegions)->get();
        $user = Auth::user();

        $checkSearch = [];

        $column_management = ColumnManagements::where('user_id',$request->user()->id)
        ->where('section','checks')
        ->select('data')
        ->first();

        if($user->isUserInformationOnly()) {
            $userInformationOnly = Check::where('checks.created_by',Auth::user()->id)->get();

            $checkRegistrationSearch = DB::table('checks')->where('checks.created_by',Auth::user()->id)
                ->join('vehicles', 'checks.vehicle_id', '=', 'vehicles.id')->select('vehicles.registration')->distinct('registration')->get();
                
            foreach ($checkRegistrationSearch as $key => $checkRegistration) {
                $checkSearch[$key]['id'] = $checkRegistration->registration;
                $checkSearch[$key]['text'] = $checkRegistration->registration;
            }
        }

        $userData = User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->orderBy('email', 'asc')->get();

        $userDataArray = array();
        foreach ($userData as $key => $user) {
            if ($user->email) {
                $customString = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->email . ')';
            } else {
                $customString = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->username . ')';
            }
            array_push($userDataArray, ['id'=>$user->id, 'text'=>$customString]);
        }

        $data = $this->vehicleService->getDataDivRegLoc();
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $region_for_select = $this->vehicleService->regionForSelect($data);
        } else {
            $region_for_select = $data['vehicleRegions'];
        }
        $region_for_select = ['' => ''] + $region_for_select;
        $region_for_select = collect($region_for_select);

        $vehicleCheckType = config('config-variables.vehicle_check_type');

        JavaScript::put([
            'vehicleRegistrations' => $vehicleRegistrations,
            'filters' => $filters,
            'filtersFields' => $filtersFields,
            'column_management' => $column_management,
            'checkSearch' => $checkSearch,
            'userDataArray' => $userDataArray,
        ]);
        return view('checks.index', compact('vehicleRegistrations','region_for_select','vehicleCheckType'));
    }

    /**
     * Return the checks data for the grid
     * 
     * @return [type] [description]
     */
    public function anyData()
    {
        return GridEncoder::encodeRequestedData(new ChecksRepository(), Input::all());
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();

        ///$vehicleRegistrations = Vehicle::select('registration as id', 'registration as text', 'last_odometer_reading as odometer')->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))->get();
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text', 'last_odometer_reading as odometer')->whereIn('vehicles.vehicle_region_id', $userRegions)->get();
        $checkTrailerReferenceNumber = Check::select('trailer_reference_number')->whereNotNull('trailer_reference_number')->distinct()->lists('trailer_reference_number');

        $appUrl = config('config-variables.app_url').'/defects';
        $isTrailerFeatureEnabled = setting('is_trailer_feature_enabled');

        JavaScript::put([
            'vehicleRegistrations' => $vehicleRegistrations,
            'authuserid' => $user->id,
            'appUrl' => $appUrl,
            'is_trailer_feature_enabled' => $isTrailerFeatureEnabled,
            'checkTrailerReferenceNumber' => $checkTrailerReferenceNumber,
        ]);
        return view('checks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $check = Check::with('creator', 'updater','vehicle', 'vehicle.type')->select('checks.*',DB::raw("CASE WHEN check_duration IS NOT NULL THEN
        CONCAT(
        CASE WHEN floor(TIME_TO_SEC(check_duration) / 60) = '00' THEN '00 min ' ELSE CONCAT(floor(TIME_TO_SEC(check_duration) / 60), ' min ') END,
        CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1) = '00' THEN ' 00 sec ' ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1), ' sec') END
        ) ELSE 'N/A' END AS  check_duration"))->whereHas('vehicle', function($q) use ($userRegions) {
                    $q->whereIn('vehicle_region_id', $userRegions);
                })
            ->where('id', $id)
            ->first();

        if($check == null || !$user->isHavingRegionAccess($check->vehicle->vehicle_region_id)){
             return redirect('/checks');
        }
        if($user->isUserInformationOnly() && $check->creator->id != $user->id){
             return redirect('/checks');
        }
        // $recreatedJson = $this->recreateCheckJson($check);
        // $check->json = $recreatedJson;
        if(!empty($check)){
            if(trim($check->json) != "") {
                $checkJson = json_decode($check->json);
                $checkJson->total_defect = 0;
                $preexistingDefectIds = PreexistingDefectAcknowledgement::where('check_id',$id)->where('status','1')->select('defect_id')->get()->toArray();
                //$preexistingDefects = Defect::with('updater')->whereIn('id',$preexistingDefectIds)->get()->toArray();
                $preExistingDefectObjects = Defect::with('updater')->whereIn('id',$preexistingDefectIds)->get();
                $preexistingDefectArrayIds = [];
                $preexistingDefectMasterIds = [];
                $preexistingDefectMasterId_DefectId_map = [];
                $preexisting_DefectId_imagestring_map = [];
                $newly_added_defects = Defect::with('updater')->where('check_id',$id)->get();
                //foreach ($preexistingDefects as $defect) {
                foreach ($preExistingDefectObjects as $defectObj) {
                    $defect = $defectObj->toArray();
                    array_push($preexistingDefectMasterIds,$defect['defect_master_id'] );
                    array_push($preexistingDefectArrayIds,$defect['id']);
                    $preexistingDefectMasterId_DefectId_map[$defect['defect_master_id']] = $defect['id'];
                    $preexisting_DefectId_status_map[$defect['id']] = $defect['status'];
                    $preexisting_DefectId_reportdatetime_map[$defect['id']] = $defect['report_datetime'];
                    $preexisting_DefectId_updatedby_map[$defect['id']] = $defect['updater']['first_name'].' '.$defect['updater']['last_name'];

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
                }
                if($check->type == "Return Check"){
                    foreach ($checkJson->screens->screen as $screen) {
                        if(isset($screen->options->optionList)){
                            foreach ($screen->options->optionList as $option) {
                                $option->defect_count = 0;
                                $option->prohibitional_defect_count = 0;
                                if(isset($option->defects) && isset($option->defects->defect)){
                                    $defectsArray = $option->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->imageString = $preexisting_DefectId_imagestring_map[$defect->defect_id];
                                        }
                                        // re-calculating defects count
                                        if($defect->selected == "yes"){
                                            if( $defect->prohibitional == "yes" ){
                                                $option->prohibitional_defect_count = $option->prohibitional_defect_count + 1;
                                            }
                                            else{
                                                $option->defect_count = $option->defect_count + 1;
                                            }
                                            $checkJson->total_defect = $checkJson->total_defect + 1;
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
                                $screen->defect_count = 0;
                                $screen->prohibitional_defect_count = 0;
                                if(isset($screen->defects) && isset($screen->defects->defect)){
                                    $defectsArray = $screen->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->imageString = $preexisting_DefectId_imagestring_map[$defect->defect_id];
                                        }
                                        // re-calculating defects count
                                        if($defect->selected == "yes"){
                                            if( $defect->prohibitional == "yes" ){
                                                $screen->prohibitional_defect_count = $screen->prohibitional_defect_count + 1;
                                            }
                                            else{
                                                $screen->defect_count = $screen->defect_count + 1;
                                            }
                                            $checkJson->total_defect = $checkJson->total_defect + 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $check->json = json_encode($checkJson);
            }
        }
        $check->json = replaceWithPreSignedUrl($check->type,$check->json);
        $json = json_decode($check->json, true);
        $options = $json['screens']['screen'][0]['options']['optionList'];

        $vehicleVORLogData = VehicleVORLog::where(['vehicle_id'=>$check->vehicle->id,
         'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();

        $vehicleDays = Vehicle::where('id',$check->vehicle->id)->withTrashed()->first()->toArray();

        $vorDuration = '';
        if(starts_with($vehicleDays['status'], 'VOR')) {
            if(!empty($vehicleVORLogData)){
                $now = Carbon::now();
                $dayLabel = '';
            
                if ($vehicleVORLogData->vorDuration > 1) {
                    $dayLabel = ' days';
                } else {
                    $dayLabel = ' day';
                }
                $vorDuration = ($vehicleVORLogData->vorDuration < 1)? 'Today': $vehicleVORLogData->vorDuration . $dayLabel;
            }
        } else {
            $vorDuration = NULL;
        }
        JavaScript::put([
            'check' => $check,
            'vorDuration' => $vorDuration
        ]);
        
        return view('checks.show', compact('check', 'options', 'vorDuration'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('checks.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = \Auth::user();

        $check = Check::find($id);
        
        $this->user_id = $user->id;
        $this->vehicle_id = $check->vehicle_id;
        $this->check_id = $id;
        $json = $request->get('json');
        $processedJsonResponse = $this->processJson($json, $check);
        $check->json = $processedJsonResponse['json'];
        $check->updated_by = $user->id;
        if($check->save()){
            return response()->json(['message' => "Check has been saved successfully", "status_code" => 200], 200); //Vehicle Check has been updated successfully.
        }
        else{
            return $this->response->error('This is an error in updating Vehicle Check', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
     * Export pdf
     **/
    public function exportPdf($id) {
        $check = Check::with('vehicle.type')->findOrFail($id);
        $locationImage = "";
        $locationArray = [];
        if(isset($check->location)) {
            $locationArray = explode(",", $check->location);
            if(!$check->hasMedia('checkMapImage')) {
                $this->checkMapImage($check);
                $check = Check::with('vehicle.type')->findOrFail($id);
            }
            $media = $check->getMedia('checkMapImage');
            $locationImage = getPresignedUrl($media[0]);
        } else {
            $locationArray = [];
        }

        // $recreatedJson = $this->recreateCheckJson($check);
        // $check->json = $recreatedJson;

        if(!empty($check)){
            if(trim($check->json) != "") {
                $checkJson = json_decode($check->json);
                $checkJson->total_defect = 0;
                $preexistingDefectIds = PreexistingDefectAcknowledgement::where('check_id',$id)->where('status','1')->select('defect_id')->get()->toArray();
                $preExistingDefectObjects = Defect::with('updater')->whereIn('id',$preexistingDefectIds)->get();
                $preexistingDefectArrayIds = [];
                $preexistingDefectMasterIds = [];
                $preexistingDefectMasterId_DefectId_map = [];
                $preexisting_DefectId_imagestring_map = [];
                $newly_added_defects = Defect::with('updater')->where('check_id',$id)->get();

                foreach ($preExistingDefectObjects as $defectObj) {
                    $defect = $defectObj->toArray();
                    array_push($preexistingDefectMasterIds,$defect['defect_master_id'] );
                    array_push($preexistingDefectArrayIds,$defect['id']);
                    $preexistingDefectMasterId_DefectId_map[$defect['defect_master_id']] = $defect['id'];
                    $preexisting_DefectId_status_map[$defect['id']] = $defect['status'];
                    $preexisting_DefectId_reportdatetime_map[$defect['id']] = $defect['report_datetime'];
                    $preexisting_DefectId_updatedby_map[$defect['id']] = $defect['updater']['first_name'].' '.$defect['updater']['last_name'];

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
                }

                if($check->type == "Return Check"){
                    foreach ($checkJson->screens->screen as $screen) {
                        if(isset($screen->options->optionList)){
                            foreach ($screen->options->optionList as $option) {
                                $option->defect_count = 0;
                                $option->prohibitional_defect_count = 0;
                                if(isset($option->defects) && isset($option->defects->defect)){
                                    $defectsArray = $option->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->imageString = $preexisting_DefectId_imagestring_map[$defect->defect_id];
                                        }
                                        // re-calculating defects count
                                        if($defect->selected == "yes"){
                                            if( $defect->prohibitional == "yes" ){
                                                $option->prohibitional_defect_count = $option->prohibitional_defect_count + 1;
                                            }
                                            else{
                                                $option->defect_count = $option->defect_count + 1;
                                            }
                                            $checkJson->total_defect = $checkJson->total_defect + 1;
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
                                $screen->defect_count = 0;
                                $screen->prohibitional_defect_count = 0;
                                if(isset($screen->defects) && isset($screen->defects->defect)){
                                    $defectsArray = $screen->defects->defect;
                                    foreach ($defectsArray as $defect) {
                                        $defect->is_pre_existing_defect = 0;
                                        if (isset($defect->defect_id) && in_array($defect->defect_id, $preexistingDefectArrayIds)) {
                                            $defect->is_pre_existing_defect = 1;
                                            $defect->selected = 'yes';
                                            // $defect->defect_id = $preexistingDefectMasterId_DefectId_map[$defect->id];//note : $defect->id is defectMasterId
                                            $defect->imageString = $preexisting_DefectId_imagestring_map[$defect->defect_id];
                                        }
                                        // re-calculating defects count
                                        if($defect->selected == "yes"){
                                            if( $defect->prohibitional == "yes" ){
                                                $screen->prohibitional_defect_count = $screen->prohibitional_defect_count + 1;
                                            }
                                            else{
                                                $screen->defect_count = $screen->defect_count + 1;
                                            }
                                            $checkJson->total_defect = $checkJson->total_defect + 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $check->json = json_encode($checkJson);

            }
        }

        $check_details = json_decode($check->json);
        $footerHTML = url().'/html/footer.blade.php';

        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);

        $pdf = PDF::loadView('pdf.checkExport', array('check' => $check, 'check_details' => $check_details, 'location' => $locationArray, 'locationImage' => $locationImage))
            ->setPaper('a4')
            ->setOrientation('portrait')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));

        $filename = $check->vehicle->registration . '_' . 'check' . $id . '_' . $check->report_datetime->format('dmY') . '.pdf';
        return $pdf->download($filename);
    }
    
    /**
     * Export Word
     **/
    public function exportWord($id) {
        error_reporting(E_ALL ^ E_STRICT);
        $check = Check::with('vehicle.type')->findOrFail($id);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();    
        $document = $phpWord->loadTemplate(public_path('wordTemplate/checkDetails.docx'));

        $document->setValue('date', htmlspecialchars($check->report_datetime, ENT_COMPAT, 'UTF-8'));
        $document->setValue('registration', htmlspecialchars($check->vehicle->registration, ENT_COMPAT, 'UTF-8'));
        $document->setValue('manufacturer', htmlspecialchars($check->vehicle->type->manufacturer, ENT_COMPAT, 'UTF-8'));
        $document->setValue('model', htmlspecialchars($check->vehicle->type->model, ENT_COMPAT, 'UTF-8'));
        $document->setValue('status', htmlspecialchars($check->status, ENT_COMPAT, 'UTF-8'));
        $document->setValue('odometer', htmlspecialchars($check->vehicle->last_odometer_reading . ' ' . $check->vehicle->type->odometer_setting, ENT_COMPAT, 'UTF-8'));
        $document->setValue('RESULT', htmlspecialchars($check->status, ENT_COMPAT, 'UTF-8'));
        $temp_file = base_path('storage/Check Details.docx');

        $document->saveAs($temp_file);  
        return response()->download($temp_file);  
    }

    protected function getChecksFilters($show)
    {
        $start_range = Carbon::today(config('config-variables.displayTimezone'))->toDateTimeString();
        $end_range = Carbon::tomorrow(config('config-variables.displayTimezone'))->toDateTimeString();

        $filters = [
            'groupOp' => 'AND',
            'rules' => [
                ['field' => 'checks.report_datetime', 'op' => 'ge', 'data' => $start_range],
                ['field' => 'checks.report_datetime', 'op' => 'lt', 'data' => $end_range],
            ],
        ];
        if ($show === 'roadworthy') {
            $status = 'RoadWorthy';
            array_push($filters['rules'], ['field' => 'checks.status', 'op' => 'eq', 'data' => 'RoadWorthy']);
        }
        if ($show === 'safe-to-operate') {
            $status = 'SafeToOperate';
            array_push($filters['rules'], ['field' => 'checks.status', 'op' => 'eq', 'data' => 'SafeToOperate']);
        }
        if ($show === 'unsafe-to-operate') {
            $status = 'UnsafeToOperate';
            array_push($filters['rules'], ['field' => 'checks.status', 'op' => 'eq', 'data' => 'UnsafeToOperate']);
        }

        return [
            'filters' => $filters,
            'status' => $status,
            'startRange' => Carbon::today(config('config-variables.displayTimezone'))->format('d/m/Y'),
            'endRange' => Carbon::today(config('config-variables.displayTimezone'))->format('d/m/Y'),
        ];
    }

    public function convertJson()
    {
        $checks = Check::all();
        foreach ($checks as $check) {
            $recreatedJson = $this->recreateCheckJson($check);
            $check->json = $recreatedJson;
            // print_r($check->json);
            $check->save();
        }
    }

    public function updateCheckImagePathInJson(Request $request)
    {
        $media = Media::where('name', $request->temp_id)->where('model_type', Check::class)->first();
        if($media) {
            $url = $media->getUrl();
            $check = Check::find($media->model_id);
            $check->json = str_replace($request->temp_id, $url, $check->json);
            $check->save();

            return $url;
        }
        return '';
    }

    public function checkMapImage($check) {
        $locationArray = explode(",", $check->location);
        $checkMapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?center:" .$locationArray[0]. ',' .$locationArray[1]."&format=png&zoom=15&scale=2&size=1000x250&maptype=roadmap&markers=color:red%7C" .$locationArray[0] . ',' .$locationArray[1]. "&key=".config('config-variables.google_map_key');

        $desFolder = public_path('img/checks/gmap/');
        $imageName = 'check_map_image_'.$check->id.'.png';
        $imagePath = $desFolder.$imageName;
        file_put_contents($imagePath, file_get_contents($checkMapImageUrl));

        $checkMapImage = $check->addMedia($imagePath)
                                ->toCollectionOnDisk('checkMapImage', 'S3_uploads');
    }
}
