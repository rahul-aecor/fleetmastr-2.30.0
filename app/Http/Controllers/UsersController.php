<?php

namespace App\Http\Controllers;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use PDF;
use Mail;
use Auth;
use Hash;
use View;
use Input;
use DB;
use JavaScript;
use Validator;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Settings;
use App\Models\UserDivision;
use App\Models\UserRegion;
use App\Models\UserLocation;
use App\Http\Requests;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\PrivateUseLogs;
use App\Models\UserVerification;
use App\Models\ColumnManagements;
use App\Models\VehicleUsageHistory;
use App\Models\VehicleMaintenanceNotification;
use App\Http\Controllers\Controller;
use App\Repositories\UsersRepository;
use App\Custom\Helper\P11dReportHelper;
use App\Repositories\UsersVehicleHistoryRepository;
use App\Repositories\UsersVehiclePrivateUseRepository;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Custom\Facades\GridEncoder;
use Illuminate\Foundation\Auth\ResetsPasswords;


class UsersController extends Controller
{
    use ResetsPasswords;
    public $title= 'User Management';

    public function __construct() {
       //$this->middleware('auth');
       //$this->middleware('can:user.manage');
        View::share ( 'title', $this->title );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rolesOptions = $this->getAllRoles();
        $mobileRolesOptions = $this->getAllMobileRoles();
        $companyOptions = $this->getAllCompanies();
        $lineManagerOptions = $this->getAllLineManagers();
        $privateUseDays = 0;

        $column_management = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','users')
        ->select('data')
        ->first();

        $settings = DB::table('settings')->where('key','defect_email_notification')->first();
        $userNoticationsList = config('config-variables.userNotificationEventTypes');

        // dvsa notification if enabled
        $isDVSAEnabled = Settings::where('key', 'is_dvsa_enabled')->first();
        if ($isDVSAEnabled && $isDVSAEnabled->value == 1) {
            $userNoticationsList['DVSA earned recognition:'] = ['dvsa_maintenance_and_safety_report' => 'DVSA maintenance and safety report'];
        }

        $userRepository = new UsersRepository();
        $resultUser = $userRepository->getAllLinkedData();
        $resultVehicle = (new UserService())->getAllVehicleLinkedData();
        $divisionLists = UserDivision::lists('name', 'id')->toArray();
        $regionLists = UserRegion::lists('name', 'id')->toArray();
        $locationLists = UserLocation::lists('name', 'id')->toArray();
        $companyListArray = array();
        foreach ($companyOptions as $key => $value) {
            array_push($companyListArray, ['id'=>$key, 'text'=>$value]);
        }

        $lineManagerOptionsArray = array();
        foreach ($lineManagerOptions as $key => $value) {
            array_push($lineManagerOptionsArray, ['id'=>$key, 'text'=>$value]);
        }

        $userLastName = array();
        $users = User::all();
        $userLastNameData = User::withTrashed()->orWhere('is_disabled',1)->select('last_name as id', 'last_name as text')->get();
        foreach ($users as $key => $value) {
            $userLastName[$key]['id'] = $value->last_name;
            $userLastName[$key]['text'] = $value->last_name;
        }

        $driverTag = config('config-variables.driver_tag');

        $brandName = env('BRAND_NAME');
        $userRegions = [];
        $userRegionsSearchData = [];
        if($brandName === 'rps') {
            foreach ($resultUser['userRegion'] as $keys => $values) {
                foreach ($values as $key => $value) {
                    $userRegions[$keys][$key]=$value .' ('. $resultUser['userDivisions'][$keys].')';
                    $userRegionsSearchData[$key]=$value .' ('. $resultUser['userDivisions'][$keys].')';
                }
            }
        } else {
            $userRegions = $resultUser['userRegion'];
            $userRegionsSearchData = $resultUser['userOnlyRegions'];
        }
        asort($userRegions);
        asort($userRegionsSearchData);
        asort($resultUser['userBaseLocation']);
        $vehicleLocationArray = [];
        foreach ($resultUser['userBaseLocation'] as $id => $location) {
            if(env('IS_REGION_LOCATION_LINKED_IN_USER')) {
                $k = 0;
                foreach ($location as $key => $value) {
                    unset($resultUser['userBaseLocation'][$id][$key]);
                    // $resultUser['userBaseLocation'][$id][$k] = ['id'=> $key, 'text' => $value];
                    $vehicleLocationArray[$id][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                unset($resultUser['userBaseLocation'][$id]);
                // $resultUser['userBaseLocation'][] = ['id'=> $id, 'text' => $location];
                $vehicleLocationArray[] = ['id'=> $id, 'text' => $location];
            }
        }

        $resultUser['userBaseLocation'] = $vehicleLocationArray;

        $userRegionsForDropdown = $userRegions;
        foreach ($userRegionsForDropdown as $id => $region) {
            if(env('IS_DIVISION_REGION_LINKED_IN_USER')) {
                $k = 0;
                foreach ($region as $key => $value) {
                    unset($userRegionsForDropdown[$id][$key]);
                    $userRegionsForDropdown[$id][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                unset($userRegionsForDropdown[$id]);
                $userRegionsForDropdown[] = ['id'=> $id, 'text' => $region];
            }
        }
        JavaScript::put([
            'column_management' => $column_management,
            'settings' => $settings,
            'userBaseLocation' => $resultUser['userBaseLocation'],
            'userRegion' => $userRegionsForDropdown,
            'companyList' => $companyListArray,
            'lineManagerOptionsList' => $lineManagerOptionsArray,
            'brandName' => env('BRAND_NAME'),
            'isRegionLinkedInUser' => env('IS_DIVISION_REGION_LINKED_IN_USER'),
            'isRegionLinkedInVehicle' => env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'isLocationLinkedInUser' => env('IS_REGION_LOCATION_LINKED_IN_USER'),
            'divisionLists' => $divisionLists,
            'regionLists' => $regionLists,
            'locationLists' => $locationLists,
            'userLastName' => $userLastName,
            'userLastNameData' => $userLastNameData,
        ]);

        return view('users.index')
            ->with('privateUseDays', $privateUseDays)
            ->with('searchEmail', 'test@abc.com')
            ->with('rolesList', $rolesOptions)
            ->with('mobileRolesList', $mobileRolesOptions)
            ->with('companyList', $companyOptions)
            ->with('userNoticationsList',$userNoticationsList)
            ->with('userDivisions', $resultUser['userDivisions'])
            ->with('userRegion', $userRegions)
            ->with('userRegionsSearchData', $userRegionsSearchData)
            ->with('vehicleDivisions', $resultVehicle['vehicleDivisions'])
            ->with('allVehicleDivisionsList', $resultVehicle['vehicleRegions'])
            ->with('lineManagerList', $lineManagerOptions)
            ->with('driverTag', $driverTag);
    }

    public function getAllRoles() {
        //$returnArray = Role::whereNotIn('name', ['Backend manager','App access', 'Workshop manager', 'Defect email notifications','User information only', 'App version handling', 'Manage DVSA configurations']);
        $returnArray = Role::whereNotIn('name', ['Backend manager','App access', 'Workshop manager', 'Defect email notifications','User information only', 'App version handling', 'Manage DVSA configurations']);
            if(setting('is_incident_reports_enabled') != 1) {
                $returnArray->where('name', '!=', 'Incident reports');
            }
            if(setting('is_telematics_enabled') != 1) {
                $returnArray->where('name', '!=', 'Telematics');
            }
            if(setting('is_dvsa_enabled') != 1) {
                $returnArray->where('name', '!=', 'Earned recognition');
            }
            if(setting('is_alertcentre_enabled') != 1) {
                $returnArray->where('name', '!=', 'Alert Centre');
            }
            $returnArray = $returnArray->orderBy('display_order')->lists('name', 'id')->unique()->toArray();

        $value = "Super admin";
        $key = array_search($value, $returnArray);
        unset($returnArray[$key]);
        $superAdmin[$key] = $value;
        $returnArray = $superAdmin + $returnArray;
        return $returnArray;
    }

    public function getAllMobileRoles() {
        $returnArray = Role::whereIn('name', ['App access'])->orderBy('display_order')->lists('name', 'id')->unique()->toArray();
        return $returnArray;
    }

    public function getAllCompanies() {
        $allOtherCompanies = Company::whereNotIn('name',['Aecor','Other'])->where('user_type', 'Other')->orderBy('name')->lists('name', 'id')->unique()->toArray();
        $lastTwo = Company::whereIn('name',['Aecor','Other'])->lists('name', 'id')->unique()->toArray();
        return ['' => ''] + $allOtherCompanies + $lastTwo;
    }

    public function getAllCompaniesJson() {
        $allOtherCompanies = Company::whereNotIn('name',['Aecor','Other'])->where('user_type', 'Other')->orderBy('name')->lists('name', 'id')->unique()->toArray();
        $lastTwo = Company::whereIn('name',['Aecor','Other'])->lists('name', 'id')->unique()->toArray();
        $returnJsonString = array();
        foreach ($allOtherCompanies as $key => $value) {
            array_push($returnJsonString, ['id'=>$key, 'text'=>$value]);
        }
        foreach ($lastTwo as $key => $value) {
            array_push($returnJsonString, ['id'=>$key, 'text'=>$value]);
        }

        return $returnJsonString;
    }

    public function getAllLineManagers($id = null) {
        if (!is_null($id) && !empty($id)) {
            return ['' => ''] + User::select(
                \DB::raw("CONCAT(first_name,' ', last_name) AS full_name, id")
            )->where('id','!=',$id)->whereIn('workshops_user_flag',[0,2])->orderBy('first_name')->lists('full_name', 'id')->toArray();
        }
        return ['' => ''] + User::select(
            \DB::raw("CONCAT(first_name,' ', last_name) AS full_name, id")
        )->whereIn('workshops_user_flag',[0,2])->orderBy('first_name')->lists('full_name', 'id')->toArray();
    }

    public function getLineManagerData($id) {
        $user = User::with('roles')->findOrFail($id);
        return $user;
    }

    public function anyData(Request $request) {
       return GridEncoder::encodeRequestedData(new UsersRepository($request->all()), Input::all());
    }

    public function checkEmail(Request $request) {
        if ($request->email !== null && !empty($request->email)) {
            if ($request->id) {
                $user = DB::table('users')->where('email', $request->email)->where('id', '!=', $request->id)->first();
            } else {
                $user = DB::table('users')->where('email', $request->email)->first();
            }
            if ($user) {
                return "false";
            }
        }
        return "true";
    }

    public function checkUsernameAvailability(Request $request) {
        if ($request->username !== null && !empty($request->username)) {
            $user = DB::table('users')->where('username', $request->username)->first();
        }
        if ($request->id && $user) {
            return "true";
        }
        if ($user) {
            if($request->sendusername){
                $uname = strtolower($request->username);
                //$userRows  = User::whereRaw("username REGEXP '^{$uname}(-[0-9]*)?$'")->get();
                $userRows  =  DB::table('users')->whereRaw("username REGEXP '^{$uname}([0-9]*)?$'")->get();
                $countUser = count($userRows);

                $newUsername = ($countUser > 0) ? $uname.$countUser : $uname;
                if ($request->email !== null && !empty($request->email && $request->email !== "")) {
                    if (filter_var($request->email, FILTER_VALIDATE_EMAIL)){
                        $useremail = DB::table('users')->where('email', $request->email)->first();
                        if ($useremail) {
                            $returndata = array('username'=>$newUsername,'available'=>'false');
                        }else{
                            $returndata = array('username'=>$request->email,'available'=>'false');
                        }
                    }
                }else{
                    $returndata = array('username'=>$newUsername,'available'=>'false');
                }
                return $returndata;
            }
            return "false";
        } else {
            if ($request->email !== null && !empty($request->email && $request->email !== "")) {
                if (filter_var($request->email, FILTER_VALIDATE_EMAIL)){
                    $useremail = User::where('email', $request->email)->first();
                    if ($useremail) {
                        $returndata = array('username'=>$useremail->email,'available'=>'true');
                    }else{
                        $returndata = array('username'=>$request->email,'available'=>'true');
                    }
                    return $returndata;
                }
            }
        }
        return "true";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addCompany(StoreCompanyRequest $request)
    {
        $company = new Company();
        $company->name = $request->name;
        $companyNameLength = strlen($request->name);

        if($companyNameLength >= 3) {
            $company->abbreviation = substr($request->name, 0, 3);
        } else {
            $company->abbreviation = $request->name;
        }
        $company->user_type = 'Other';
        if($company->save()){
            return $this->getAllCompaniesJson();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePrivateUseData(Request $request)
    {
        $queryStartDate = Carbon::createFromFormat('d M Y', $request->start_date)->toDateString();
        $queryEndDate = !empty($request->end_date) ? Carbon::createFromFormat('d M Y', $request->end_date)->toDateString() : Carbon::now()->toDateString();
        $vehiclehistory1 = VehicleUsageHistory::where(['user_id'=>$request->user_id, 'vehicle_id'=>$request->vehicle_id])
        ->whereDate('from_date','<=',$queryEndDate)
        ->whereDate('from_date','<=',$queryStartDate)
        ->where('to_date',NULL)
        ->get();
        if (count($vehiclehistory1) == 0) {
            $vehiclehistory = VehicleUsageHistory::where(['user_id'=>$request->user_id, 'vehicle_id'=>$request->vehicle_id])
            ->whereDate('from_date','<=',$queryEndDate)
            ->whereDate('from_date','<=',$queryStartDate)
            ->whereDate('to_date','>=',$queryStartDate)
            ->whereDate('to_date','>=',$queryEndDate)
            ->get();
            if (count($vehiclehistory) == 0) {
                return 'Invalid Dates';
            }
        }

        $private_use_log = new PrivateUseLogs();
        $p11dReportHelper = new P11dReportHelper();
        $private_use_log->user_id = $request->user_id;
        $private_use_log->vehicle_id = $request->vehicle_id;
        $private_use_log->tax_year = $p11dReportHelper->calcTaxYear();
        $private_use_log->start_date = $request->start_date;
        $private_use_log->end_date = (!empty($request->end_date))?$request->end_date:NULL;
        $private_use_log->save();

        return $p11dReportHelper->calcUserPrivateUseDays($request->user_id) ;
    }
   /**
     * update a created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePrivateUseData(Request $request)
    {
        $queryStartDate = Carbon::createFromFormat('d M Y', $request->start_date)->toDateString();
        $queryEndDate = !empty($request->end_date) ? Carbon::createFromFormat('d M Y', $request->end_date)->toDateString() : Carbon::now()->toDateString();
        $vehiclehistory1 = VehicleUsageHistory::where(['user_id'=>$request->user_id, 'vehicle_id'=>$request->vehicle_id])
        ->whereDate('from_date','<=',$queryEndDate)
        ->whereDate('from_date','<=',$queryStartDate)
        ->where('to_date',NULL)
        ->get();
        if (count($vehiclehistory1) == 0) {
            $vehiclehistory = VehicleUsageHistory::where(['user_id'=>$request->user_id, 'vehicle_id'=>$request->vehicle_id])
            ->whereDate('from_date','<=',$queryEndDate)
            ->whereDate('from_date','<=',$queryStartDate)
            ->whereDate('to_date','>=',$queryStartDate)
            ->whereDate('to_date','>=',$queryEndDate)
            ->get();
            if (count($vehiclehistory) == 0) {
                return 'Invalid Dates';
            }
        }

        $id = $request->id;
        $private_use_log = PrivateUseLogs::where('id',$id)->with('user')->first();
        $p11dReportHelper = new P11dReportHelper();
        $private_use_log->vehicle_id = $request->vehicle_id;
        $private_use_log->tax_year = $p11dReportHelper->calcTaxYear();
        $private_use_log->start_date = $request->start_date;
        $private_use_log->end_date = (!empty($request->end_date))?$request->end_date:NULL;
        $private_use_log->save();
        return $p11dReportHelper->calcUserPrivateUseDays($request->user_id) ;
    }

    public function editPrivateUseData(Request $request)
    {
        $id = $request['id'];
        $private_use_log = PrivateUseLogs::where('id',$id)->with('user')->first();
        $p11dReportHelper = new P11dReportHelper();
        $totalPrivateUseDays = $p11dReportHelper->calcUserPrivateUseDays($id) ;
        $vehicleRegistrations = ['' => 'Select'] + Vehicle::lists('registration', 'id')->unique()->toArray();

        return view('_partials.users.edit_private_use_log', compact('id', 'user', 'totalPrivateUseDays','vehicleRegistrations', 'private_use_log'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->company_id = $request->company_id;
        $user->job_title = $request->job_title;
        $user->mobile = $request->mobile;
        $user->landline = $request->landline;
        $user->driver_tag = $request->driver_tag;
        $user->driver_tag_key = ($request->driver_tag_key != '') ? $request->driver_tag_key : null;
        $user->fuel_card_number = !empty($request->fuel_card_number) ? $request->fuel_card_number : null;
        $user->engineer_id = $request->engineer_id;
        $user->enable_login = $request->enable_login;
        $user->imei = $request->imei;
        $user->line_manager = $request->line_manager;
        $user->field_manager_phone = $request->field_manager_phone;
        $user->fuel_card_personal_use = $request->fuel_card_personal_use == 'on' ? 1 : 0;
        $user->fuel_card_issued = $request->fuel_card_issued == 'on' ? 1 : 0;
        $user->username = $request->username;
        $user->user_division_id = ($request->user_division_id != '') ? $request->user_division_id : null;
        $user->user_region_id = ($request->user_region_id != '') ? $request->user_region_id : null;
        $user->user_locations_id = ($request->user_locations_id != '') ? $request->user_locations_id : null;
        $isEmailProvided = (!empty($request->email))? true : false;
        $user->email = (!empty($request->email))?$request->email:($request->username.'@'.env('BRAND_NAME').'-imastr.com');
        $user->is_lanes_account = 0;
        $token = str_random(30);
        $link = url('users/verification', [$token]);

        $defectEmailNotificationId = Role::where('name','Defect email notifications')->first()->id;

        if(!$isEmailProvided) {
            $user->is_verified = 1;
            $user->is_default_password = 1;
            $user->password = bcrypt(env('DEFAULT_PASSWORD'));
        }

        if($user->save()){
            $roles  = array_filter($request->roles);//array_filter is used to remove empty element in case of bespoke role.

            if($request->newDefectEmailNotification) {
                array_push($roles, $defectEmailNotificationId);
            }
            if($request->roles) {
                $user->roles()->sync($roles);
            }

            if($request->accessible_divisions) {
                $user->divisions()->sync($request->accessible_divisions);
            }

            if($request->accessible_regions) {
                $user->regions()->sync($request->accessible_regions);
            }


            if($request->message_accessible_divisions) {
                $user->messageDivisions()->sync($request->message_accessible_divisions);
            }

            if($request->message_accessible_regions) {
                $user->messageRegions()->sync($request->message_accessible_regions);
            }

            // storing user verification token
            if ($isEmailProvided) {
                $userVerification = new UserVerification();
                $userVerification->user_id = $user->id;
                $userVerification->key = $token;
                $userVerification->save();

                $userName = $user->first_name;
                $emailAddress = $user->email;

                Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $token) {
                    $message->to($emailAddress);
                    $message->subject('fleetmastr - set your account password');
                });

            } else{
                $user->is_verified = 1;
                $user->is_default_password = 1;
                $user->password = bcrypt(env('DEFAULT_PASSWORD'));
                $user->save();
            }

            $eventTypesListGroup = config('config-variables.userNotificationEventTypes');

            // dvsa notification if enabled
            $isDVSAEnabled = Settings::where('key', 'is_dvsa_enabled')->first();
            if ($isDVSAEnabled && $isDVSAEnabled->value == 1) {
                $eventTypesListGroup['DVSA earned recognition:'] = ['dvsa_maintenance_and_safety_report' => 'DVSA maintenance and safety report'];
            }

            foreach ($eventTypesListGroup as $key => $eventTypesList) {
                $index = 0;
                $vehicleMaintenanceNotificationList = [];
                if ($request->event_type) {
                    $event_type_list = $request->event_type;
                    foreach ($eventTypesList as $key => $value) {
                        $vehicleMaintenanceNotificationList[$index]['user_id'] = $user->id;
                        $vehicleMaintenanceNotificationList[$index]['event_type'] = $key;
                        if (in_array($key, $event_type_list)) {
                            $vehicleMaintenanceNotificationList[$index]['is_enabled'] = 1;
                        } else {
                            $vehicleMaintenanceNotificationList[$index]['is_enabled'] = 0;
                        }
                        $index ++;
                    }
                    VehicleMaintenanceNotification::insert($vehicleMaintenanceNotificationList);
                } else {
                    foreach ($eventTypesList as $key => $value) {
                        $vehicleMaintenanceNotificationList[$index]['user_id'] = $user->id;
                        $vehicleMaintenanceNotificationList[$index]['event_type'] = $key;
                        $vehicleMaintenanceNotificationList[$index]['is_enabled'] = 0;
                        $index ++;
                    }
                    VehicleMaintenanceNotification::insert($vehicleMaintenanceNotificationList);
                }
            }            

            flash()->success(config('config-variables.flashMessages.dataSaved'));

        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        if ($user->line_manager == 0) {
            $user->line_manager = NULL;
        }
        $roles = $user->roles()->get();
        $givenRoles = $roles->pluck('id');
        $rolesOptions = $this->getAllRoles();
        $rolesOptionsFlip = array_flip($rolesOptions);
        $messageRoleId = $rolesOptionsFlip['Messaging'];
        $mobileRolesOptions = $this->getAllMobileRoles();
        $companyOptions = $this->getAllCompanies();
        $user->role_id = $givenRoles->all();
        $lineManagerOptions = $this->getAllLineManagers($id);
        $givenRolesArray = $givenRoles->toArray();
        $workshopsManagerId = Role::where('name','Workshop manager')->first()->id;
        $vehicleDefectId = Role::where('name','Vehicle defects')->first()->id;
        $defectEmailNotificationId = Role::where('name','Defect email notifications')->first()->id;
        $settings = DB::table('settings')->where('key','defect_email_notification')->first();
        $p11dReportHelper = new P11dReportHelper();
        $privateUseDays = $p11dReportHelper->calcUserPrivateUseDays($id) ;
        $privateUseDaysPrev = $p11dReportHelper->calcUserPrevYearPrivateUseDays($id) ;

        $appDevice = '';
        $appVersion = '';
        if(!empty($user->user_agent)) {
            $userAgentValue = explode(';',$user->user_agent);
            $userAgentDevice = isset($userAgentValue[1])?$userAgentValue[1]:'N/A';
            $userAgentVersion = isset($userAgentValue[2])?$userAgentValue[2]:'N/A';
            $appDevice = $userAgentDevice;
            $appVersion = $userAgentVersion;
        } else{
            $appDevice = 'N/A ';
            $appVersion = 'N/A';
        }

        if (!str_contains($user->email ,'@')) {
            $user->email = '';
        }

        $userNoticationsListGroup = config('config-variables.userNotificationEventTypes');
        $userNotifications = VehicleMaintenanceNotification::where('user_id','=',$id)->orderBy('event_type')->get();
        $userNotificationsValue = $userNotifications->groupBy('event_type')->toArray();

        // dvsa notification if enabled
        $isDVSAEnabled = Settings::where('key', 'is_dvsa_enabled')->first();
        if ($isDVSAEnabled && $isDVSAEnabled->value == 1) {
            $userNoticationsListGroup['DVSA earned recognition:'] = ['dvsa_maintenance_and_safety_report' => 'DVSA maintenance and safety report'];
        }

        $usersNotificationsArray = array();
        foreach ($userNoticationsListGroup as $notificationKey => $notificationList) {
            $usersNotificationsArray[$notificationKey] = array();
            foreach ($notificationList as $key => $notification) {
                $usersNotificationsArray[$notificationKey][$key]['is_enabled'] = isset($userNotificationsValue[$key]) ? $userNotificationsValue[$key][0]['is_enabled'] : 0 ;
                $usersNotificationsArray[$notificationKey][$key]['eventTypeKey'] = $key;
                $usersNotificationsArray[$notificationKey][$key]['eventTypeEvent'] = $notification;
            }

        }
        $userNoticationsListGroup = $usersNotificationsArray;

        $userDivisions = [];
        $allDivisions = UserDivision::orderBy('name', 'asc')->get()->toArray();
        if(is_array($allDivisions) && !empty($allDivisions)) {
            foreach ($allDivisions as $divisions) {
                // create all divisions lists
                if(isset($divisions['name']) && $divisions['id']) {
                    $userDivisions[$divisions['id']] = $divisions['name'];
                }
            }
        }

        $userRepository = new UsersRepository();
        $resultUser = $userRepository->getAllLinkedData();
        $resultVehicle = (new UserService())->getAllVehicleLinkedData();
        // $resultMessage = (new UserService())->getAllMessageLinkedData();

        $brandName = env('BRAND_NAME');
        $userRegions = [];
        if($brandName === 'rps') {
            foreach ($resultUser['userRegion'] as $keys => $values) {
                foreach ($values as $key => $value) {
                    $userRegions[$keys][$key]=$value .' ('. $resultUser['userDivisions'][$keys].')';
                }
            }
            asort($userRegions);
        } else {
            $userRegions = $resultUser['userRegion'];
        }

        $driverTag = config('config-variables.driver_tag');

        return view('users.edit')
            ->with('user', $user)
            ->with('privateUseDays', $privateUseDays)
            ->with('privateUseDaysPrev', $privateUseDaysPrev)
            ->with('rolesList', $rolesOptions)
            ->with('mobileRolesList', $mobileRolesOptions)
            ->with('givenRolesArray', $givenRolesArray)
            ->with('companyList', $companyOptions)
            ->with('lineManagerList', $lineManagerOptions)
            ->with('workshopsManagerId', $workshopsManagerId)
            ->with('defectEmailNotificationId', $defectEmailNotificationId)
            // ->with('userDivisions', $userDivisions)
            ->with('userDivisions', $resultUser['userDivisions'])
            ->with('userRegion', $userRegions)
            ->with('vehicleDivisions', $resultVehicle['vehicleDivisions'])
            ->with('allVehicleDivisionsList', $resultVehicle['vehicleRegions'])
            ->with('vehicleDefectId',$vehicleDefectId)
            ->with('appDevice',$appDevice)
            ->with('appVersion',$appVersion)
            ->with('userNotifications',$userNoticationsListGroup)
            ->with('driverTag',$driverTag)
            // ->with('messageDivisions', $resultMessage['messageDivisions'])
            // ->with('allMessageDivisionsList', $resultMessage['messageRegions'])
            ->with('messageRoleId', $messageRoleId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->username = $request->username;
        $emaiChanged = ($request->email!==$user->email && $request->email != '')?true:false;
        $user->email = (!empty($request->email))?$request->email:$user->username.'@'.env('BRAND_NAME').'-imastr.com';
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->company_id = $request->company_id;
        $user->job_title = $request->job_title;
        $user->mobile = $request->mobile;
        $user->landline = $request->landline;
        $user->driver_tag = $request->driver_tag;
        $user->driver_tag_key = ($request->driver_tag!='none' && $request->driver_tag_key != '') ? $request->driver_tag_key : null;
        $user->fuel_card_number = !empty($request->fuel_card_number) ? $request->fuel_card_number : null;
        $user->engineer_id = $request->engineer_id;
        $user->enable_login = $request->enable_login;
        $user->user_division_id = ($request->user_division_id != '') ? $request->user_division_id : null;
        $user->user_region_id = ($request->user_region_id != '') ? $request->user_region_id : null;
        $user->user_locations_id = ($request->user_locations_id != '') ? $request->user_locations_id : null;
        $user->imei = $request->imei;
        $user->line_manager = $request->line_manager;
        $user->field_manager_phone = $request->field_manager_phone;
        $user->fuel_card_personal_use = $request->fuel_card_personal_use == 'on' ? 1 : 0;
        $user->fuel_card_issued = $request->fuel_card_issued == 'on' ? 1 : 0;

        $workShopDefectsId = Role::where('name','Vehicle defects')->first()->id;
        $workShopsManagerId = Role::where('name','Workshop manager')->first()->id;
        $defectEmailNotificationId = Role::where('name','Defect email notifications')->first()->id;
        
        if ($request->workshopmanager) {
            if (count($request->roles) == 1 && in_array($workShopDefectsId, $request->roles)) {
                $user->workshops_user_flag = 1;
            } else {
                $user->workshops_user_flag = 2;
            }
        } else {
            $user->workshops_user_flag = 0;
        }
        if ($user->save()) {
            $roles  = array_filter($request->roles);//array_filter is used to remove empty element in case of bespoke role.
            if(!isset($request->accessible_divisions)) {
                $request->accessible_divisions = [];
            }
            $user->divisions()->sync($request->accessible_divisions);

            if(!isset($request->accessible_regions)) {
                $request->accessible_regions = [];
            }
            $user->regions()->sync($request->accessible_regions);


            if(!isset($request->message_accessible_divisions)) {
                $request->message_accessible_divisions = [];
            }
            $user->messageDivisions()->sync($request->message_accessible_divisions);

            if(!isset($request->message_accessible_regions)) {
                $request->message_accessible_regions = [];
            }
            $user->messageRegions()->sync($request->message_accessible_regions);

            if($request->defectEmailNotification) {
                array_push($roles, $defectEmailNotificationId);
            }

            if($request->workshopmanager) {
                array_push($roles, $workShopsManagerId);
            }

            if(count($roles) == 2 && $request->workshopmanager && in_array($workShopDefectsId, $roles)) {
                $removed = array_shift($roles);
            }
            $user->roles()->sync($roles);
            if($emaiChanged){
                $token = str_random(30);
                $link = url('users/verification', [$token]);
                $userVerification = new UserVerification();
                $userVerification->user_id = $user->id;
                $userVerification->key = $token;
                $userVerification->save();

                $userName = $user->first_name;
                $emailAddress = $user->email;

                if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $token) {
                        $message->to($emailAddress);
                        $message->subject('fleetmastr - set your account password');
                    });
                }

                $user->is_verified = 0;
                $user->save();
            }
            
            $eventTypesListGroup = config('config-variables.userNotificationEventTypes');
            $index = 0;
            $vehicleMaintenanceNotificationList = [];

            // dvsa notification if enabled
            $isDVSAEnabled = Settings::where('key', 'is_dvsa_enabled')->first();
            if ($isDVSAEnabled && $isDVSAEnabled->value == 1) {
                $eventTypesListGroup['DVSA earned recognition:'] = ['dvsa_maintenance_and_safety_report' => 'DVSA maintenance and safety report'];
            }

            foreach ($eventTypesListGroup as $groupKey => $eventTypeList) {
                foreach ($eventTypeList as $key => $notification) {
                    $eventTypeValue = 0;
                    if (isset($request->event_type) && in_array($key,$request->event_type)) {
                        $eventTypeValue = 1;
                    }
                    $vehicleNotificationEntry = VehicleMaintenanceNotification::firstOrNew(array('user_id' => $id,'event_type' => $key));
                    $vehicleNotificationEntry->is_enabled = $eventTypeValue;
                    $vehicleNotificationEntry->save();
                }
            }

            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('users');
    }

    public function resetpasswordadmin(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // check if it is a lanes email
        $email = $user->email;
        $email = str_replace('@'.env('BRAND_NAME').'-imastr.com','',$email);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $user->is_verified = 1;
            $user->is_default_password = 1;
            $user->password = bcrypt(env('DEFAULT_PASSWORD'));
            $user->save();
            flash()->success("Password has been set to default password");
            return redirect()->back()->with('status', "Password has been set to default password");
        } else {
            $response = Password::sendResetLink(['email'=>$email], function (Message $message) {
                $message->subject($this->getEmailSubject());
            });

            switch ($response) {
                case Password::RESET_LINK_SENT:
                    flash()->success("An email has been sent to user with a password reset link");
                    return redirect()->back()->with('status', "An email has been sent to user with a password reset link");

                case Password::INVALID_USER:
                    return redirect()->back()->withErrors(['email' => trans($response)]);
            }
        }
    }

    /**
     * Used to update password.
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function changePassword(Request $request)
    {
        $dataToUpdate = $request->all();
        if(Auth::user()->is_lanes_account == 1) {
            $response = ['success' => false, 'message' => 'You are not authorized to change your password.'];
            return json_encode($response);
        }

        if(!isset($dataToUpdate['password']) || trim($dataToUpdate['password']) == '') {
            $response = ['success' => false, 'message' => 'Please provide new password to change your old password.'];
            return json_encode($response);
        }

        if(isset($dataToUpdate['old_password']) && trim($dataToUpdate['old_password']) != '') {
            $user = User::where('email', Auth::user()->email)->orWhere('username',Auth::user()->username)->get();
            if(!isset($user[0])) {
                return $response = ['status' => false, 'message' => 'Invalid email provided.'];
            }else if($user[0]->is_disabled){
                return $response = ['status' => false, 'message' => 'The password connected with your Lanes Group email address cannot be reset on this platform. Please contact support@lanes-i.com for assistance.'];
            }

            if (Hash::check($dataToUpdate['old_password'], $user[0]->password)) {
                $rules = [
                    'password'              => 'required|alpha_num|confirmed',
                    'password_confirmation' => 'required|alpha_num'
                ];

                $messages = [
                    'password.required' => 'New password is required.',
                    'password_confirmation.required' => 'Confirm new password is required.',
                    'password.confirmed' => 'New password and confirm new password do not match.'
                ];

                $validator = Validator::make($request->all(), $rules, $messages);

                if ($validator->fails()) {
                    return $response = ['status' => false, 'message' => $validator->errors()];
                }

                $dataToUpdate['password'] = bcrypt($dataToUpdate['password']);
                unset($dataToUpdate['old_password']);
                unset($dataToUpdate['password_confirmation']);

                if (User::where('email', Auth::user()->email)->orWhere('username',Auth::user()->username)->update($dataToUpdate)) {
                    $response = ['success' => true, 'message' => 'Password has been changed successfully.'];
                } else {
                    $response = ['success' => false, 'message' => 'Password change unsuccessful. Please try later.'];
                }
                return json_encode($response);

            } else {
                $response = ['success' => false, 'message' => 'Current password does not match, Please provide valid current password to update your new password.'];
                return json_encode($response);
            }
        }

        $response = ['success' => false, 'message' => 'Password change unsuccessful. Please try later.'];
        return json_encode($response);
    }

    /**
     * Used To Disable User.
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function anyDisable($id)
    {
        if(Auth::id()==$id) {
            flash()->success(config('config-variables.flashMessages.noDeleteAccess'));
        } else {
            $user=User::find($id);
            $user->is_disabled = 1;
            if ($user->save()) {
                flash()->success(config('config-variables.flashMessages.userDisabled'));
            } else {
                flash()->success(config('config-variables.flashMessages.userNotDisabled'));
            }
        }
        return redirect('users');
    }

    /**
     * Used To Enable User.
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function anyEnable($id)
    {
        $user = User::withDisabled()->where('id', $id)->first();
        $user->is_disabled = 0;
        if ($user->save()) {
            flash()->success(config('config-variables.flashMessages.dataEnabled'));
        } else {
            flash()->success(config('config-variables.flashMessages.dataNotEnabled'));
        }
        return redirect('users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePrivateUseDates(Request $request)
    {
        if (PrivateUseLogs::where('id', $request->id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
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
        if(User::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('users');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'company' => 'max:50',
            'division' => 'max:50',
            'job_title' => 'max:50',
            'region' => 'in:Central,East,South East,South West,West|max:50',
            'base_location' => 'max:50',
            'is_active' => 'boolean',
            'is_lanes_account' => 'boolean',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
    }

    /**
     * Set user password.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setPassword(Request $request, $key)
    {
        $usersPasswords = UserVerification::where(['key'=>$key])->get();
        $message = "";
        $error = false;

        if(count($usersPasswords) == 0) {
            $isUserVerified = UserVerification::withTrashed()->where(['key'=>$key])->get();
            if(count($isUserVerified) > 0) {
                $error=true;
                $message = "You have already set the password.";
                return view('users.set_password', compact('error', 'message'));
            } else {
                $message = "Password reset link is expired.";
                return view('users.set_password', compact('error', 'message'));
            }
        }

        foreach ($usersPasswords as $key => $usersPassword) {
            $timediff = time() - strtotime($usersPassword->created_at);
            if($timediff <= 604800){ //key expires in 7 days
                $users = User::findOrFail($usersPassword->user_id);
                $users->is_verified = 0;
                if ($users->save()) {
                } else {
                    $error=true;
                    $message = "User email could not be verified.";
                }
                return view('users.set_password', compact('usersPasswords', 'message', 'error'));
            }
            else {
                $error=true;
                $message = "Verification key expired.";
                return view('users.set_password', compact('error', 'message'));
            }
        }
    }

    /**
     * Save user password.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function savePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:8',
            ],[
                'confirmed' => 'The passwords do not match'
            ]
        );

        $key = $request->key;
        $password = $request->password;
        $usersPassword = UserVerification::where('key', $key)->first();
        $users = User::where('id', $usersPassword->user_id)->first();
        $users->password = Hash::make($password);
        $users->is_verified = 1;
        $users->is_default_password = 0;
        $users->save();
        $usersPassword->delete();

        if($users->isAppUser()) {
            return redirect('/auth/successreset')->with('message', 'Your password has been set.');
        } else {
            return redirect('/login')->with('message', 'Your password has been set.');
        }
    }

    /**
     * Resend invitation
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation(Request $request, $id)
    {
        $userId = $request->id;
        
        UserVerification::where('user_id', $userId)->forceDelete();
        
        $user = User::find($userId);
        $userName = $user->first_name;
        $emailAddress = $user->email;
        $key = str_random(30);
        $link = url('users/verification', [$key]);

        $userVerification = new UserVerification();
        $userVerification->user_id = $user->id;
        $userVerification->key = $key;
        $userVerification->save();

        if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $key) {
                $message->to($emailAddress);
                $message->subject('fleetmastr - set your account password');
            });
        }
        flash()->success('Invitation has been sent.');

        return redirect('/users');
    }

    public function anyGetEnabledUsers(Request $request)
    {
        $authUser = $request->user();

        if(!($authUser->can('user.manage') || $authUser->can('messaging.manage'))) {
            abort(404);
        }

        $authUserMessageRegions = $authUser->messageRegions->pluck('id');
        $users = User::with('userRegion')
                        ->whereHas('userRegion', function($q) use ($authUserMessageRegions) {
                            $q->whereIn('user_region_id', $authUserMessageRegions);
                        })
                    ->orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }

    public function userVehicleHistory(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        $p11dReportHelper = new P11dReportHelper();
        $totalPrivateUseDays = $p11dReportHelper->calcUserPrivateUseDays($id) ;
        $vehicleRegistrations = ['' => 'Select'] + Vehicle::orderBy('registration')->lists('registration', 'id')->unique()->toArray();        
        $currentUser = Auth::user();
        JavaScript::put([
            'user' => $currentUser->isSuperAdmin()
        ]);
        View::share('title', 'User Vehicle History');

        return view('users.vehicle_history', compact('id', 'user', 'totalPrivateUseDays','vehicleRegistrations'));
    }

    public function getPrivateUseLogs(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        $p11dReportHelper = new P11dReportHelper();
        $totalPrivateUseDays = $p11dReportHelper->calcUserPrivateUseDays($id) ;
        $vehicleRegistrations = Vehicle::lists('registration', 'id')->orderBy('registration')->unique()->toArray();
        $currentUser = Auth::user();
        JavaScript::put([
            'user' => $currentUser->isSuperAdmin()
        ]);
        View::share('title', 'User Vehicle History');

        return view('_partials.users.private_use_log', compact('id', 'user', 'totalPrivateUseDays','vehicleRegistrations'));
    }

    public function getUserVehicleHistoryData(Request $request, $id)
    {
        return GridEncoder::encodeRequestedData(new UsersVehicleHistoryRepository($id), Input::all());
    }
    public function getUserVehiclePrivateUseData(Request $request, $id)
    {
        return GridEncoder::encodeRequestedData(new UsersVehiclePrivateUseRepository($id), Input::all());
    }

    public function updateVehicleHistoryDates(Request $request, $id)
    {
        VehicleUsageHistory::where('id', $id)
          ->update([$request['cellname'] => Carbon::parse($request['value'])->toDateTimeString()]);
    }

    public function exportVehicleHistoryPdf($id)
    {
        $user = User::where('id', $id)->first();
        $vehicleUsageHistory = VehicleUsageHistory::with('vehicle_history')->where('user_id', $id)
                               ->orderBy('id','DESC')->get();

        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);

        $pdf = PDF::loadView('pdf.vehicleHIstoryExport', array('user' => $user, 'vehicleHistory' => $vehicleUsageHistory))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOrientation('portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));

        $filename = $user->first_name . '_' . $user->last_name . '_' . 'vehiclehistory' . '_' . $date->format('dmY') . '.pdf';

        return $pdf->download($filename);
    }

    public function checkIsDallasKeyExist(Request $request)
    {
        $alreadyExistedDallasKeyCount = User::where('driver_tag_key', '=', $request->driver_tag_key)
                                            ->where('id', '!=', $request->id)
                                            ->count();
                                            
        if ($alreadyExistedDallasKeyCount > 0) {
            return "false";
        }

        return "true";
    }

    /**
     * Get user divisions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserDivisions()
    {
        $userDivisions = UserDivision::with('users')->get();
        return response()->json($userDivisions);
    }

    /**
     * Get user regions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserRegions()
    {
        $authUserMessageRegions = Auth::user()->messageRegions->pluck('id');
        $userRegions = UserRegion::with('users')->whereIn('id', $authUserMessageRegions)->get();
        return response()->json($userRegions);
    }
}
