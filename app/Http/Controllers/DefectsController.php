<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Input;
use Storage;
use JavaScript;
use Mail;
use DB;
use App\Http\Requests;
use App\Models\Role;
use App\Models\User;
use App\Models\Defect;
use App\Models\ColumnManagements;
use App\Models\DefectMaster;
use App\Models\DefectHistory;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\VehicleVORLog;
use App\Http\Controllers\Controller;
use App\Repositories\ChecksRepository;
use Illuminate\Support\Facades\Config;
use App\Repositories\DefectsRepository;
use App\Repositories\WorkshopDefectsRepository;
use App\Custom\Facades\GridEncoder;
use App\Http\Requests\StoreDefectHistoryRequest;
use App\Events\PushNotification;
use PDF;
use View;
use Carbon\Carbon as Carbon;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Services\UserService;
use App\Models\Settings;
use Spatie\MediaLibrary\Media;


class DefectsController extends Controller
{
    public $title= 'Vehicle Defects';

    public function __construct() {
        View::share ( 'title', $this->title );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->get();
        $vehicleDriverRawData = User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->orderBy('email', 'asc')->get();
        $vehicleDriverdata = array();
        foreach ($vehicleDriverRawData as $key => $value) {
            if ($value->email) {
                $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->email . ')';
            } else {
                $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->username . ')';
            }
            array_push($vehicleDriverdata, ['id'=>$value->id, 'text'=>$customString]);
        }
        
        $user = Auth::user();
        
        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
         })->with(['company'])->get();

        $column_management = ColumnManagements::where('user_id',$user->id)
        ->where('section','vehiclesDefects')
        ->select('data')
        ->first();

        $workshopData = []; 

        $defectAllocatedTo = [];

        $defectSearch = [];

        $userRoles = Auth::user()->roles()->get();

        $roleCount = count($userRoles);

        $workshopUserRole = [];
        
        $workshopUser = $userRoles->map(function ($item, $key) use (&$workshopUserRole) {
            if($item['name'] == 'Workshop manager') {
                $workshopUserRole = $item; 
            }
        });

        if($user->isUserInformationOnly()) {
            $userInformationOnly = Defect::where('defects.created_by',Auth::user()->id)->get();

            $defctRegistrationSearch = DB::table('defects')->where('defects.created_by',Auth::user()->id)
                ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')->select('vehicles.registration')->distinct('registration')->get();
                
            foreach ($defctRegistrationSearch as $key => $defctRegistration) {
                $defectSearch[$key]['id'] = $defctRegistration->registration;
                $defectSearch[$key]['text'] = $defctRegistration->registration;
            }
            
            foreach ($userInformationOnly as $userInformation) {
                $userData = User::where('id', $userInformation->workshop)->with('company')->get();
                foreach ($userData as $key => $user) {
                    $userRecord = [];
                    $userRecord['id'] = $user->id;
                    $userRecord['text'] = $user->company->name. ' ('.$user->first_name.' '. $user->last_name.')';
                    array_push($defectAllocatedTo, $userRecord);  

                }  
            }
        } 
        
        if($roleCount && $workshopUserRole) {
            $user = [];
            $user['id'] = Auth::user()->id;
            $user['text'] = 'My defects';
            array_push($workshopData, $user);
        }

        foreach ($workshopusers as $key => $value) {
            $user = [];
            $user['id'] = $value->id;
            $user['text'] = $value->company->name. ' ('.$value->first_name.' '. $value->last_name.')';
            array_push($workshopData, $user);
        }

        $vehicleListing = (new UserService())->getAllVehicleDashboardData();
            
       // $defctRegistrationSearch = [['id'=>'aaa','text'=>'aaaa'],['id'=>'aaaccc','text'=>'bbbccc']];
        $vorOpportunityCostPerDay = 0;
        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostJson = $fleetCost->value;
        $fleetCostData = json_decode($fleetCostJson, true);
        if(isset($fleetCostData['vor_opportunity_cost_per_day'])){
            $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;
        }


        JavaScript::put([
            'vehicleRegistrations' => $vehicleRegistrations,
            'workshopData' => $workshopData,
            'defectAllocatedTo' => $defectAllocatedTo,
            'column_management' => $column_management,
            'defectSearch' => $defectSearch,
            'vehicleDriverdata' => $vehicleDriverdata,
            'vorOpportunityCostPerDay' => $vorOpportunityCostPerDay
        ]);
        return view('defects.index', compact('vehicleListing'));
    }

    /**
     * Return the checks data for the grid
     * 
     * @return [type] [description]
     */
    public function anyData()
    {
        if(Auth::user()->isWorkshopManager()){
            $user = User::find(Auth::user()->id);
            return GridEncoder::encodeRequestedData(new WorkshopDefectsRepository(['id'=>$user->id]), Input::all());
        }
        return GridEncoder::encodeRequestedData(new DefectsRepository(), Input::all());
    }
    /*public function anyWorkshopUserData()
    {
        $user = User::find(Auth::user()->id);
        //print_r($user->company_id);exit;
        return GridEncoder::encodeRequestedData(new WorkshopDefectsRepository(['id'=>$user->company_id]), Input::all());
    }*/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->get();
        JavaScript::put([
            'user' => Auth::user(),            
            'vehicleRegistrations' => $vehicleRegistrations
        ]);
        return view('defects.create');
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
        $defect = Defect::with('vehicle.type', 'defectMaster', 'creator', 'check', 'workshop','updater')
        ->whereHas('vehicle', function($q)
            {
                // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
            })
        ->where('id', $id)
        ->first();

        if(!$defect || !$user->isHavingRegionAccess($defect->vehicle->vehicle_region_id)) {
            return redirect('/defects');
        }

        if ($defect->creator == null) {
            $defect->creator = User::withDisabled()->where('id', $defect->created_by)->first();
        } 
        if ($defect->updater == null) {
            $defect->updater = User::withDisabled()->where('id', $defect->updated_by)->first();
        } 

        if($user->isUserInformationOnly() && $defect->creator->id != $user->id){
             return redirect('/defects');
        }

        $images = $defect->getMedia();
        $vehicleVORLogData = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();
        
        $vorDuration = '';
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
        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->orderBy('report_datetime', 'desc')->orderBy('id', 'desc')->get();
        foreach ($comments as $key => $comment) {
            if ($comment->creator == null) {
                $comment->creator = User::withDisabled()->where('id', $comment->created_by)->first();
            } 
        }

        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
         })
        //->where('is_verified', 1)
        ->where('is_disabled', 0)
        ->with(['company'])->get();

        $workshops = [];
        $workshopUserArray = [];
        foreach ($workshopusers as $key => $value) {
            $workshopuser = [];
            $workshopuser['value'] = $value->id;
            $workshopuser['text'] = $value->company->name.' ('.$value->first_name.' '.$value->last_name.')';
            // $workshopstring = '{"value":"'.$value->id.'","text":"'.$value->company->name.' ('.$value->first_name.' '.$value->last_name.')"}'; 
            array_push($workshopUserArray, $workshopuser);
        }

        array_multisort(array_column($workshopUserArray, "text"), SORT_ASC, $workshopUserArray);
        foreach ($workshopUserArray as $key => $workshop) {
            $workshopstring = json_encode($workshop); 
            array_push($workshops, $workshopstring);
        }

        $vehicleDefectRecords = Defect::with('vehicle','defectMaster')->where('vehicle_id',$defect->vehicle_id)->where('status','!=','Resolved')->get();
        
        $vehicleDefectStatus = $defect->vehicle->status;


        $vehicleDisplay = request()->get('vehicleDisplay');


        $defectStatusRecord = Defect::find($id);
        $defectId = $defectStatusRecord->id;
        $dectVehicleId = $defectStatusRecord->vehicle_id; 
       
        $defectstatus = [
                ['value'=>'Reported', 'text'=>'Reported'],
                ['value'=>'Acknowledged', 'text'=>'Acknowledged'],
                ['value'=>'Allocated', 'text'=>'Allocated'],
                ['value'=>'Under repair', 'text'=>'Under repair'],
                ['value'=>'Repair rejected', 'text'=>'Repair rejected'],
                ['value'=>'Discharged', 'text'=>'Discharged'],
                ['value'=>'Resolved', 'text'=>'Resolved']
            ];

        $roadsideAssistance = [
            ['value'=>'No','text'=>'No'],
            ['value'=>'Yes','text'=>'Yes']
            ];

        if(Auth::user()->isWorkshopManager()){
            $defectstatus = [
                    ['value'=>'Allocated', 'text'=>'Allocated'],
                    ['value'=>'Under repair', 'text'=>'Under repair'],
                    ['value'=>'Repair rejected', 'text'=>'Repair rejected'],
                    ['value'=>'Discharged', 'text'=>'Discharged']
                ];            
        }

        
        $vorDay = 0;
        $vorCostPerDay = 0;
        $vorOpportunityCostPerDay = 0;
 
        if($vehicleVORLogData) {
            $vorDay = $vehicleVORLogData->vorDuration ? $vehicleVORLogData->vorDuration : 0;
            $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
            $fleetCostJson = $fleetCost->value;
            $fleetCostData = json_decode($fleetCostJson, true);
            if(isset($fleetCostData['vor_opportunity_cost_per_day'])){
                $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;

            }       
            $vorCostPerDay = $vorDay * $vorOpportunityCostPerDay; 
        }
        $vehicleDisplay = request()->get('vehicleDisplay');

        $vehicleDefectStatusCount = Defect::where('vehicle_id',$defect->vehicle_id)->where('status','!=','Resolved')->count();

        JavaScript::put([
            'workshops' => $workshops,
            'defectstatus' => $defectstatus,
            'vehicleDefectRecords' => $vehicleDefectRecords,
            'roadsideAssistance' => $roadsideAssistance,
            'defectId' => $defectId,
            'dectVehicleId' => $dectVehicleId,
            'vehicleDefectStatusCount' => $vehicleDefectStatusCount,
            'vehicleDefectStatus' => $vehicleDefectStatus,
        ]);

        return view('defects.show', compact('defect', 'comments', 'images','vorDuration','workshops','defectstatus','roadsideAssistance', 'vehicleDefectRecords','vorDay','vorOpportunityCostPerDay','vorCostPerDay','vehicleDisplay'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {      
        JavaScript::put([
            'defect' => ['edit' => 'enabled']
        ]);
        
        $defect = Defect::with('vehicle.type', 'defectMaster', 'creator', 'check','updater')
        ->whereHas('vehicle', function($q)
        {
            // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
            $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
        })
        ->where('id', $id)
        ->first();

        if(!$defect || !Auth::user()->isHavingRegionAccess($defect->vehicle->vehicle_region_id)) {
            return redirect('/defects');
        }

        if ($defect->creator == null) {
            $defect->creator = User::withDisabled()->where('id', $defect->created_by)->first();
        }
        if ($defect->updater == null) {
            $defect->updater = User::withDisabled()->where('id', $defect->updated_by)->first();
        }

        if(Auth::user()->isUserInformationOnly() && $defect->creator->id != Auth::user()->id){
             return redirect('/defects');
        }

        $images = $defect->getMedia();
        $vehicleVORLogData = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->first();

        $vorDuration = 'N/A';
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
        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->orderBy('report_datetime', 'desc')->orderBy('id', 'desc')->get();
        foreach ($comments as $key => $comment) {
            if ($comment->creator == null) {
                $comment->creator = User::withDisabled()->where('id', $comment->created_by)->first();
            }
        }
                
        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
         })->with(['company'])->get();

        $workshops = [];
        $workshopUserArray = [];
        foreach ($workshopusers as $key => $value) {
            $workshopuser = [];
            $workshopuser['value'] = $value->id;
            $workshopuser['text'] = $value->company->name.' ('.$value->first_name.' '.$value->last_name.')';
            // $workshopstring = '{"value":"'.$value->id.'","text":"'.$value->company->name.' ('.$value->first_name.' '.$value->last_name.')"}'; 
            array_push($workshopUserArray, $workshopuser);
         }

        array_multisort(array_column($workshopUserArray, "text"), SORT_ASC, $workshopUserArray);
        foreach ($workshopUserArray as $key => $workshop) {
            $workshopstring = json_encode($workshop); 
            array_push($workshops, $workshopstring);
        }

        $vehicleDefectRecords = Defect::with('vehicle','defectMaster')->where('vehicle_id',$defect->vehicle_id)->where('status','!=','Resolved')->get();

        $roadsideAssistance = [
            ['value'=>'No','text'=>'No'],
            ['value'=>'Yes','text'=>'Yes']
            ];

        $defectstatus = [
                ['value'=>'Reported', 'text'=>'Reported'],
                ['value'=>'Acknowledged', 'text'=>'Acknowledged'],
                ['value'=>'Allocated', 'text'=>'Allocated'],
                ['value'=>'Under repair', 'text'=>'Under repair'],
                ['value'=>'Repair rejected', 'text'=>'Repair rejected'],
                ['value'=>'Discharged', 'text'=>'Discharged'],
                ['value'=>'Resolved', 'text'=>'Resolved']
            ];

        if(Auth::user()->isWorkshopManager()){
            $defectstatus = [
                    ['value'=>'Allocated', 'text'=>'Allocated'],
                    ['value'=>'Under repair', 'text'=>'Under repair'],
                    ['value'=>'Repair rejected', 'text'=>'Repair rejected'],
                    ['value'=>'Discharged', 'text'=>'Discharged']
                ];            
        }

        $vehicleVORDays = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();

        $vorDay = 0;
        $vorCostPerDay = 0;
        $vorOpportunityCostPerDay = 0;
        if($vehicleVORDays) {
            $vorDay = $vehicleVORDays->vorDuration ? $vehicleVORDays->vorDuration : 0;
            $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
            $fleetCostJson = $fleetCost->value;
            $fleetCostData = json_decode($fleetCostJson, true);
            if(isset($fleetCostData['vor_opportunity_cost_per_day'])){
                $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;

            }        
            $vorCostPerDay = $vorDay * $vorOpportunityCostPerDay;
        }

        $vehicleDefectStatusCount = Defect::where('vehicle_id',$defect->vehicle_id)->where('status','!=','Resolved')->count();
        $defectId = $defect->id;
        $dectVehicleId = $defect->vehicle_id;
        JavaScript::put([
            'workshops' => $workshops,
            'defectstatus' => $defectstatus,
            'vehicleDefectRecords' => $vehicleDefectRecords,
            'roadsideAssistance' => $roadsideAssistance,
            'vehicleDefectStatusCount' => $vehicleDefectStatusCount,
            'defectId' => $defectId,
            'dectVehicleId' => $dectVehicleId,
        ]);        
        return view('defects.show', compact('defect', 'comments', 'images','vorDuration', 'workshops','roadsideAssistance' ,'vehicleDefectRecords', 'vorDay','vorOpportunityCostPerDay','vorCostPerDay'));
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
        //
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDuplicate($id)
    {
        $defect = Defect::find($id);
        $vehicle_id = $defect->vehicle_id;
        $defect_master_id = $defect->defect_master_id;
        if(Defect::where('id', $id)->delete()) {
            $defectlist = Defect::where(['vehicle_id'=>$vehicle_id,'defect_master_id'=>$defect_master_id])->whereNotIn('status',['Resolved'])->get();
            //Defect::where('id',$id)->update(['duplicate_flag'=>false]);//reset duplicate flag for deleted defect
            if ($defectlist->count() == 1) {
                $duplicateDefectId = $defectlist->first()->id;
                Defect::where('id',$duplicateDefectId)->update(['duplicate_flag'=>false]);
            }
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('defects');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeComment(StoreDefectHistoryRequest $request)
    {   
        $defectHistory = new DefectHistory();
        $defectHistory->defect_id = $request->defect_id;
        $defectHistory->comments = $request->comments;
        $defectHistory->created_by = Auth::id();
        $defectHistory->updated_by = Auth::id();
        $defectHistory->report_datetime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        
        if($defectHistory->save()){
            if (!empty($request->file())) {
                $fileName = $request->file('attachment')->getClientOriginalName();
                $customFileName = preg_replace('/\s+/', '_', $fileName);                
                if(!empty($request->file_input_name)) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $customFileName = $request->file_input_name . "." . $ext;
                }          
                $defectHistoryMedia = DefectHistory::findOrFail($defectHistory->id);
                $fileToSave= $request->file('attachment')->getRealPath();
                $defectHistoryMedia->addMedia($fileToSave)
                                    ->setFileName($customFileName)
                                    ->withCustomProperties(['mime-type' => $request->file('attachment')->getMimeType()])
                                    ->toCollectionOnDisk('defect_history', 'S3_uploads');
            }
            
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        return redirect('defects/'.$request->defect_id);
    }
    public function destroyComment($id) {
        if(DefectHistory::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect()->back();
    }
    public function updateComment(Request $request) {
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $comment = DefectHistory::find($id);
        $comment->comments = $value;
        $comment->updated_by = Auth::id();
        $comment->save();
    }

    public function updateEstimatedDefectCost(Request $request){
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');
        
        $estimatedCost = Defect::find($id);
        $estimatedCost->estimated_defect_cost_value = $value;
        $estimatedCost->updated_by = Auth::id();
        $estimatedCost->save();
    }

    public function updateActualDefectCost(Request $request){
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $estimatedCost = Defect::find($id);
        $estimatedCost->actual_defect_cost_value = $value;
        $estimatedCost->updated_by = Auth::id();
        $estimatedCost->save();
    }    

    public function updateDetails(Request $request) {
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');
        // $defect = null;
        // $vehicle = null;
        $commentValue = Input::get('commentValue');
        if ($field == 'defect_roadside_assistance') {
            $defect = Defect::find($id);
            $defect->roadside_assistance = $value;
            $defect->save();
        }
        if ($field == 'defect_status') {
            $defect = Defect::find($id);
            $defect->status = $value;
            if($value == 'Repair rejected') {
                $defect->workshop = NULL;
            } else {
                if($defect->rejectreason) {
                    $defect->rejectreason = NULL;
                }
            }
            if($value == "Resolved") {
                $defect->resolved_datetime = Carbon::now();
            } else {
                $defect->resolved_datetime = NULL;
            }
            
            $defect->updated_by = Auth::id();
            if($defect->save()){
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set defect status to "'.$value.'" and added comment';
                $defectHistory->defect_status_comment = $commentValue;
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();

                $defectEmailNotification = User::with('regions')->whereHas('roles', function ($query) {
                                                $query->where('name', '=', 'Defect email notifications');
                                            })->get();
                
                $defectEmail = [];
                $defectFirstName = [];
                $Roles = Role::where('name','Defect email notifications')->first();
                $link = url('defects', [$defect->id]);
                $settings = DB::table('settings')->where('key','defect_email_notification')->first();

                if($settings->key == 'defect_email_notification' && $settings->value == 1) {
                    if ($value == 'Reported') {
                        $vehicleRegistrationNumber = Defect::where('status','Reported')->where('id',$defect->id)
                                                            ->with('vehicle')->first()->toArray();
                        $registration = $vehicleRegistrationNumber['vehicle']['registration'];
                        $vehicleRegionId = $vehicleRegistrationNumber['vehicle']['vehicle_region_id'];

                        $defectEmailNotification->each(function ($item, $key) use(&$defectEmail, &$defectFirstName, &$link, &$registration, &$vehicleRegionId) {   
                            $userRegionsIds = $item->regions->lists('id')->toArray();
                            if (count($userRegionsIds) > 0 && in_array($vehicleRegionId, $userRegionsIds)) {
                                if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                                    Mail::queue('emails.defect_reported', ['userName' => $item->first_name, 'emailAddress' => $item->email, 'link' => $link, 'registration' => $registration], function ($message) use ($item, &$link, $registration) {
                                        $message->to($item->email, $item->first_name, $link, $registration);

                                        $message->subject('fleetmastr - defect notification '.$registration);
                                    });
                                }
                            }
                        });   
                    } else {
                        $vehicleRegistrationNumber = Defect::where('status','!=','Reported')->where('id',$defect->id)->with('vehicle')->first()->toArray();
                        $defectsStatus = $vehicleRegistrationNumber['status'];
                        $registration = $vehicleRegistrationNumber['vehicle']['registration'];
                        $vehicleRegionId = $vehicleRegistrationNumber['vehicle']['vehicle_region_id'];

                        $defectEmailNotification->each(function ($item, $key) use(&$defectEmail, &$defectFirstName, &$link, &$registration, &$vehicleRegionId, &$defectsStatus) {
                            $userRegionsIds = $item->regions->lists('id')->toArray();
                            if (count($userRegionsIds) > 0 && in_array($vehicleRegionId, $userRegionsIds)) {
                                if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                                    Mail::queue('emails.defect_status_change', ['userName' => $item->first_name, 'emailAddress' => $item->email, 'link' => $link, 'registration' => $registration,'defectsStatus' => $defectsStatus], function ($message) use ($item, &$link, $registration) {
                                        $message->to($item->email, $item->first_name, $link, $registration);
                                        $message->subject('fleetmastr - defect notification '.$registration);
                                    });
                                }
                            }
                        });                
                    }
                }

                if($value == 'Discharged') {
                    $vehicle = Vehicle::find($defect->vehicle_id);
                    $notification = new Notification();
                    $notification->message = 'Defect status for ' .$vehicle->registration.  ' was changed to Discharged';
                    $notification->save();

                    $superAdminUsers = User::with('regions')
                                            ->whereHas('roles', function ($query) {
                                                $query->where('name', '=', 'Super admin');
                                            })->select('id')->get();

                    foreach($superAdminUsers as $user) {
                        $userRegionsIds = $user->regions->lists('id')->toArray();
                        if (count($userRegionsIds) > 0 && in_array($vehicle->vehicle_region_id, $userRegionsIds)) {
                            $userNotification = new UserNotification();
                            $userNotification->user_id = $user->id;
                            $userNotification->defect_id = $defect->id;
                            $userNotification->notification_id = $notification->id;
                            $userNotification->save();
                        }
                    }
                }
            }
        }

        if ($field == 'workshop') {
            $defect = Defect::find($id);
            $companyIdToSearch = $defect->workshop;
            if($value == '') {
                $defect->workshop = null;
            } else {
                $defect->workshop = $value;
                $companyIdToSearch = $value;
            }

            $defect->updated_by = Auth::id();
            if($defect->save()){
                $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
                })->with(['company'])->get();

                $workshopuser = "";

                foreach ($workshopusers as $key => $wuser) {
                    if ($wuser->id == $companyIdToSearch) {
                        $workshopuser = $wuser;
                    }
                }

                $userName = $workshopuser->first_name;
                $emailAddress = $workshopuser->email;
                $company = Company::findOrFail($workshopuser->company_id);

                if ($value != '') {                    
                    $link = url('defects', [$defect->id]);
                    Mail::queue('emails.workshop_allocation', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $link) {
                        $message->to($emailAddress);
                        $message->subject('fleetmastr - new defect requiring resolution');
                    });                    
                }

                $userFullName = $workshopuser->first_name.' '.$workshopuser->last_name;
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                if ($value != '') {
                    $defectHistory->comments = 'allocated the defect to  "'.$company->name.' ('.$userFullName.')"';
                } else {
                    $defectHistory->comments = 'un-allocated the defect from  "'.$company->name.' ('.$userFullName.')"';
                }
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();
            }
        }
        if ($field == 'reject_reason') {
            $defect = Defect::find($id);
            if($value == '') {
                $defect->rejectreason = null;
            } else {
                $defect->rejectreason = $value;
            }
            $defect->updated_by = Auth::id();
            if($defect->save()){
                /*$defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set rejection reason to "'.$value.'"';
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();*/
            }
        }
        if ($field == 'vehicle_status') {
            $vehicle = Vehicle::withTrashed()->find($id);
            $vehicle->status = $value;
            $vehicle->updated_by = Auth::id();
            $vehicle->save();
        }
        if ($field == 'defect_cost') {
            $defect = Defect::find($id);
            if($value == '') {
                $defect->cost = null;    
            } else {
                $defect->cost = $value;
            }            
            $defect->updated_by = Auth::id();
            if($defect->save()){
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set defect cost to £ '.$defect->present()->formattedCost();
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();
            }
        }
        if ($field == 'invoice_date') {
            $defect = Defect::find($id);
            if($value == '') {
                $defect->invoice_date = null;    
            } else {
                $defect->invoice_date = $value;
            }            
            $defect->updated_by = Auth::id();
            if($defect->save()){
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set defect invoice date to "'.date("d M Y", strtotime($value)).'"';
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();
            }
        }
        if ($field == 'invoice_number') {
            $defect = Defect::find($id);
            if($value == '') {
                $defect->invoice_number = null;
            } else {
                $defect->invoice_number = $value;
            }
            $defect->updated_by = Auth::id();
            if($defect->save()){
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set defect invoice number to "'.$value.'"';
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();
            }
        }
        if ($field == 'defect_completion') {
            $defect = Defect::find($id);
            if($value == '') {
                $defect->est_completion_date = null;    
            } else {
                $defect->est_completion_date = $value;
            }
            $defect->updated_by = Auth::id();
            if($defect->save()){
                $defectHistory = new DefectHistory();
                $defectHistory->defect_id = $defect->id;
                $defectHistory->type = "system";
                $defectHistory->comments = 'set estimated completion date to "' . date("d M Y", strtotime($value)) . '"';
                $defectHistory->created_by = Auth::id();
                $defectHistory->updated_by = Auth::id();
                $defectHistory->save();
            }
        }
        // $id = $defect ? $defect->vehicle_id : ($vehicle ? $vehicle->id : null);
        // if($id) {
        //     $payload = [
        //         'body' => config('config-variables.pushNotification.messages.defect_updated'),
        //         'vehicle_id' => $id
        //     ];
        //     event(new PushNotification($id));
        // }
        event(new PushNotification(config('config-variables.pushNotification.messages.defect_updated')));
    }
    public function downloadMedia($id) {      
        $media = Media::findOrFail($id);      
        $file_name = $media->file_name;
        $file_url = getPresignedUrl($media);
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"".$file_name."\""); 
        readfile($file_url);
        // exit;
    }
    /**
     * Export pdf
     **/
    public function exportPdf($id) {
        $defect = Defect::with('vehicle.type', 'check','creator','updater')
            ->whereHas('vehicle', function($q)
            {
                // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
            })
            ->findOrFail($id);
        
        $images = $defect->getMedia();
        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->orderBy('report_datetime', 'desc')->orderBy('id', 'desc')->get();
        $vehicleVORLogData = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->first();
        $vorDuration = 'N/A';
        if(!empty($vehicleVORLogData)){
            $now = Carbon::now();
            $vorDuration = ($vehicleVORLogData->dt_off_road->diff($now)->days < 1)? 'Today': $vehicleVORLogData->dt_off_road->diff($now)->days." days";
        }

        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
         })->with(['company'])->get();
        $workshops = [];
        foreach ($workshopusers as $key => $value) {
            $workshopstring = '{"value":"'.$value->id.'","text":"'.$value->company->name.' ('.$value->first_name.' '.$value->last_name.')"}'; 
            array_push($workshops, $workshopstring);
        }

        $vehicleVORDays = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();

        $vorDay = 0;
        $vorCostPerDay = 0;
        $vorOpportunityCostPerDay = 0;
        if($vehicleVORDays) {
            $vorDay = $vehicleVORDays->vorDuration ? $vehicleVORDays->vorDuration : 0;
            $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
            $fleetCostJson = $fleetCost->value;
            $fleetCostData = json_decode($fleetCostJson, true);
            if(isset($fleetCostData['vor_opportunity_cost_per_day'])){
                $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;

            }        
            $vorCostPerDay = $vorDay * $vorOpportunityCostPerDay;
        }

        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);
       
        $pdf = PDF::loadView('pdf.defectHistoryExport', array('defect' => $defect, 'comments' => $comments, 'images' => $images, 'vorDuration' => $vorDuration, 'workshops' => $workshops, 'vorCostPerDay' => $vorCostPerDay, 'vorDay' => $vorDay))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
           // ->setOption('header-right', 'Page [page] of [toPage]')
           // ->setOption('header-left', $date->format('H:i:s d M Y'))
            ->setOrientation('portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));
        /*if (get_brand_setting('brand_product_name', false)) {
            $pdf->setOption('header-center', get_brand_setting('brand_product_name'));
        }*/

        // $filename = 'Defectsummary' . $defect->id . '_' . $defect->vehicle->registration . '_' . $defect->report_datetime->format('j') . '_' . $defect->report_datetime->format('M') . '_' . $defect->report_datetime->format('Y') . '.pdf';
        $filename = $defect->vehicle->registration . '_' . 'defecthistory' . $id . '_' . $defect->report_datetime->format('dmY') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportDefectNote($id) {
        $defect = Defect::with('creator', 'vehicle.type', 'defectMaster', 'check','updater')
            ->whereHas('vehicle', function($q)
            {
                // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
            })
            ->findOrFail($id);

        $defectMasterData = DefectMaster::select('page_title')->distinct()->where('order', '>', 0)->orderBy('order')->get();
        $images = $defect->getMedia();
        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->orderBy('report_datetime', 'desc')->orderBy('id', 'desc')->get();
        $vehicleVORLogData = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->first();

        $vorDuration = 'N/A';
        if(!empty($vehicleVORLogData)){
            $now = Carbon::now();
            $vorDuration = ($vehicleVORLogData->dt_off_road->diff($now)->days < 1)? 'Today': $vehicleVORLogData->dt_off_road->diff($now)->days." days";
        }
        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);

        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshop manager');
         })->with(['company'])->get();
        $workshops = [];
        foreach ($workshopusers as $key => $value) {
            $workshopstring = '{"value":"'.$value->id.'","text":"'.$value->company->name.' ('.$value->first_name.' '.$value->last_name.')"}'; 
            array_push($workshops, $workshopstring);
        }

        $vorDay = 0;
        $vorCostPerDay = 0;
        $vorOpportunityCostPerDay = 0;

        $vehicleVORDays = VehicleVORLog::where(['vehicle_id'=>$defect->vehicle->id, 'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();

        if($vehicleVORDays) {
            $vorDay = $vehicleVORDays->vorDuration ? $vehicleVORDays->vorDuration : 0;
            $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
            $fleetCostJson = $fleetCost->value;
            $fleetCostData = json_decode($fleetCostJson, true);
            if(isset($fleetCostData['vor_opportunity_cost_per_day'])) {
                $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;
            }
            $vorCostPerDay = $vorDay * $vorOpportunityCostPerDay;          
        }

        $pdf = PDF::loadView('pdf.defectNoteExport', array('defect' => $defect, 'comments' => $comments, 'images' => $images, 'defectMasterData' => $defectMasterData, 'vorDuration' => $vorDuration, 'workshops' => $workshops, 'vorCostPerDay' => $vorCostPerDay, 'vorDay' => $vorDay))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            //->setOption('header-right', 'Page [page] of [toPage]')
            //->setOption('header-left', $date->format('H:i:s d M Y'))
            ->setOrientation('portrait')
            ->setOption('enable-forms', true)
            // ->setOption('user-style-sheet', url().'/css/pdf.css');
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));        
       /* if (get_brand_setting('brand_product_name', false)) {
            $pdf->setOption('header-center', get_brand_setting('brand_product_name'));
        }*/

        // $filename = 'Defectnote' . $defect->id . '_' . $defect->vehicle->registration . '_' . $defect->report_datetime->format('j') . '_' . $defect->report_datetime->format('M') . '_' . $defect->report_datetime->format('Y') . '.pdf';
        $filename = $defect->vehicle->registration . '_' . 'defect' . $id . '_' . $defect->report_datetime->format('dmY') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Word
     **/
    public function exportWord($id) {
        error_reporting(E_ALL ^ E_STRICT);
        
        $defect = Defect::with('vehicle.type')
            ->whereHas('vehicle', function($q)
            {
                // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
            })
            ->findOrFail($id);

        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->get(); 

        $phpWord = new \PhpOffice\PhpWord\PhpWord();    
        $document = $phpWord->loadTemplate(public_path('wordTemplate/defectHistoryDetails.docx'));

        $document->setValue('date', htmlspecialchars($defect->report_datetime, ENT_COMPAT, 'UTF-8'));
        $document->setValue('registration', htmlspecialchars($defect->vehicle->registration, ENT_COMPAT, 'UTF-8'));
        $document->setValue('manufacturer', htmlspecialchars($defect->vehicle->type->manufacturer, ENT_COMPAT, 'UTF-8'));
        $document->setValue('model', htmlspecialchars($defect->vehicle->type->model, ENT_COMPAT, 'UTF-8'));
        $document->setValue('status', htmlspecialchars($defect->status, ENT_COMPAT, 'UTF-8'));
        $document->setValue('odometer', htmlspecialchars($defect->vehicle->last_odometer_reading . ' ' . $defect->vehicle->type->odometer_setting, ENT_COMPAT, 'UTF-8'));
        $document->setValue('RESULT', htmlspecialchars($defect->status, ENT_COMPAT, 'UTF-8'));

        $i = 1;
        $document->cloneRow('comment', $comments->count());
        foreach($comments as $comment){
            $document->setValue('comment#'.$i, htmlspecialchars($comment->comments, ENT_COMPAT, 'UTF-8'));
            $document->setValue('userEmail#'.$i, htmlspecialchars($comment->creator->email, ENT_COMPAT, 'UTF-8'));
            $document->setValue('postedAt#'.$i, htmlspecialchars($comment->report_datetime, ENT_COMPAT, 'UTF-8'));
            if ($comment->created_at != $comment->updated_at) {
                $document->setValue('updatedAt#'.$i,'Updated At:'.$comment->updated_at);
            }

            if (!empty($comment->getMedia())) {
                foreach ($comment->getMedia() as $media) {
                   $attachment = $media->name;
                }
                $document->setValue('attachment#'.$i, 'Attachment: '.$attachment);
            }
                
        }
        $temp_file = base_path('storage/Defect Details.docx');

        $document->saveAs($temp_file);  
        return response()->download($temp_file);  
    }

    /**
     * Get defect comments
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getDefectComments($id)
    {
        $comments = DefectHistory::with('creator', 'updater')->where('defect_id', $id)->orderBy('report_datetime', 'desc')->orderBy('id', 'desc')->get();

        $defectCommentsHtml = view('defects.defect_comments', ['comments' => $comments])->render();
        return array('defectCommentsHtml' => $defectCommentsHtml);
    }

    public function updateDefectStatus(Request $request){
        $vehicle = Vehicle::where('id',$request->vehicleId)->first();
        $vehicleUpdateStatusUpdate = $vehicle->update(['status' => "Roadworthy"]);
        $vehicleStatus = $vehicle->status;
        return $vehicleStatus;
    }
}
