<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Auth;
use View;
use JavaScript;
use Carbon\Carbon;
use App\Http\Requests;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Services\VehicleService;
use App\Services\ZoneService;
use App\Services\TelematicsService;
use App\Models\Zone;
use App\Models\ZoneAlerts;
use App\Models\ZoneAlertSession;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\Check;
use App\Models\Location;
use App\Models\VehicleType;
use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Custom\Facades\GridEncoder;
use Input;
use App\Services\UserService;
use App\Repositories\ZonesRepository;
use App\Repositories\ZoneAlertsRepository;
use App\Repositories\TelematicsJourneysRepository;
use App\Repositories\TelematicsJourneyIncidentsRepository;
use App\Repositories\TelematicsVehiclesRepository;
use App\Repositories\LocationRepository;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Models\ZoneVehicle;
use App\Models\ZoneVehicleType;
use App\Models\ZoneVehicleRegion;
use App\Models\ColumnManagements;
use App\Models\LocationCategory;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Custom\Helper\Common;
use App\Repositories\UsersRepository;
use Illuminate\Support\Collection;
class TelematicsController extends Controller
{
    public $title= 'Telematics';
    protected $obj_user_telematics_journey;
    protected $obj_telematics_journeys;

    public function __construct(VehicleService $vehicleService, TelematicsService $telematicsService) {
        $this->obj_telematics_journeys = new TelematicsJourneys();
        $this->vehicleService = $vehicleService;
        $this->telematicsService = $telematicsService;
        View::share ( 'title', $this->title );
    }
    private function getSpeedLimit($lat,$lon){
        \Log::info('getSpeedLimit ' . $lat . ',' . $lon);
        $url = 'https://router.hereapi.com/v8/routes?transportMode=car&origin='.$lat.','.$lon.'&destination='.$lat.','.$lon.'&apiKey='.env('HERE_API_KEY').'&spans=maxSpeed,names&return=polyline';
        $resp = file_get_contents($url);
        $data = json_decode($resp, true);
    	if(isset($data['routes']) && isset($data['routes'][0]) && isset($data['routes'][0]['sections'])&& isset($data['routes'][0]['sections'][0]) && isset($data['routes'][0]['sections'][0]['spans']) && isset($data['routes'][0]['sections'][0]['spans'][0]) && isset($data['routes'][0]['sections'][0]['spans'][0]['maxSpeed'])){
            	return $data['routes'][0]['sections'][0]['spans'][0]['maxSpeed'];
    	}
    	else{
    		return 0;
    	}

        // return $data['routes'][0]['sections'][0]['spans'][0]['maxSpeed'];
    }
    /**
     * Display the telematics summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $zonesColumnManagement = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','telematicsZones')
        ->select('data')
        ->first();

        $zoneAlertsColumnManagement = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','telematicsZoneAlerts')
        ->select('data')
        ->first();
        
        // $lastnames = [];
        // foreach ($userDetails as $key => $user) {
        //     $lastnames[$index]['id'] =  $user->user->id;
        //     $userTextVisibility = substr($user->user->first_name, 0, 1).' '.$user->user->last_name . ' (' .$user->user->email . ')';
        //     $lastnames[$index]['text'] = $userTextVisibility;
        //     $index++;
        // }

        // $basic = TelematicsJourneys::with(['user', 'vehicle'])->groupBy('vehicle_id')->get();
        $liveUserDetails = [];
        $i = 0;
        // $livelastnames = [];
        // foreach ($basic as $key => $value) {
        //     if ($value->user) {
        //         if ($value->user->id == env('SYSTEM_USER_ID')) {
        //           $userTextVisibility = 'Driver Unknown';
        //         }
        //         else{
        //             $userTextVisibility = substr($value->user->first_name, 0, 1).' '.$value->user->last_name . ' (' .$value->user->email . ')';            
        //         }
        //     	$livelastnames[$i]['id'] =  $value->vehicle->registration;
        //     	$livelastnames[$i]['text'] = $userTextVisibility;
        //     	$i++;
        //     }
        // }
        $userRegions = Auth::user()->regions->lists('id')->toArray();  
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicle_region_id', $userRegions)->where('is_telematics_enabled','1')->get();
        // $userDetails = TelematicsJourneys::with(['user', 'vehicle'])
        //                  ->orderBy('start_time', 'desc')
        //                  ->get()->unique('user_id', 'vehicle_id');

        $index = 0;
        $lastnames = [];
        $userDetails = User::where('job_title', '!=', 'Workshop User')->where('is_disabled', 0)->get();
        foreach ($userDetails as $key => $user) {
          $lastnames[$index]['id'] =  $user->id;
          if ($user->id == env('SYSTEM_USER_ID')) {
              $userTextVisibility = config('config-variables.telematicsSystemUserVisibleName.FULL');
          }
          else{
            $userTextVisibility = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->email . ')';
          }
          $lastnames[$index]['text'] = $userTextVisibility;
          $index++;
        }

        $data=$this->vehicleService->getDataDivRegLoc();
        $regionForSelect = $data['vehicleRegions'];
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $regionForSelect=$this->vehicleService->regionForSelect($data);
        }
        $regionForSelect=collect($regionForSelect);

        $index = 0;
        $regionForFilter = [];
        foreach ($regionForSelect as $key => $value) {
          $regionForFilter[$index]['id'] =  $key;
          $regionForFilter[$index]['text'] = $value;
          $index++;
        }

        $index = 0;
        $incidentTypes = config('config-variables.telematics_incidents_filter');
        $incidentType = [];
        foreach ($incidentTypes as $key => $value) {
          $incidentType[$index]['id'] =  $key;
          $incidentType[$index]['text'] = $value;
          $index++;
        }

        if(!isset($_COOKIE['telematics_ref_tab'])) {
            setcookie('telematics_ref_tab', 'live_tab', time() + (86400 * 30), "/");
            $selectedTab = 'live_tab';
        } else {
            $selectedTab = str_replace("#", "", $_COOKIE['telematics_ref_tab']);
        }
        $columnManagement = ColumnManagements::where('user_id', $request->user()->id)
                                                ->where('section', 'journeys')
                                                ->select('data')
                                                ->first();

        // $vehiclesHavingTelematicsData = TelematicsJourneys::distinct()->lists('vehicle_id')->toArray();
        
        $vehicleTypesBaseRegionQuery=Vehicle::select('vehicle_region_id',DB::raw('GROUP_CONCAT(vehicle_types.id) AS vehicle_type_ids'))
        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')->groupBy('vehicles.vehicle_region_id')->get()->pluck('vehicle_type_ids','vehicle_region_id');
        $vehicleTypesBaseRegion=[];
        foreach($vehicleTypesBaseRegionQuery as $vtKey=>$vt){
            $_vt=explode(',',$vt);
            foreach($_vt as $_vtKey=>$vtValue){
                if(!isset($vehicleTypesBaseRegion[$vtKey]) || (!in_array($vtValue,$vehicleTypesBaseRegion[$vtKey]))){
                    $vehicleTypesBaseRegion[$vtKey][]=$vtValue;
                }
            }
            
        }
        $vehicleTypeIds = Vehicle::where('is_telematics_enabled', 1)->distinct()->lists('vehicle_type_id')->toArray();
        $vehicleTypeProfiles = VehicleType::select('id as id','vehicle_type as text')->whereIn('id',$vehicleTypeIds)->orderBy('text')->get();

        // get all the locations and category data
        $allLocations = Location::select('id', 'name as text')->get()->toArray();
        $allLocationCategory = LocationCategory::withTrashed()->has('location')->select('id', 'name as text')->get()->toArray();

/*        $vehicleTypeProfilesArray = [];
        $index = 0;
        foreach ($vehicleTypeProfiles as $key => $vehicleType) {
            $vehicleTypeProfilesArray[$index]['id'] =  $vehicleType->id;
            $vehicleTypeProfilesArray[$index]['text'] = $vehicleType->text;
            $index++;
        }*/
        //print_r($vehicleTypeProfiles);exit;

        //zone filters
        $zoneService = new ZoneService();
        $zonenames = [];
        $zonenames = $zoneService->getAllZones();
        $zoneNamesFilter = [];
        $i=0;
        foreach ($zonenames as $key => $value) {
            $zoneNamesFilter[$i]['id'] = $value;
            $zoneNamesFilter[$i]['text'] = $value;
            $i++;
        }

        $dataTemp = $this->vehicleService->getDataDivRegLoc();
        $data = $dataTemp['vehicleRegions'];
        $vehicleRegions = [];

        $i=0;
        foreach ($data as $key => $value) {
            $vehicleRegions[$i]['id'] = $key;
            $vehicleRegions[$i]['text'] = $value;
            $i++;
        }
      
        $zonestatus = [1=>'Active', 0=>'In-active'];
        $zoneStatusFilter = [];
        $i=0;
        foreach ($zonestatus as $key => $value) {
            $zoneStatusFilter[$i]['id'] = $key;
            $zoneStatusFilter[$i]['text'] = $value;
            $i++;
        }

        $alertSetting = [1=>'On entry', 0=>'On exit', 2=>'On entry and exit'];
        $alertSettingFilter = [];
        $alertTypeFilter = [];
        $i=0;
        foreach ($alertSetting as $key => $value) {
            $alertSettingFilter[$i]['id'] = $key;
            $alertSettingFilter[$i]['text'] = $value;
            if($key != 2) {
                $alertTypeFilter[$i]['id'] = $key;
                $alertTypeFilter[$i]['text'] = $value;
            }
            $i++;
        }

        $companyOptions = $this->getAllCompanies();
        $companyListArray = array();
        foreach ($companyOptions as $key => $value) {
            array_push($companyListArray, ['id'=>$key, 'text'=>$value]);
        }

        // get logged user id
        $loggedUserId = auth()->user()->id;

        $typeForSelect = [
            ['id' => 'user', 'text' => 'Users'],
            ['id' => 'vehicle', 'text' => 'Vehicles']
        ];

        JavaScript::put([
            'lastname' => $lastnames,
            // 'livelastname' => $livelastnames,
            'vehicleRegistrations' => $vehicleRegistrations,
            'regionForSelect' => $regionForFilter,
            'incidentTypes' => $incidentType,
            'selectedTab' => $selectedTab,
            'zoneNames' => $zoneNamesFilter,
            'vehicleRegions' => $vehicleRegions,
            'zonestatus'=>$zoneStatusFilter,
            'zonesColumnManagement' => $zonesColumnManagement,
            'zoneAlertsColumnManagement' => $zoneAlertsColumnManagement,
            // 'chartData' => $this->getBehaviourChartsData()
            'vehicleTypeProfiles' => $vehicleTypeProfiles,
            'columnManagement' => $columnManagement,
            'allLocation' => $allLocations,
            'allLocationCategory' => $allLocationCategory,
            'companyList' => $companyListArray,
            'loggedUserId' => $loggedUserId,
            'alertSetting' => $alertSettingFilter,
            'alertType' => $alertTypeFilter,
            'typeForSelect' => $typeForSelect,
            'vehicleTypesBaseRegion'=>$vehicleTypesBaseRegion
        ]);
        
        $formattedStartDate = Carbon::today()->startOfDay()->format('d/m/Y h:i:s');
        $formattedEndDate = Carbon::today()->endOfDay()->format('d/m/Y h:i:s');
        $defaultDateRange = $formattedStartDate.' - '.$formattedEndDate;
        $todayDateRange = $formattedStartDate.' - '.$formattedEndDate;

        $column_management = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','locations')
        ->select('data')
        ->first();

        JavaScript::put([
            'column_management' => $column_management,            
        ]);
      
        return view('telematics.index')
               ->with('selectedTab', $selectedTab)
               ->with('defaultDateRange', $defaultDateRange)
               ->with('todayDateRange',$todayDateRange)
               ->with('_regionForSelect',$regionForSelect)
               ->with('_vehicleTypeProfiles',$vehicleTypeProfiles)
               ->with('_allLocationCategory',$allLocationCategory);
    }

    public function resetTelematicsTab(){
        setcookie('telematics_ref_tab', 'behaviours_tab', time() + (86400 * 30), "/");
        return redirect('telematics');
    }
    
    public function createZone() {
        $allRegions=[];
        $data=$this->vehicleService->getDataDivRegLoc();
        $vehicleRegions =['' => '']+$data['vehicleRegions'];
        
        $alertType = ['one_off'=>'One-off', 'regular'=>'Regular'];
        $alertInterval = ['1min'=>'Every 1 minute', '5min'=>'Every 5 minutes', '30min'=>'Every 30 minutes'];
        $zoneTracking = ['0'=>'Track activity outside zone', '1'=>'Track activity inside zone'];
        $vehicleTypes = VehicleType::select('id as id', 'vehicle_type as text')->orderBy('text')->get();
        $alertSetting = config('config-variables.alert_setting');

        
        $userRegions = Auth::user()->regions->lists('id')->toArray();  
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicle_region_id', $userRegions)->get();
        $allVehicleTypesList = VehicleType::withTrashed()->select('id as id', 'vehicle_type as text')->get();
        $finalTypeList = [];
        foreach($allVehicleTypesList as $vehicletype){
            $finalTypeList[$vehicletype->id] = $vehicletype->text;
        }
            
        $column_management = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','telematicsZones')
        ->select('data')
        ->first();

        $resultVehicle = (new UserService())->getAllVehicleLinkedData();

        JavaScript::put([
            'column_management' => $column_management,
            'isRegionLinkedInVehicle' => env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'zoneRegistration' => $vehicleRegistrations 
        ]);
       // print_r($resultVehicle);
       // print_r($allVehicleTypesList);exit;
        return view('telematics.createZone')
                ->with('user', Auth::user())
                ->with('isRegionLinkedInVehicle', env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                ->with('vehicleDivisions', $resultVehicle['vehicleDivisions'])
                ->with('allVehicleDivisionsList', $resultVehicle['vehicleRegions'])
                ->with('allVehicleTypesList', $finalTypeList)
                ->with('vehicleRegistrations',$vehicleRegistrations)
                ->with('vehicleTypes',$vehicleTypes)
                ->with('vehicleRegions',$vehicleRegions)
                ->with('alertType',$alertType)
                ->with('alertInterval',$alertInterval)
                ->with('zoneTracking',$zoneTracking)
                ->with('alertSetting',$alertSetting);
    }
    
    public function storeZone(Request $request)
    {
        //Array ( [_token] => BuObfkj3dQaQTiaunzDTPAT7STqIUjNnkyNaXCGF [name] => HARDIK1 [region] => 4 [alert_status] => on [alert_type] => one-off [alert_interval] => 1min )
        //print_r($request->all());exit;
        $zoneService = new ZoneService();
        $zone = $zoneService->store($request->all());

        flash()->success(config('config-variables.flashMessages.zoneSaved'));
        // return $this->index($request);
        return redirect()->route('telematics.index');

    }
    public function zoneDetails($id) {
        $zone = Zone::where('id','=',$id)->first();
        //print_r($zone);exit;
        return view('telematics.showZone')
                ->with('zone',$zone);
    }
    public function editZone($id) {
        
        $data=$this->vehicleService->getDataDivRegLoc();
        $alertSetting = config('config-variables.alert_setting');

      /*   $alertType = ['one_off'=>'One-off', 'regular'=>'Regular'];
        $alertInterval = ['1min'=>'Every 1 minute', '5min'=>'Every 5 minutes', '30min'=>'Every 30 minutes']; */
        $zone = Zone::where('id','=',$id)->first();
        $zoneService = new ZoneService();
        $zoneBoundsJson = $zoneService->makePolygonJson($zone);
       /*  if ($zone->is_tracking_inside == 0) {
            $is_tracking_inside_text = 'Track activity outside zone';
        }
        else{
           $is_tracking_inside_text = 'Track activity inside zone'; 
        }
        $zoneApplyToText = ['type' => '', 'data' => '']; */
       
        $column_management = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','telematicsZones')
        ->select('data')
        ->first();

        JavaScript::put([
            'column_management' => $column_management,
        ]);
        return view('telematics.editZone')
                ->with('zone',$zone) 
                ->with('zoneBoundsJson',$zoneBoundsJson)
                //->with('is_tracking_inside_text',$is_tracking_inside_text)
                ->with('alertSetting',$alertSetting);
    }
    
    public function updateZone(Request $request)
    {         
        //Array ( [_token] => BuObfkj3dQaQTiaunzDTPAT7STqIUjNnkyNaXCGF [name] => HARDIK1 [region] => 4 [alert_status] => on [alert_type] => one-off [alert_interval] => 1min )
        //print_r($request->all());exit;
        $data = $request->all();
        $id = $data['id'];
        $zone = Zone::findOrFail($id);
        $zoneService = new ZoneService();
        $zone = $zoneService->update($request->all(), $id);
        
        flash()->success(config('config-variables.flashMessages.zoneSaved'));
        // return $this->index($request);
        return redirect()->route('telematics.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyZone($id)
    {
        if(Zone::where('id', $id)->delete()) {
            return redirect('telematics');
        }
    }

    /**
     * Return the zones data for the grid
     *
     * @return [type] [description]
     */
    public function getZoneData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new ZonesRepository($request), $request->all());
    }

    /**
     * Return the zones data for the grid
     *
     * @return [type] [description]
     */
    public function getZoneAlertsData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new ZoneAlertsRepository($request), $request->all());
    }
    public function zoneAlertMarkerDetails(Request $request)
    {
        $markerDetails = array();
        $zoneAlertId = $request->alertId;
        $zoneAlert = ZoneAlerts::with('vehicle','user','zone')->where('id',$zoneAlertId)->first();
        $zoneService = new ZoneService();
        $mapAlertData = $zoneService->mapAlertsJson($zoneAlert);

        return json_encode(['markerData' => $mapAlertData]);
    }

    public function loadInfoWindowAlertMarker($zoneAlertData) {
        $view = view('_partials.telematics.zoneAlertMarkerDetails')->with(['markerDetails'=>$zoneAlertData]);
        return $view->render();
    }

    //should remain same
    private function processChartData($data) {

        $finalArray = [];
        $processKeys = ['drivingChartData','fleetMilesChartData', 'fuelChartData', 'co2ChartData', 'scoreHistoryChartData'];
        foreach($data as $key => $singleChartData) {
            if (in_array($key,$processKeys)) {
                $singleChartData = (array)$singleChartData;
                $single = [];
                foreach ($singleChartData as $dateMonth => $value) {
                    $dateMonth = Carbon::parse($dateMonth);
                    $dateMonth = $dateMonth->format('d M Y');
                    // $dateMonth = $dateMonth->format('M Y');
                    $single[$dateMonth] = $value;
                }
                $finalArray[$key]['labels'] = array_keys($single);
                $finalArray[$key]['data'] = array_values($single);
                if ($key == 'fleetMilesChartData') {
                    //convert meters to miles
                    foreach ($finalArray[$key]['data'] as $keyname => $value1) {
                        $finalArray[$key]['data'][$keyname] = $value1 * 0.00062137;
                    }
                }
                if ($key == 'scoreHistoryChartData') {
                    $overallScoreValue = [];
                    $efficiencyScoreValue = [];
                    $safetyScoreValue = [];
                    foreach ($finalArray[$key]['data'] as $value1) {
                        $efficiencyScore = isset($value1['efficiencyScoreValue']) ? $value1['efficiencyScoreValue'] : 100;
                        $safetyScore = isset($value1['safetyScoreValue']) ? $value1['safetyScoreValue'] : 100;

                        array_push($overallScoreValue, ($efficiencyScore + $safetyScore)/2);
                        array_push($efficiencyScoreValue, $efficiencyScore);
                        array_push($safetyScoreValue, $safetyScore);
                    }
                    $finalArray[$key]['data']['overallScoreValue'] = $overallScoreValue;
                    $finalArray[$key]['data']['efficiencyScoreValue'] = $efficiencyScoreValue;
                    $finalArray[$key]['data']['safetyScoreValue'] = $safetyScoreValue;
                }
            }
        }
        return $finalArray;
    }

    public function getBehaviourTabData(){
//print_r("here");exit;
        $behaviourData = [];
        $payload = [];

        $userFilterValue = '';
        if (request()->has('userFilterValue')) {
            $userFilterValue = request()->get('userFilterValue');
        }
        $registrationFilterValue = '';
        if (request()->has('registrationFilterValue')) {
            $registrationFilterValue = request()->get('registrationFilterValue');
        }
        $regionFilterValue = '';
        if (request()->has('regionFilterValue')) {
            $regionFilterValue = request()->get('regionFilterValue');
        }

        if (request()->has('startDate') && request()->has('endDate')) {
             $sdate = request()->get('startDate');
             $edate = request()->get('endDate');
             $startDate = Carbon::createFromFormat('d/m/Y H:i:s',  $sdate)->startOfDay(); 
             $endDate = Carbon::createFromFormat('d/m/Y H:i:s',  $edate)->endOfDay();
             $startDateForPeriod = Carbon::createFromFormat('d/m/Y H:i:s',  $sdate);
             $endDateForPeriod = Carbon::createFromFormat('d/m/Y H:i:s',  $edate);
             /*$sdate = request()->get('startDate')." 00:00:00";
             $edate = request()->get('endDate')." 23:59:59";
             $startDate = Carbon::createFromFormat('Y-m-d H:i:s',  $sdate)->startOfDay(); 
             $endDate = Carbon::createFromFormat('Y-m-d H:i:s',  $edate)->endOfDay();
             $startDateForPeriod = Carbon::createFromFormat('Y-m-d H:i:s',  $sdate);
             $endDateForPeriod = Carbon::createFromFormat('Y-m-d H:i:s',  $edate);*/

        } else {
             $startDateStr = Carbon::now()->subYear(1)->firstOfMonth()->format('Y-m-d H:i:s');
             $endDateStr = Carbon::now()->subMonth(1)->endOfMonth()->format('Y-m-d H:i:s');
             $startDate = Carbon::createFromFormat('Y-m-d H:i:s',  $startDateStr )->startOfDay(); 
             $endDate = Carbon::createFromFormat('Y-m-d H:i:s',  $endDateStr )->endOfDay(); 
             $startDateForPeriod = Carbon::createFromFormat('Y-m-d H:i:s',  $startDate)->startOfMonth(); 
             $endDateForPeriod = Carbon::createFromFormat('Y-m-d H:i:s',  $endDate)->endOfMonth(); 

        }

        $journey_ids = [];

        $loggedinUserRegions = Auth::user()->regions->lists('id')->toArray();
        $loggedinUserRegionVehicles = Vehicle::whereIn('vehicle_region_id', $loggedinUserRegions)->get()->pluck('id');


        /*** FOR RAW DATA CALCULATION *****/
        // $scoreChartData = TelematicsJourneys::selectRaw("GROUP_CONCAT(journey_id) AS journey_ids, date(start_time) as row_day")
        //                             //->whereDate("start_time",">=",$startDate->toDateString())
        //                             //->whereDate("start_time","<=",$endDate->toDateString())
        //                             ->whereBetween("start_time",[$startDate,$endDate])
        //                             ->whereNotNull('end_time')
        //                             ->groupBy(['row_day']);

        /*** FOR RAW DATA CALCULATION *****/
        // $sql = TelematicsJourneys::selectRaw("DATE_FORMAT(start_time,'%Y-%m') AS row_period, GROUP_CONCAT(journey_id) AS journey_ids")
        //                             //->whereDate("start_time",">=",$startDate->toDateString())
        //                             //->whereDate("start_time","<=",$endDate->toDateString())
        //                             ->whereBetween("start_time",[$startDate,$endDate])
        //                             ->whereNotNull('end_time')
        //                             ->groupBy('row_period');

        // $sql = TelematicsJourneys::selectRaw("sum(gps_distance) as sum_gps_distance, sum(engine_duration) as sum_engine_duration, sum(fuel) as sum_fuel, sum(co2) as sum_co2, AVG(safety_score) as avgSafetyScore, AVG(efficiency_score) as avgEfficiencyScore, date(start_time) as row_period")
        //                             ->whereDate("start_time",">=",$startDate->toDateString())
        //                             ->whereDate("start_time","<=",$endDate->toDateString())
        //                             ->whereNotNull('end_time')  
        //                             ->groupBy(['row_period']);


        $sql = TelematicsJourneys::selectRaw("GROUP_CONCAT(id) AS journey_ids, date(start_time) as row_period")
                                    ->where("start_time",">=",$startDate->toDateString().' 00:00:00')
                                    ->where("start_time","<=",$endDate->toDateString().' 23:59:59')
                                    ->whereNotNull('end_time')  
                                    ->groupBy(['row_period']);

        if ($userFilterValue == "" && $registrationFilterValue == '' && $regionFilterValue == "") {
                                                                   
        }
        if ( $userFilterValue != "" ) {
            $sql = $sql->where("user_id",$userFilterValue);
        }
        if ( $registrationFilterValue != "" ) {
            $sql = $sql->where("vrn",$registrationFilterValue);
        }
        if ( $regionFilterValue != "" ) {
            $vehicleIds = Vehicle::where('vehicle_region_id',$regionFilterValue)->where('is_telematics_enabled','1')->lists('id')->toArray();
            $sql = $sql->whereIn("vehicle_id",$vehicleIds);
        }
        else{
            $sql = $sql->whereIn('vehicle_id', $loggedinUserRegionVehicles);
        }

        // $interval = DateInterval::createFromDateString('1 month');
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($startDateForPeriod, $interval, $endDateForPeriod);
        $res_rows = $sql->get();

        // $scoreChartDataResult = $scoreChartData->get();

        $behaviourDataNew = [];

        /*** FOR RAW DATA CALCULATION *****/
        // $allJourneyIds = [];
        // foreach($res_rows as $journey) {
        //     $journeyIds = explode(",", $journey->journey_ids);
        //     $allJourneyIds = array_merge($allJourneyIds , $journeyIds);

        //     $journeySummaryData = TelematicsJourneys::whereIn('journey_id', $journeyIds)->get();
        //     $res_row = $this->telematicsService->calculateJourneyScore($journeySummaryData);
        //     $rowPeriod = $journey->row_period;

        //     $behaviourDataNew['fleetMilesChartData'][$rowPeriod] = $res_row["gps_distance"];
        //     $behaviourDataNew['drivingChartData'][$rowPeriod] = $res_row["engine_duration"];
        //     $behaviourDataNew['fuelChartData'][$rowPeriod] = $res_row["fuel"];
        //     $behaviourDataNew['co2ChartData'][$rowPeriod] = $res_row["co2"];
        // }

        // foreach ($res_rows as $res_row) {
        //     $behaviourDataNew['fleetMilesChartData'][$res_row["row_period"]] = $res_row["sum_gps_distance"];
        //     $behaviourDataNew['drivingChartData'][$res_row["row_period"]] = $res_row["sum_engine_duration"];
        //     $behaviourDataNew['fuelChartData'][$res_row["row_period"]] = $res_row["sum_fuel"];
        //     $behaviourDataNew['co2ChartData'][$res_row["row_period"]] = $res_row["sum_co2"];

        //     $monthly_safety_score = $res_row['avgSafetyScore'] == null ? 100 :$res_row['avgSafetyScore'];
        //     $monthly_efficiency_score = $res_row['avgEfficiencyScore'] == null ? 100 : $res_row['avgEfficiencyScore'];
        //     $monthly_overall_score = ($monthly_safety_score + $monthly_efficiency_score);
        //     $monthly_score_history = ["overallScoreValue"=>$monthly_overall_score,"safetyScoreValue"=>$monthly_safety_score,"efficiencyScoreValue"=>$monthly_efficiency_score];
        //     $behaviourDataNew['scoreHistoryChartData'][$res_row["row_period"]] = $monthly_score_history;
        // }


        foreach ($res_rows as $res_row) {

            $scoreData = $this->getUserVehicleScoreData($res_row['journey_ids']);

            $behaviourDataNew['fleetMilesChartData'][$res_row["row_period"]] = $scoreData["gps_distance"];
            $behaviourDataNew['drivingChartData'][$res_row["row_period"]] = $scoreData["engine_duration"];
            $behaviourDataNew['fuelChartData'][$res_row["row_period"]] = $scoreData["fuel"];
            $behaviourDataNew['co2ChartData'][$res_row["row_period"]] = $scoreData["co2"];

            $monthly_safety_score = $scoreData['safety'] == null ? 100 :$scoreData['safety'];
            $monthly_efficiency_score = $scoreData['efficiency'] == null ? 100 : $scoreData['efficiency'];
            $monthly_overall_score = ($monthly_safety_score + $monthly_efficiency_score);
            $monthly_score_history = ["overallScoreValue"=>$monthly_overall_score,"safetyScoreValue"=>$monthly_safety_score,"efficiencyScoreValue"=>$monthly_efficiency_score];
            $behaviourDataNew['scoreHistoryChartData'][$res_row["row_period"]] = $monthly_score_history;
        }

        // $dateArr = [];

        /*** FOR RAW DATA CALCULATION *****/
        // foreach ($scoreChartDataResult as $journey) {

        //     $journeyIds = explode(",", $journey->journey_ids);
        //     $journeySummaryData = TelematicsJourneys::whereIn('journey_id', $journeyIds)->get();
        //     $res_row = $this->telematicsService->calculateJourneyScore($journeySummaryData);
        //     $dateArr[] = $journey->row_day;
        //     $monthly_safety_score = $res_row['safety'] == null ? 100 : $res_row['safety'];
        //     $monthly_efficiency_score = $res_row['efficiency'] == null ? 100 : $res_row['efficiency'];
        //     $monthly_overall_score = ($monthly_safety_score + $monthly_efficiency_score);
        //     $monthly_score_history = ["overallScoreValue"=>$monthly_overall_score,"safetyScoreValue"=>$monthly_safety_score,"efficiencyScoreValue"=>$monthly_efficiency_score];
        //     $behaviourDataNew['scoreHistoryChartData'][$journey->row_day] = $monthly_score_history;

        // }

        $behaviourRawData = [];

        foreach ($period as $dt) {
            $dtFormat = $dt->format("Y-m-d");
            if (!isset($behaviourDataNew['fleetMilesChartData'][$dtFormat])) {
                $behaviourRawData['fleetMilesChartData'][$dtFormat] = 0;
            }
            else{
                $behaviourRawData['fleetMilesChartData'][$dtFormat] = $behaviourDataNew['fleetMilesChartData'][$dtFormat];
            }
            if (!isset($behaviourDataNew['drivingChartData'][$dtFormat])) {
                $behaviourRawData['drivingChartData'][$dtFormat] = 0;
            }
            else{
                $behaviourRawData['drivingChartData'][$dtFormat] = $behaviourDataNew['drivingChartData'][$dtFormat];
            }
            if (!isset($behaviourDataNew['fuelChartData'][$dtFormat])) {
                $behaviourRawData['fuelChartData'][$dtFormat] = 0;
            }
            else{
                $behaviourRawData['fuelChartData'][$dtFormat] = $behaviourDataNew['fuelChartData'][$dtFormat];
            }
            if (!isset($behaviourDataNew['co2ChartData'][$dtFormat])) {
                $behaviourRawData['co2ChartData'][$dtFormat] = 0;
            }
            else{
                $behaviourRawData['co2ChartData'][$dtFormat] = $behaviourDataNew['co2ChartData'][$dtFormat];
            }
            if (!isset($behaviourDataNew['scoreHistoryChartData'][$dtFormat])) {
                $behaviourRawData['scoreHistoryChartData'][$dtFormat] = ["overallScoreValue"=>100,"safetyScoreValue"=>100,"efficiencyScoreValue"=>100];
            }
            else{
                $behaviourRawData['scoreHistoryChartData'][$dtFormat] = $behaviourDataNew['scoreHistoryChartData'][$dtFormat];
            }

        }

        $behaviourData = $behaviourRawData;
        $behaviourData['chart_data'] = $this->processChartData($behaviourRawData);

        /*** FOR RAW DATA CALCULATION *****/        
        // $journeySummaryPieChartData = TelematicsJourneys::whereIn('journey_id', $allJourneyIds)->get();
        // $behaviourData['pie_chart_data'] = $this->telematicsService->calculateJourneyScore($journeySummaryPieChartData);
        return $behaviourData;

    }

    public function getIncidentsGridData(Request $request){
        return GridEncoder::encodeRequestedData(new TelematicsJourneyIncidentsRepository($request), $request->all());
    }
    public function getIncidentsData_remove() {

            $incidentMarkerDetailsData = DB::table('telematics_journeys');
            $incidentData = [];
            $userRegions = Auth::user()->regions->lists('id')->toArray(); 
            if (request()->has('startDate') && request()->has('endDate')) {
                $startDate = request()->get('startDate').' 00:00:00';
                $endDate = request()->get('endDate').' 23:59:59';
            } else {
                $startDate = Carbon::now()->toDateString().' 00:00:00';
                $endDate = Carbon::now()->toDateString().' 23:59:59';
            }
            
            $incidentsData = [];
            $systemUserId = env("SYSTEM_USER_ID");
            //$journeyIds = array_merge($journeyIds,['0']);//this line is added because journeyid for heartbeat incident is 0;
            $incidentMarkerDetailsData = DB::table(DB::raw('telematics_journey_details force index (telematics_journey_details_time_index)'))
                                    ->join('telematics_journeys as telematics_journeys', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                                    ->join('users as user', 'telematics_journeys.user_id', '=', 'user.id')
                                    ->join('vehicles as vehicle', 'telematics_journeys.vehicle_id', '=', 'vehicle.id')
                                    ->leftjoin('users as nominatedDriver', 'vehicle.nominated_driver', '=', 'nominatedDriver.id')
                                    //->join('telematics_journeys as tj', 'telematics_journey_details.telematics_journey_id', '=', 'tj.id')
                                    ->where('vehicle.is_telematics_enabled','1')
                                    ->whereBetween("time",[$startDate,$endDate]);

            $userFilterValue = '';
            if (request()->has('userFilterValue')) {
                $userFilterValue = request()->get('userFilterValue');
                $incidentMarkerDetailsData = $incidentMarkerDetailsData->where("telematics_journeys.user_id",$userFilterValue );
                if ($userFilterValue != '') {
                    if($userFilterValue==$systemUserId){
                        $incidentMarkerDetailsData = $incidentMarkerDetailsData->where("telematics_journeys.user_id",$userFilterValue)->whereNull('vehicle.nominated_driver');
                    }else{
                        $incidentMarkerDetailsData = $incidentMarkerDetailsData->where(function ($query) use($userFilterValue) {
                                                                                    $query->where("telematics_journeys.user_id","=",$userFilterValue)
                                                                                          ->orWhere('vehicle.nominated_driver', '=', $userFilterValue);
                                                                                });
                        $incidentMarkerDetailsData = $incidentMarkerDetailsData->where("vehicle.nominated_driver",$userFilterValue);
                    }
                }
            }
            $registrationFilterValue = '';
            if (request()->has('registrationFilterValue')) {
                $registrationFilterValue = request()->get('registrationFilterValue');
                $incidentMarkerDetailsData = $incidentMarkerDetailsData->where("telematics_journey_details.vrn", $registrationFilterValue);
            }
            $regionFilterValue = '';
            if (request()->has('regionFilterValue')) {
                $regionFilterValue = request()->get('regionFilterValue');
                $incidentMarkerDetailsData = $incidentMarkerDetailsData->where("vehicle.vehicle_region_id", $regionFilterValue);
            }
            $incidentTypeFilterValue = '';
            if (request()->has('incidentTypeFilterValue')) {
                $incidentTypeFilterValue = request()->get('incidentTypeFilterValue');
                if ($incidentTypeFilterValue == 'harsh.cornering') {
                    $incidentMarkerDetailsData = $incidentMarkerDetailsData->whereIn('ns', ['tm8.dfb2.cnrl.l','tm8.dfb2.cnrr.l']);
                }
                else{
                    $incidentMarkerDetailsData = $incidentMarkerDetailsData->where('ns', $incidentTypeFilterValue);
                }
            } else {
                $incidentTypes = array_keys(config('config-variables.telematics_incidents'));
                $incidentMarkerDetailsData = $incidentMarkerDetailsData->whereIn('ns', $incidentTypes);
            }
            $incidentMarkerDetailsData = $incidentMarkerDetailsData->selectRaw('vehicle.registration,
                CASE WHEN j.end_time IS NULL OR u.id = "'.$systemUserId.'" THEN "'.config('config-variables.telematicsSystemUserVisibleName.FULL').'" 
                ELSE CONCAT(u.first_name, " ", u.last_name) END AS user,

                ns as incident_type,DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as date_edited,
                concat(telematics_journey_details.street," ",telematics_journey_details.town," ",telematics_journey_details.post_code) as location,lat as latitude,lon as longitude,telematics_journey_details.id as journeryIncidentIndex,telematics_journey_details.telematics_journey_id as journey_id,telematics_journey_details.heading, telematics_journey_details.speed,
                    CASE WHEN ns = "tm8.dfb2.acc.l" THEN "harsh_acceleration.png"
                    WHEN ns = "tm8.dfb2.cnrl.l" THEN "harsh_left_cornering.png"
                    WHEN ns = "tm8.dfb2.cnrr.l" THEN "harsh_right_cornering.png"
                    WHEN ns = "tm8.dfb2.dec.l" THEN "harsh_braking.png"
                    WHEN ns = "tm8.dfb2.rpm" THEN "rpm_over_threshold.png"
                    WHEN ns = "tm8.dfb2.spdinc" THEN "speeding_over_threshold.png"
                    WHEN ns = "tm8.gps.idle.end" THEN "idling.png"
                    WHEN ns = "tm8.gps.idle.start" THEN "idling.png"
                    WHEN ns = "tm8.gps.idle.ongoing" THEN "idling.png"
                    WHEN ns = "tm8.gps.exces.idle" THEN "idling.png"
                    ELSE "location.png" END as icon,
                    CASE WHEN ns = "tm8.dfb2.acc.l" THEN "Harsh Acceleration"
                    WHEN ns = "tm8.dfb2.cnrl.l" THEN "Harsh Left Cornering"
                    WHEN ns = "tm8.dfb2.cnrr.l" THEN "Harsh Right Cornering"
                    WHEN ns = "tm8.dfb2.dec.l" THEN "Harsh Braking"
                    WHEN ns = "tm8.dfb2.rpm" THEN "RPM"
                    WHEN ns = "tm8.dfb2.spdinc" THEN "Speeding"
                    WHEN ns = "tm8.gps.idle.end" THEN "Idle End"
                    WHEN ns = "tm8.gps.idle.start" THEN "Idle Start"
                    WHEN ns = "tm8.gps.idle.ongoing" THEN "Idling"
                    WHEN ns = "tm8.gps.exces.idle" THEN "Idling"
                    ELSE "location.png" END as incident_name, 1 as count
                ');
        return $incidentMarkerDetailsData->get();
        
    }

    private function getIncidentTypeFromNs($namespace) {
        switch ($namespace) {
            case 'tm8.dfb2.acc.l' :
                return 'Harsh Acceleration';
                break;

            case 'tm8.dfb2.cnrl.l' :
                return 'Harsh Left Cornering';
                break;

            case 'tm8.dfb2.cnrr.l' :
                return 'Harsh Right Cornering';
                break;

            case 'tm8.dfb2.dec.l' :
                return 'Harsh Braking';
                break;

            case 'tm8.dfb2.rpm' :
                return 'RPM';
                break;

            case 'tm8.dfb2.spdinc' :
                return 'Speeding';
                break;

            case 'tm8.gps' :
                return 'GPS Periodic';
                break;

            case 'tm8.gps.idle.end';
                return 'Idle End';
                break;

            case 'tm8.gps.idle.start' :
                return 'Idle Start';
                break;

            case 'tm8.gps.ign.off' :
                return 'Ignition Off';
                break;

            case 'tm8.gps.ign.on' :
                return 'Ignition On';
                break;

            case 'tm8.gps.jny.end' :
                return 'Journey End';
                break;

            case 'tm8.gps.jny.start' :
                return 'Journey Start';
                break;

            case 'tm8.jny.sum' :
                return 'Journey Summary';
                break;

            case 'tm8.jny.sum.ex1' :
                return 'Journey Summary Extended';
                break;

            case 'tm8.battery.profile.generated' :
                return 'Battery Profile Generated';
                break;

            case 'tm8.gps.idle.ongoing' :
                return 'Idling';
                break;

            case 'tm8.gps.exces.idle' :
                return 'Extended idling';
                break;

            case 'tm8.fnol' :
                return 'FNOL';
                break;

            default :
                return str_replace("tm8","",str_replace("."," ",$namespace));
                break;

        }
    }

    private function getIconFromIncidentType($namespace) {

        $baseDir = '/img/vehicle_images/incidentsXs/';

        switch ($namespace) {
            case 'tm8.dfb2.acc.l' :
                return $baseDir.'harsh_acceleration.png';
                break;

            case 'tm8.dfb2.cnrl.l' :
                return $baseDir.'harsh_left_cornering.png';
                break;

            case 'tm8.dfb2.cnrr.l' :
                return $baseDir.'harsh_right_cornering.png';
                break;

            case 'tm8.dfb2.dec.l' :
                return $baseDir.'harsh_braking.png';
                break;

            case 'tm8.dfb2.rpm' :
                return $baseDir.'rpm_over_threshold.png';
                break;

            case 'tm8.dfb2.spdinc' :
                return $baseDir.'speeding_over_threshold.png';
                break;


            case 'tm8.gps.idle.end' :
                return $baseDir.'idling.png';
                break;

            case 'tm8.gps.idle.start' :
                return $baseDir.'idling.png';
                break;

            case 'tm8.gps.idle.ongoing' :
                return $baseDir.'idling.png';
                break;

            case 'tm8.gps.exces.idle' :
                return $baseDir.'idling.png';
                break;

            case 'tm8.fnol' :
                return $baseDir.'crash.png';
                break;

            default :
                return $baseDir.'location.png';
                break;

        }
    }

    public function incidentMarkerDetails(Request $request)
    {
        $markerDetails = array();
        $incident_id = $request->incident_id;
        $incident = TelematicsJourneyDetails::with('telematicsJourneys.vehicle','telematicsJourneys.user')->find($incident_id);
        $vehicle = $incident->telematicsJourneys->vehicle;
        $user = $incident->telematicsJourneys->user;
        
        $incidentData = [];
        $incidentData['vehicle_id'] = $vehicle->id;
        $incidentData['direction'] = $this->calcDirection($incident->heading);
        $incidentData['speed'] = $this->mpsToMph($incident->speed).' MPH';//need to convert this speed to mph
        $incidentData['status'] =  $vehicle->status;
        $incidentData['journey_id'] = $incident->telematics_journey_id;
        $incidentData['ns'] = $incident->ns;
        $incidentData['icon'] = $this->getIconFromIncidentType($incident->ns);
        $incidentData['incident_name'] = $this->getIncidentTypeFromNs($incident->ns);
        $driver = TelematicsJourneys::getDriverToShow($incident->telematics_journey_id);
        $userTextVisibility = 'N/A';
        if($driver != null && $driver['user_id'] != env('SYSTEM_USER_ID')){
            $userTextVisibility = substr($driver['first_name'], 0, 1).' '.$driver['last_name'] . ' (' .$driver['email'] . ')';
       }
       else {
            $userTextVisibility = config('config-variables.telematicsSystemUserVisibleName.FULL');
       }
        
        $incidentData['registration'] = $vehicle->registration;
        $incidentData['user'] = $userTextVisibility;
        $incidentData['location'] = $incident->street." ".$incident->town." ".$incident->post_code;
        //$incidentData['date_edited'] = DATE_FORMAT($incident->time,"%Y-%m-%d %H:%i:%s");
        $incidentData['date_edited'] = Carbon::parse($incident->time)->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
        $incidentData['latitude'] = $incident->lat;
        $incidentData['longitude'] = $incident->lon;
        
        // $maxAccelaration = $this->getSpeedLimit($incident->lat, $incident->lon);
        $maxAccelaration = $incident->street_speed;
        $incidentData['max_acceleration'] = number_format(round((float)$this->mpsToMph($maxAccelaration),0,PHP_ROUND_HALF_UP),2).' MPH';
        $incidentData['street_speed'] = $this->mpsToMph($maxAccelaration);

        return view('_partials.telematics.incidentMarkerDetails')->with(['incidentData'=>$incidentData]);
    }

    private function mpsToMph($metersPerSecond) {
        $metersPerSecond = is_numeric($metersPerSecond) ? $metersPerSecond : 0;
        // if(env('TELEMATICS_PROVIDER') != 'webfleet') {
            $milesPerHour = $metersPerSecond * 2.236936;
        // } else {
        //     $milesPerHour = $metersPerSecond * 0.621371;
        // }
        return number_format($milesPerHour,2);
        //miles per hour = meters per second ? 2.236936
    }
    public function journeyMarkerDetails(Request $request)
    {
        $markerDetails = array();
        $vehicle_id = $request->registration;
        $vehicle = Vehicle::where('registration',$vehicle_id)->with(['nominatedDriver'])->first();
        $incidentData = $request->data;
        $incidentData['vehicle_id'] = $vehicle->id;
        $incidentData['direction'] = $this->calcDirection($incidentData['data']['heading']);
        $incidentData['speed'] = $this->mpsToMph($incidentData['data']['speed']).' MPH';
        $incidentData['status'] = $vehicle = Vehicle::where('registration',$incidentData['registration'])->first() ? $vehicle->status : '';


        $registration = $incidentData['registration'];
        $journeyId = $incidentData['data']['journey_id'];

        // $maxAccelaration = $this->getSpeedLimit($incidentData['latitude'], $incidentData['longitude']);
        $maxAccelaration = $incidentData['data']['street_speed'];

        $incidentData['max_acceleration'] = number_format(round((float)$this->mpsToMph($maxAccelaration),0,PHP_ROUND_HALF_UP),2).' MPH';
        $incidentData['street_speed'] = $this->mpsToMph($maxAccelaration);

        return view('_partials.telematics.journeyMarkerDetails')->with(['incidentData'=>$incidentData]);
    }

    public function markerDetails(Request $request)
    {
        $markerDetails = array();
        $vehicle_id = $request->vehicle_id;
        $vehicle = Vehicle::where('id',$vehicle_id)->with(['nominatedDriver','lastTelematicsJourney'])->first();
        
        $lastVehicleJourney = $vehicle->lastTelematicsJourney->first();
        
        $row = [];

        $row['latestTelematics'] = TelematicsJourneyDetails::where('vrn',$vehicle->registration)
        //->where('telematics_journey_id','>=',$vehicle->telematics_latest_journey_id)
                                            ->whereNotNull('post_code')
                                            //->orderBy('time','DESC')
                                            ->orderBy('id','DESC')
                                            ->first(['street', 'town', 'post_code', 'speed', 'heading', 'time', 'lat', 'lon', 'street_speed']);

        //$row['todayJourneySummaries'] = TelematicsJourneys::where('vrn',$vehicle->registration)->whereDate('start_time', '=', Carbon::today())->get();
        //$row['todayVehicleTrack'] = TelematicsJourneyDetails::select('time')->where('vrn',$vehicle->registration)->whereDate('time', '=', Carbon::today())->where('ns','tm8.gps.ign.on')->orderBy('id','ASC')->first();

        $markerDetails['driver'] = $vehicle->nominatedDriver ? $vehicle->nominatedDriver->first_name.' '.$vehicle->nominatedDriver->last_name : 'Unassigned driver';
        if(env('TELEMATICS_PROVIDER') == 'webfleet') { 
            $markerDetails['registration'] = $vehicle->registration;
            $markerDetails['status'] = $vehicle->status;
            $markerDetails['vehicleId'] = $vehicle_id;
            // $markerDetails['driver'] = 'NA';
            $markerDetails['speed'] = 'NA';
            $markerDetails['street_speed'] = 'NA';
            $markerDetails['direction'] = 'NA';
            $markerDetails['lat'] = $vehicle->telematics_lat;
            $markerDetails['lon'] = $vehicle->telematics_lon;
            $markerDetails['street'] = $vehicle->telematics_street;
            $markerDetails['town'] = $vehicle->telematics_town;
            $markerDetails['postcode'] = $vehicle->telematics_postcode;
            //$markerDetails['last_update'] = $vehicle->telematics_latest_location_time;
            $markerDetails['last_update'] = Carbon::parse($vehicle->telematics_latest_location_time)->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));

            $markerDetails['total_idling'] = "0";
            $markerDetails['total_stopped'] = "0";
        }
        else{

            $latestTelematics = isset($row['latestTelematics']) ? $row['latestTelematics'] : '';

            $markerDetails['registration'] = $vehicle->registration;
            $markerDetails['status'] = $vehicle->status;
            $markerDetails['date'] = $vehicle->created_at;
            $markerDetails['vehicleId'] = $vehicle_id;
            // $markerDetails['driver'] = $lastVehicleJourney != null && $lastVehicleJourney->user != null ? $lastVehicleJourney->user->first_name.' '.$lastVehicleJourney->user->last_name : 'Driver Unknown';
            // if ($lastVehicleJourney != null && $lastVehicleJourney->user != null && $lastVehicleJourney->user->id == env('SYSTEM_USER_ID')) {
            //     $markerDetails['driver'] = 'Driver Unknown';
            // }

            if ($latestTelematics != '') {
                $markerDetails['street'] = $latestTelematics['street'];
                $markerDetails['town'] = $latestTelematics['town'];
                $markerDetails['postcode'] = $latestTelematics['post_code'];
                $markerDetails['speed'] = $this->mpsToMph($latestTelematics['speed']).' MPH';
                $markerDetails['direction'] = $this->calcDirection($latestTelematics['heading']);
                $markerDetails['last_update'] = Carbon::parse($latestTelematics['time'])->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
                $markerDetails['total_idling'] = "0";
                $markerDetails['total_stopped'] = "0";
                $markerDetails['lat'] = $vehicle->telematics_lat;
                $markerDetails['lon'] = $vehicle->telematics_lon;
                // $markerDetails['max_acceleration'] = number_format(round((float)$this->mpsToMph($this->getSpeedLimit($latestTelematics['lat'], $latestTelematics['lon'])),0,PHP_ROUND_HALF_UP),2).' MPH';

               if($latestTelematics['street_speed'] == 0 || $latestTelematics['street_speed'] == null){
                    $markerDetails['street_speed'] = round((float)$this->mpsToMph($this->getSpeedLimit($latestTelematics['lat'], $latestTelematics['lon'])));
                }
                else{
                    $markerDetails['street_speed'] = round((float)$this->mpsToMph($latestTelematics['street_speed']));
                }

                // $markerDetails['max_acceleration'] = number_format(round((float)$this->getSpeedLimit($latestTelematics['lat'], $latestTelematics['lon']),0,PHP_ROUND_HALF_UP),2).' MPH';
            }
            else{
                $markerDetails['street'] = '';
                $markerDetails['town'] = '';
                $markerDetails['postcode'] = '';
                $markerDetails['speed'] = '';
                $markerDetails['direction'] = '';
                $markerDetails['last_update'] = '';
                $markerDetails['total_idling'] = "0";
                $markerDetails['total_stopped'] = "0";
                // $markerDetails['lat'] = '';
                // $markerDetails['lon'] = '';
                $markerDetails['lat'] = $vehicle->telematics_lat;
                $markerDetails['lon'] = $vehicle->telematics_lon;
		$markerDetails['street_speed']='';
            }
        }
        
        return view('_partials.telematics.markerDetails')->with(['markerDetails'=>$markerDetails]);
    }

    public function getLocationmarkerDetails(Request $request)
    {
        $location = Location::where('id', $request->location_id)->with('category')->first();
        return view('_partials.telematics.locationMarkerDetails')->with(['location'=>$location]);
    }

    public function getSearchedTelematicsData(Request $request){
        $sRegList = null;
        if (request()->has('sRegList')) {
            $sRegList= explode(",",request()->get('sRegList'));
        }
        return $this->calculate_vars($sRegList, 'search');
    }

/*    public function getTelematicsData(){
       return $this->calculate_vars();
    }

    public function getVehiclesOnFleet(){
            $vehicles = Vehicle::with('type','nominatedDriver','lastTelematicsJourney')->where('is_telematics_enabled','1')->get();
            $vehiclesOnFleet = $this->populateVehiclesOnFleetArray($vehicles);
            \Log::info($vehiclesOnFleet);
            return $vehiclesOnFleet;
    }

    public function getActiveVehiclesOnFleet($userRegions, $activeVehiclesVrn)
    {
        $vehicles = Vehicle::with('type','nominatedDriver','lastTelematicsJourney')->whereIn('registration',$activeVehiclesVrn)->get();  
        $vehiclesOnFleet = $this->populateVehiclesOnFleetArray($vehicles);
        \Log::info("activevehiclesOnFleet");
        \Log::info($vehiclesOnFleet);
        return $vehiclesOnFleet;
    }
    
    public function getInActiveVehiclesOnFleet($userRegions, $activeVehiclesVrn){

        $inactiveVehicles = Vehicle::with('type','nominatedDriver','lastTelematicsJourney')->where('is_telematics_enabled','1')->whereNotIN('registration',$activeVehiclesVrn)->whereIn('vehicle_region_id', $userRegions)->get();
        \Log::info("inactivevehicles===list");
        \Log::info($inactiveVehicles);
        $inactiveVehiclesOnFleet = $this->populateVehiclesOnFleetArray($inactiveVehicles);
        \Log::info("inactivevehiclesOnFleet");
        \Log::info($inactiveVehiclesOnFleet);
        return $inactiveVehiclesOnFleet;
    }

    public function getAllVehiclesOnFleet_old(){
            $allVehicleOnFleet = [];
            $length = 0;
            $userRegions = Auth::user()->regions->lists('id')->toArray();
            $activeVehiclesOnFleet = DB::table('telematics_journey_details')
            ->join('vehicles','vehicles.registration','=','telematics_journey_details.vrn')
            ->select('vehicles.registration')
            ->where('vehicles.is_telematics_enabled', 1)
            ->whereIn('vehicle_region_id', $userRegions)
            ->groupBy('vehicles.registration')
            ->get();

            $activeVehiclesVrn = collect($activeVehiclesOnFleet)->pluck('registration');
            
            // $activeVehiclesVrn = TelematicsJourneys::whereNull('end_time')
            //                     ->distinct()
            //                     ->get(['vrn'])
            //                     ->pluck('vrn');

            $activeVehiclesOnFleet = $this->getActiveVehiclesOnFleet($userRegions, $activeVehiclesVrn);
            //$inactiveVehiclesOnFleet = $this->getInActiveVehiclesOnFleet($userRegions, $activeVehiclesVrn);

            if(count($activeVehiclesOnFleet) > 0) {
              foreach ($activeVehiclesOnFleet as $key => $activeVehicle) {
                $allVehicleOnFleet[$length] =$activeVehicle;
                $length++;
              }
            }
            // if(count($inactiveVehiclesOnFleet) > 0) {
            //   foreach ($inactiveVehiclesOnFleet as $key => $inActiveVehicle) {
            //     $allVehicleOnFleet[$length] =$inActiveVehicle;
            //     $length++;
            //   }
            // }

        return $allVehicleOnFleet;
    }

    private function populateVehiclesOnFleetArray($vehicles){
        $vehiclesOnFleet = array();
        $registrations = $vehicles->lists('registration')->toArray();
        $respData = [];

        $latestTelematics = DB::table(DB::raw('telematics_journey_details thdl'))
        ->join(DB::raw('(SELECT MAX(tjd.id) AS latestId,registration,vt.vehicle_category AS vehicle_category, vt.vehicle_subcategory AS vehicle_subcategory FROM `vehicles` v 
        INNER JOIN vehicle_types vt ON vt.id =  v.vehicle_type_id 
        INNER JOIN telematics_journey_details tjd ON tjd.vrn = v.registration
        WHERE v.is_telematics_enabled = 1
        GROUP BY registration) tmp1'),'tmp1.latestId','=','thdl.id')
        ->where('lat','!=',0)
        ->where('lon','!=',0)
        ->whereNotNull('lon')
        ->whereNotNull('lat')
        ->get();

        foreach ($latestTelematics as $key => $value) {
            $respData[$value->vrn]['latestTelematics'] = json_decode(json_encode($value), true);
        }

        foreach ($vehicles as $key => $vehicle) {
            
            if (!array_key_exists($vehicle->registration, $respData)) {
                continue;
            }

            $vehicleData = $respData[$vehicle->registration];
            $latestTelematics = $vehicleData['latestTelematics'];
            $lastTelematicsJourney = $vehicle->lastTelematicsJourney->first();
            $driverId = $lastTelematicsJourney == null || $lastTelematicsJourney->user == null ? env('SYSTEM_USER_ID') : $lastTelematicsJourney->user->id;
            if ($latestTelematics) {
                $vehicleStatus = "stopped";
                $vehicleMarkerIconType = $vehicle->type->vehicle_subcategory == null? ($vehicle->type->vehicle_category == 'hgv' ? 'hgv' : 'van') : $vehicle->type->vehicle_subcategory;
                if ($latestTelematics['ns'] == 'tm8.gps.ign.off' || $latestTelematics['ns'] == 'tm8.jny.sum' || $latestTelematics['ns'] == 'tm8.jny.sum.ex1' || $latestTelematics['ns'] == 'tm8.gps.heartbeat' || $latestTelematics['ns'] == 'tm8.gps.jny.end') {
                    $vehicleMarkerIconType = $vehicleMarkerIconType.'_stopped';
                } elseif ($latestTelematics['ns'] == 'tm8.gps.idle.start' OR $latestTelematics['ns'] == 'tm8.gps.idle.ongoing' OR $latestTelematics['ns'] == 'tm8.gps.exces.idle') {
                    $vehicleMarkerIconType = $vehicleMarkerIconType.'_idling';
                    $vehicleStatus = "idling";
                } else {
                    $vehicleMarkerIconType = $vehicleMarkerIconType.'_moving';
                    $vehicleStatus = "moving";
                }
                $driverName = $lastTelematicsJourney == null || $lastTelematicsJourney->user == null ? 'Driver Unknown' : $lastTelematicsJourney->user->first_name;
                if ($lastTelematicsJourney != null && $lastTelematicsJourney->user != null && $lastTelematicsJourney->user->id == env('SYSTEM_USER_ID')) {
                    $driverName = 'Driver Unknown';
                }
                $vehiclesOnFleet[$vehicle->id] = array('vehicle_id' => $vehicle->id,'driver_name' => 
                    $driverName, 'driver_id' => $driverId, 'registration' => $vehicle->registration,'latitude' => $latestTelematics['lat'], 'longitude' => $latestTelematics['lon'], 'markerType' => $vehicleMarkerIconType, 'vehicleTypeId'=>$vehicle->type->id,'userName' => $vehicle->creator,
                    'regionId' => $vehicle->vehicle_region_id, 'vehicleStatus'=>$vehicleStatus);
            }
            else {
                $vehicleMarkerIconType = $vehicle->type->vehicle_subcategory == null? ($vehicle->type->vehicle_category == 'hgv' ? 'hgv' : 'van') : $vehicle->type->vehicle_subcategory;
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_stopped';
                $vehicleStatus = "stopped";
                $driverName = $lastTelematicsJourney == null || $lastTelematicsJourney->user == null ? 'Driver Unknown' : $lastTelematicsJourney->user->first_name;
                if ($lastTelematicsJourney != null && $lastTelematicsJourney->user != null && $lastTelematicsJourney->user->id == env('SYSTEM_USER_ID')) {
                    $driverName = 'Driver Unknown';
                }
                $vehiclesOnFleet[$vehicle->id] = array('vehicle_id' => $vehicle->id,'driver_name' => $driverName, 'driver_id' => $driverId, 'registration' => $vehicle->registration,'latitude' => '51.490433', 'longitude' => '-0.262539', 'markerType' => $vehicleMarkerIconType,  'vehicleTypeId'=>$vehicle->type->id, 'userName' => $vehicle->creator,'regionId' => $vehicle->vehicle_region_id, 'vehicleStatus'=>$vehicleStatus);
            }
        }
        $response['vehiclesOnFleet'] = $vehiclesOnFleet;
        $response['tilesdata'] = $vehiclesOnFleet;
        return $vehiclesOnFleet;
    }
 
*/

    public function getAllVehiclesOnFleet()
    {
        $userRegions = [];
        if(Auth::check()) {
            $userRegions = Auth::user()->regions->lists('id')->toArray();
        }

        $allVehiclesOnFleet = Vehicle::select('vehicle_type_id', 'telematics_latest_journey_id','telematics_ns','telematics_lat','telematics_lon','vehicles.id','registration','vehicle_subcategory','vehicle_category','vehicle_region_id', 'nominated_driver')
                                                ->with('type')
                                                ->join('vehicle_types as vt', 'vt.id', '=', 'vehicles.vehicle_type_id')
                                                ->where('is_telematics_enabled','1')
                                                //->whereIn('vehicle_region_id', $userRegions)
                                                //->whereIn('registration',$sRegList)
                                                ->where('telematics_lat','!=',0)
                                                ->where('telematics_lon','!=',0)
                                                ->whereNotNull('telematics_lat')
                                                ->whereNotNull('telematics_lon');
        $lastVehicleJourneys = TelematicsJourneys::join('vehicles as v','v.telematics_latest_journey_id','=','telematics_journeys.id');

        if(!empty($userRegions)) {
            $allVehiclesOnFleet = $allVehiclesOnFleet->whereIn('vehicle_region_id', $userRegions);
            $lastVehicleJourneys = $lastVehicleJourneys->whereIn('v.vehicle_region_id', $userRegions);
        }
        $allVehiclesOnFleet = $allVehiclesOnFleet->get();
        //print_r($allVehiclesOnFleet);exit;
        $lastVehicleJourneys = $lastVehicleJourneys->get();

        $lastVehicleJourneyUsers = [];
        foreach($lastVehicleJourneys as $journey){
            $lastVehicleJourneyUsers[$journey->vrn] = $journey->user()->first();
        }

        $vehiclesOnFleet = [];
        $vehicleLatestMarkerList = [];
        $totalVehicles = $stoppedVehicles = $idleVehicles = $runningVehicles = 0;
        foreach($allVehiclesOnFleet as $fleetVehicle){
            $vehicleStatus = "stopped";
            $latestJourneyUser = empty($lastVehicleJourneyUsers) ? null: array_key_exists($fleetVehicle->registration,$lastVehicleJourneyUsers)?$lastVehicleJourneyUsers[$fleetVehicle->registration]:null;

            // $driverName =  $latestJourneyUser == null ? 'Driver Unknown' : $latestJourneyUser->first_name;
            // $driverId = $latestJourneyUser == null ? env('SYSTEM_USER_ID') : $latestJourneyUser->id;
            $driverName = $fleetVehicle->nominatedDriver ? $fleetVehicle->nominatedDriver->first_name.' '.$fleetVehicle->nominatedDriver->last_name : 'Unassigned driver';
            $driverId = $fleetVehicle->nominatedDriver ? $fleetVehicle->nominatedDriver->id : env('SYSTEM_USER_ID');

            $vehicleMarkerIconType = $fleetVehicle->type->vehicle_subcategory == null? ($fleetVehicle->type->vehicle_category == 'hgv' ? 'hgv' : 'van') : $fleetVehicle->type->vehicle_subcategory;

            $idlingEvents = config('config-variables.idling_events');
            $movingEvents = config('config-variables.moving_events');
            $stoppedEvents = config('config-variables.stopped_events');
            $startEvents = config('config-variables.start_events');
            //if ($fleetVehicle->telematics_ns == 'tm8.gps.ign.off' || $fleetVehicle->telematics_ns == 'tm8.jny.sum' || $fleetVehicle->telematics_ns == 'tm8.jny.sum.ex1' || $fleetVehicle->telematics_ns == 'tm8.gps.heartbeat' || $fleetVehicle->telematics_ns == 'tm8.gps.jny.end') {
            if (in_array($fleetVehicle->telematics_ns, $stoppedEvents)) {
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_stopped';
                $stoppedVehicles = $stoppedVehicles+1;
            } 
            //elseif ($fleetVehicle->telematics_ns == 'tm8.gps.idle.start' OR $fleetVehicle->telematics_ns == 'tm8.gps.idle.ongoing' OR $fleetVehicle->telematics_ns == 'tm8.gps.exces.idle') {
            elseif (in_array($fleetVehicle->telematics_ns, $idlingEvents)) {
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_idling';
                $vehicleStatus = "idling";
                $idleVehicles = $idleVehicles+1;
            } elseif (in_array($fleetVehicle->telematics_ns, $movingEvents) || in_array($fleetVehicle->telematics_ns, $startEvents)) {
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_moving';
                $vehicleStatus = "moving";
                $runningVehicles = $runningVehicles+1;
            }

            $vehiclesOnFleet[$fleetVehicle->id] = array('vehicle_id' => $fleetVehicle->id,'driver_name' => 
                    $driverName, 'driver_id' => $driverId, 'registration' => $fleetVehicle->registration,'latitude' => $fleetVehicle->telematics_lat, 'longitude' => $fleetVehicle->telematics_lon, 'markerType' => $vehicleMarkerIconType, 'vehicleTypeId'=>$fleetVehicle->type->id,'regionId' => $fleetVehicle->vehicle_region_id, 'vehicleStatus'=>$vehicleStatus);

            $vehicleLatestMarkerDetails['vrn'] = $fleetVehicle->registration;
            $vehicleLatestMarkerDetails['iconType'] = $vehicleMarkerIconType;
            array_push($vehicleLatestMarkerList,$vehicleLatestMarkerDetails);

        }



        $totalVehicles = $stoppedVehicles + $idleVehicles + $runningVehicles;
        $telematicsData['total_vehicles'] = $totalVehicles;
        $telematicsData['stopped_vehicles'] = $stoppedVehicles;
        $telematicsData['idle_vehicles'] = $idleVehicles;
        $telematicsData['running_vehicles'] = $runningVehicles;
        $telematicsData['vehicleLatestMarkerList'] = $vehicleLatestMarkerList;

        $resp['telematicsData'] = $telematicsData;
        $resp['vehiclesOnFleet'] = $vehiclesOnFleet;

        return $resp;
    }


    private function calcDirection($degree){
        if ( ($degree>=338 && $degree<=360) ||($degree>=0 && $degree<=22) ) {
            return 'North';
        }
        if ($degree>=23 && $degree<=75) {
            return 'North East';
        }
        if ($degree>=76 && $degree<=112) {
            return 'East';
        }
        if ($degree>=113 && $degree<=157) {
            return 'South East';
        }
        if ($degree>=158 && $degree<=202) {
            return 'South';
        }
        if ($degree>=203 && $degree<=247) {
            return 'South West';
        }
        if ($degree>=248 && $degree<=292) {
            return 'West';
        }
        if ($degree>=293 && $degree<=337) {
            return 'North West';
        }
    }

    private function calculate_vars($sRegList = null,$from = null){
        $userRegions = Auth::user()->regions->lists('id')->toArray(); 
        $telematicsData = array();
        
        $telematicsJourneyDetailsLatestEntry = Vehicle::select('telematics_ns','telematics_lat','telematics_lon','vehicles.id','registration','vehicle_subcategory','vehicle_category')
                                                ->join('vehicle_types as vt', 'vt.id', '=', 'vehicles.vehicle_type_id')                                                
                                                ->where('is_telematics_enabled','1')
                                                ->whereNotNull('telematics_lat')
                                                ->whereNotNull('telematics_lon');
        if(!empty($userRegions)) {
            $telematicsJourneyDetailsLatestEntry = $telematicsJourneyDetailsLatestEntry->whereIn('vehicle_region_id', $userRegions);
        }
	    if($sRegList != null && $from != null){
            $telematicsJourneyDetailsLatestEntry = $telematicsJourneyDetailsLatestEntry->whereIn('registration',$sRegList);
        }


        $telematicsJourneyDetailsLatestEntry = $telematicsJourneyDetailsLatestEntry->get();
        $totalVehicles = $stoppedVehicles = $idleVehicles = $runningVehicles = 0;
        $vehicleLatestMarkerList = [];
        foreach ($telematicsJourneyDetailsLatestEntry as $key => $value) {
            $vehicleMarkerIconType = 'car';
            if( $value->telematics_ns != '') {
                $vehicleMarkerIconType = $value->vehicle_subcategory == null? ( $value->vehicle_category == 'hgv' ? 'hgv' : 'van') : $value->vehicle_subcategory;
            } else {
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_stopped';
            }
            
            //if($value->telematics_ns == 'tm8.gps.ign.off' || $value->telematics_ns == 'tm8.gps.heartbeat' || $value->telematics_ns == 'tm8.jny.sum.ex1' ||  $value->telematics_ns == 'tm8.jny.sum' ||  $value->telematics_ns == 'tm8.gps.jny.end') {
            $idlingEvents = config('config-variables.idling_events');
            $movingEvents = config('config-variables.moving_events');
            $stoppedEvents = config('config-variables.stopped_events');
            $startEvents = config('config-variables.start_events');
            if (in_array($value->telematics_ns,$stoppedEvents)) {
                $stoppedVehicles = $stoppedVehicles+1;
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_stopped';
            }
	        //elseif($value->telematics_ns == 'tm8.gps.idle.start' || $value->telematics_ns == 'tm8.gps.idle.ongoing' || $value->telematics_ns == 'tm8.gps.exces.idle') {
            elseif (in_array($value->telematics_ns,$idlingEvents)) {
                $idleVehicles = $idleVehicles+1;
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_idling';
            }
	        elseif (in_array($value->telematics_ns,$movingEvents) || in_array($value->telematics_ns,$startEvents)){
                $runningVehicles = $runningVehicles+1;
                $vehicleMarkerIconType = $vehicleMarkerIconType.'_moving';
            }

            $vehicleLatestMarkerDetails['vrn'] = $value->registration;
            $vehicleLatestMarkerDetails['iconType'] = $vehicleMarkerIconType;
            array_push($vehicleLatestMarkerList,$vehicleLatestMarkerDetails);
        }

        $totalVehicles = $stoppedVehicles + $idleVehicles + $runningVehicles;
        $telematicsData['total_vehicles'] = $totalVehicles;
        $telematicsData['stopped_vehicles'] = $stoppedVehicles;
        $telematicsData['idle_vehicles'] = $idleVehicles;
        $telematicsData['running_vehicles'] = $runningVehicles;
        $telematicsData['vehicleLatestMarkerList'] = $vehicleLatestMarkerList;

        
        return $telematicsData;
    }

    private function getUserTrendDetails($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $typeFilterValue) {
        $carbonObjStartDate = Carbon::parse($startDate);
        $carbonObjEndDate = Carbon::parse($endDate);
        $diff = $carbonObjStartDate->diffInDays($carbonObjEndDate);
        $trendStartDate = $carbonObjStartDate->subDays($diff);
        $trendEndDate = $carbonObjEndDate->subDays($diff);

        return $this->obj_telematics_journeys->getTelematicsDetailsByStartDate($trendStartDate, $trendEndDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $typeFilterValue);
    }
    public function getTrendScore(Request $request)
    {
        $data = $request->all();
        //$startDate = $data['startDate'];
        //$endDate = $data['endDate'];
        $sdate = request()->get('startDate');
        $edate = request()->get('endDate');
        $startDate = Carbon::createFromFormat('d/m/Y H:i:s',  $sdate)->startOfDay(); 
        $endDate = Carbon::createFromFormat('d/m/Y H:i:s',  $edate)->endOfDay();
        $userFilterValue = '';
        if (request()->has('userFilterValue')) {
            $userFilterValue = request()->get('userFilterValue');
        }
        $registrationFilterValue = '';
        if (request()->has('registrationFilterValue')) {
            $registrationFilterValue = request()->get('registrationFilterValue');
        }
        $regionFilterValue = '';
        if (request()->has('regionFilterValue')) {
            $regionFilterValue = request()->get('regionFilterValue');
        } else {
            // $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
        }

        $userDetails = $this->obj_telematics_journeys->getTelematicsDetailsByStartDate($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $request->typeFilterValue);
        $score_data_array = isset($data['scoreData']) ? $data['scoreData'] : [];

        // trend details code start here
        $userTrendDetails = $this->getUserTrendDetails($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $request->typeFilterValue);
        $trendJourneyIds = "";
        foreach ($userTrendDetails as $key => $trendUser) {
            if ($trendJourneyIds == "") {
                $trendJourneyIds = $trendUser->journey_ids;
            }
            else{
                $trendJourneyIds = $trendJourneyIds.",".$trendUser->journey_ids;
            }
        }

        $trend_score_data['safety_score'] = 0;
        $trend_score_data['acceleration_score'] = 0;
        $trend_score_data['braking_score'] = 0;
        $trend_score_data['cornering_score'] = 0;
        $trend_score_data['speeding_score'] = 0;
        $trend_score_data['idle'] = 0;
        $trend_score_data['rpm'] = 0;
        $trend_score_data['fuel'] = 0;
        $trend_score_data['co2'] = 0;
        $trend_score_data['efficiencyScore'] = 0;

        if ($trendJourneyIds != "") {
            $trendScoreData = $this->getTrendScoreData($trendJourneyIds);

            $trend_score_data['safety_score'] = $trendScoreData['safety'] == null? 100 : $trendScoreData['safety'];
            $trend_score_data['acceleration_score'] = $trendScoreData['acceleration'] == null? 100 : $trendScoreData['acceleration'];
            $trend_score_data['braking_score'] = $trendScoreData['braking'] == null? 100 : $trendScoreData['braking'];
            $trend_score_data['cornering_score'] = $trendScoreData['cornering'] == null? 100 : $trendScoreData['cornering'];
            $trend_score_data['speeding_score'] = $trendScoreData['speeding'] == null? 100 : $trendScoreData['speeding'];
            $trend_score_data['idle'] = $trendScoreData['idle'] == null? 100 : $trendScoreData['idle'];
            $trend_score_data['rpm'] = $trendScoreData['rpm'] == null? 100 : $trendScoreData['rpm'];
            $trend_score_data['fuel'] = $trendScoreData['fuel'] == null? 100 : $trendScoreData['fuel'];
            $trend_score_data['co2'] = $trendScoreData['co2'] == null? 100 : $trendScoreData['co2'];
            $trend_score_data['efficiencyScore'] = $trendScoreData['efficiency'] == null? 100 : $trendScoreData['efficiency'];
        }
        $total_score_data['safety_score'] = 0;
        $total_score_data['acceleration_score'] = 0;
        $total_score_data['braking_score'] = 0;
        $total_score_data['cornering_score'] = 0;
        $total_score_data['speeding_score'] = 0;
        $total_score_data['idle'] = 0;
        $total_score_data['rpm'] = 0;
        $total_score_data['fuel'] = 0;
        $total_score_data['co2'] = 0;
        $total_score_data['efficiencyScore'] = 0;
        $index = 0;
        foreach ($score_data_array as $key => $score_data) {
                $total_score_data['safety_score'] = $total_score_data['safety_score'] + (isset($score_data['safety_score']) ? $score_data['safety_score'] : 0);
                $total_score_data['acceleration_score'] = $total_score_data['acceleration_score'] + (isset($score_data['acceleration_score']) ? $score_data['acceleration_score'] : 0);
                $total_score_data['braking_score'] = $total_score_data['braking_score'] + (isset($score_data['braking_score']) ? $score_data['braking_score'] : 0);
                $total_score_data['cornering_score'] = $total_score_data['cornering_score'] + (isset($score_data['cornering_score']) ? $score_data['cornering_score'] : 0);
                $total_score_data['speeding_score'] = $total_score_data['speeding_score'] + (isset($score_data['speeding_score']) ? $score_data['speeding_score'] : 0);
                $total_score_data['idle'] = $total_score_data['idle'] + (isset($score_data['idle']) ? $score_data['idle'] : 0);
                $total_score_data['rpm'] = $total_score_data['rpm'] + (isset($score_data['rpm']) ? $score_data['rpm'] : 0);
                $total_score_data['fuel'] = $total_score_data['fuel'] + (isset($score_data['fuel']) ? $score_data['fuel'] : 0);
                $total_score_data['co2'] = $total_score_data['co2'] + (isset($score_data['co2']) ? $score_data['co2'] : 0);
                $total_score_data['efficiencyScore'] = $total_score_data['efficiencyScore'] + (isset($score_data['efficiencyScore']) ? $score_data['efficiencyScore'] : 0);
        }

        $final_trend_data['trend_safety_score'] = number_format(($total_score_data['safety_score'] - $trend_score_data['safety_score'])/100, 2, '.', '');
        $final_trend_data['trend_acceleration_score'] = number_format(($total_score_data['acceleration_score'] - $trend_score_data['acceleration_score'])/100, 2, '.', '');
        $final_trend_data['trend_braking_score'] = number_format(($total_score_data['braking_score'] - $trend_score_data['braking_score'])/100, 2, '.', '');
        $final_trend_data['trend_cornering_score'] = number_format(($total_score_data['safety_score'] - $trend_score_data['safety_score'])/100, 2, '.', '');($total_score_data['cornering_score'] - $trend_score_data['cornering_score'])/100;
        $final_trend_data['trend_speeding_score'] = number_format(($total_score_data['speeding_score'] - $trend_score_data['speeding_score'])/100, 2, '.', '');
        $final_trend_data['trend_idle'] = number_format(($total_score_data['idle'] - $trend_score_data['idle'])/100, 2, '.', '');
        $final_trend_data['trend_rpm'] = number_format(($total_score_data['rpm'] - $trend_score_data['rpm'])/100, 2, '.', '');
        $final_trend_data['trend_fuel'] = number_format(($total_score_data['fuel'] - $trend_score_data['fuel'])/100, 2, '.', '');
        $final_trend_data['trend_co2'] = number_format(($total_score_data['co2'] - $trend_score_data['co2'])/100, 2, '.', '');
        $final_trend_data['trend_efficiencyScore'] = number_format(($total_score_data['efficiencyScore'] - $trend_score_data['efficiencyScore'])/100, 2, '.', '');

        return response()->json($final_trend_data);
    }

    public function getSafetyAndEfficiencyScore(Request $request)
    {
        $commonHelper = new Common();
        $score_data = array();

        $data = $request->all();
        //print_r($data);exit();
        $startDate = isset($data['startDate'])?$data['startDate']:null;
        $endDate = isset($data['endDate'])?$data['endDate']:null;
        $userFilterValue = '';
        if (request()->has('userFilterValue')) {
            $userFilterValue = request()->get('userFilterValue');
        }
        $registrationFilterValue = '';
        if (request()->has('registrationFilterValue')) {
            $registrationFilterValue = request()->get('registrationFilterValue');
        }
        $regionFilterValue = '';
        if (request()->has('regionFilterValue')) {
            $regionFilterValue = request()->get('regionFilterValue');
        } else {
            // $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
        }

        $userDetails = $this->obj_telematics_journeys->getTelematicsDetailsByStartDate($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $request->typeFilterValue);

        //////trend details code start here
        //$userTrendDetails = $this->getUserTrendDetails($startDate, $endDate, $userFilterValue, $registrationFilterValue, $regionFilterValue, $request->typeFilterValue);
        $trendJourneyIds = "";
        foreach ($userDetails as $key => $trendUser) {
            if ($trendJourneyIds == "") {
                $trendJourneyIds = $trendUser->journey_ids;
            }
            else{
                $trendJourneyIds = $trendJourneyIds.",".$trendUser->journey_ids;
            }
        }

        $trend_score_data['safety_score'] = 0;
        $trend_score_data['acceleration_score'] = 0;
        $trend_score_data['braking_score'] = 0;
        $trend_score_data['cornering_score'] = 0;
        $trend_score_data['speeding_score'] = 0;
        $trend_score_data['idle'] = 0;
        $trend_score_data['rpm'] = 0;
        $trend_score_data['fuel'] = 0;
        $trend_score_data['co2'] = 0;
        $trend_score_data['efficiencyScore'] = 0;

        if ($trendJourneyIds != "") {
            $userVehicleId = null;
            if($registrationFilterValue != '') {
                $userVehicleId = $userDetails[0]->vehicle_id;
            }
            $trendScoreData = $this->getTrendScoreData($trendJourneyIds, null, $userFilterValue, $userVehicleId);

            $trend_score_data['safety_score'] = $trendScoreData['safety'] == null? 100 : $trendScoreData['safety'];
            $trend_score_data['acceleration_score'] = $trendScoreData['acceleration'] == null? 100 : $trendScoreData['acceleration'];
            $trend_score_data['braking_score'] = $trendScoreData['braking'] == null? 100 : $trendScoreData['braking'];
            $trend_score_data['cornering_score'] = $trendScoreData['cornering'] == null? 100 : $trendScoreData['cornering'];
            $trend_score_data['speeding_score'] = $trendScoreData['speeding'] == null? 100 : $trendScoreData['speeding'];
            $trend_score_data['idle'] = $trendScoreData['idle'] == null? 100 : $trendScoreData['idle'];
            $trend_score_data['rpm'] = $trendScoreData['rpm'] == null? 100 : $trendScoreData['rpm'];
            $trend_score_data['fuel'] = $trendScoreData['fuel'] == null? 0 : $trendScoreData['fuel'];
            $trend_score_data['co2'] = $trendScoreData['co2'] == null? 0 : $trendScoreData['co2'];
            $trend_score_data['efficiencyScore'] = $trendScoreData['efficiency'] == null? 100 : $trendScoreData['efficiency'];
        }
        // trend details code end here
        $index = 0;
        $sheetArray=array();     
        $sheet=array();
        foreach ($userDetails as $key => $user) {
            $score_data[$index]['journey_ids'] = $user->journey_ids;
            $score_data[$index]['registration'] = $user->vehicle->registration;
            if ($user->user) {
                if ($user->user->id == env('SYSTEM_USER_ID')) {
                    $score_data[$index]['user'] = config('config-variables.telematicsSystemUserVisibleName.FULL');
                }
                else{
                    $score_data[$index]['user'] = $user->user->first_name . ' ' . $user->user->last_name;
                }
                $score_data[$index]['user_id'] = $user->user->id;
            }
            else{
                $score_data[$index]['user'] = config('config-variables.telematicsSystemUserVisibleName.FULL');
                $score_data[$index]['user_id'] = env('SYSTEM_USER_ID');
            }

            //$scoreData = $this->getUserVehicleScoreData($user->journey_ids,$user->user_id,$user->vehicle_id);
	    if ($request->typeFilterValue == 'user') {
                $scoreData = $this->getUserVehicleScoreData($user->journey_ids,$user->user_id);
            }
            else{
                $scoreData = $this->getUserVehicleScoreData($user->journey_ids,null,$user->vehicle_id);
            }
            $score_data[$index]['safety_score'] = $scoreData['safety'] == null? 100 : $scoreData['safety'];
            $score_data[$index]['acceleration_score'] = $scoreData['acceleration'] == null? 100 : $scoreData['acceleration'];
            $score_data[$index]['braking_score'] = $scoreData['braking'] == null? 100 : $scoreData['braking'];
            $score_data[$index]['cornering_score'] = $scoreData['cornering'] == null? 100 : $scoreData['cornering'];
            $score_data[$index]['speeding_score'] = $scoreData['speeding'] == null? 100 : $scoreData['speeding'];
            $score_data[$index]['idle'] = $scoreData['idle'] == null? 100 : $scoreData['idle'];
            $score_data[$index]['rpm'] = $scoreData['rpm'] == null? 100 : $scoreData['rpm'];
            $score_data[$index]['fuel'] = $scoreData['fuel'] == null? 0 : $scoreData['fuel'];
            $score_data[$index]['co2'] = $scoreData['co2'] == null? 0 : $scoreData['co2'];
            $score_data[$index]['gps_distance'] = $scoreData['gps_distance'] == null? 0 : $scoreData['gps_distance'] * 0.00062137;
            $score_data[$index]['driving_time'] = $scoreData['engine_duration'] == null? 0 : $scoreData['engine_duration'];
            $score_data[$index]['efficiencyScore'] = $scoreData['efficiency'] == null? 100 : $scoreData['efficiency'];
            $index++;
        }

        if(isset($data['isExport']) && $data['isExport']!=null && $data['isExport']=='yes'){
            $sheet=array();
            // $headingLabelArrayFilterRange="A2:G2";
            $headingLabelArrayFilterRange="A2:F2";
            if(!empty($score_data)){
                if(isset($request->scoreType) && !empty($request->scoreType) && $request->scoreType=='safety'){
                    $excelFileDetail=array(
                        'title' => "Safety Score"
                        );

                    if($data['typeFilterValue'] == 'user') {
                        $sheet['labelArray'] = ['Driver' ,'Safety Score', 'Acceleration Score','Braking Score', 'Cornering Score', 'Speeding Score'];
                        $onlyGet=array('user','safety_score','acceleration_score','braking_score','cornering_score','speeding_score');
                    } else {
                        $sheet['labelArray'] = ['Registration' ,'Safety Score', 'Acceleration Score','Braking Score', 'Cornering Score', 'Speeding Score'];
                        $onlyGet=array('registration','safety_score','acceleration_score','braking_score','cornering_score','speeding_score');
                    }


                    $sheet['otherParams'] = [
                        'sheetName' => "Safety Score"
                    ];

                    if(!isset($data['sortOrderColumnName'])){
                        $data['sortOrderColumnName']='safety_score';
                    }
                    if(isset($data['sortOrder']) &&  $data['sortOrder']=='desc'){
                        $score_data=collect($score_data)->sortByDesc($data['sortOrderColumnName'])->toArray();
                    }else{
                        $score_data=collect($score_data)->sortBy($data['sortOrderColumnName'])->toArray();
                    }

                    foreach($score_data as $sd){
                        $sheet['dataArray'][] = array_only($sd, $onlyGet);
                    }
                }else{
                    $excelFileDetail=array(
                        'title' => "Efficiency Score"
                        );

                    if($data['typeFilterValue'] == 'user') {
                        $sheet['labelArray'] = ['Driver' ,'Efficiency Score', 'RPM','Idle', 'Distance(Miles)', 'Driving Time(HH:MM)','Fuel(Litres)','CO2(Kg)'];
                    } else {
                        $sheet['labelArray'] = ['Registration','Efficiency Score', 'RPM','Idle', 'Distance(Miles)', 'Driving Time(HH:MM)','Fuel(Litres)','CO2(Kg)'];
                    }
                    $sheet['otherParams'] = [
                        'sheetName' => "Efficiency Score"
                    ];
                    $sheet['columnsToAlign'] = ['A'=>'left','B'=>'left','C'=>'right','D'=>'right', 'E'=>'right', 'F'=>'right', 'G'=>'right', 'H'=>'right', 'I'=>'right'];
                    
                    if(!isset($data['sortOrderColumnName'])){
                        $data['sortOrderColumnName']='efficiencyScore';
                    }
                    if(isset($data['sortOrder']) &&  $data['sortOrder']=='desc'){
                        $score_data=collect($score_data)->sortByDesc($data['sortOrderColumnName'])->toArray();
                    }else{
                        $score_data=collect($score_data)->sortBy($data['sortOrderColumnName'])->toArray();
                    }

                    foreach($score_data as $sd){
                        if($data['typeFilterValue'] == 'user') {
                            $sheet['dataArray'][]=array('user'=>$sd['user'], 'efficiencyScore'=>$sd['efficiencyScore'], 'rpm'=>$sd['rpm'], 'idle'=>$sd['idle'], 'gps_distance'=>$sd['gps_distance'], 'driving_time'=>secondsToHourMinute($sd['driving_time']), 'fuel'=>$sd['fuel'], 'co2'=>$sd['co2']);
                        } else {
                            $sheet['dataArray'][]=array('registration'=>$sd['registration'], 'efficiencyScore'=>$sd['efficiencyScore'], 'rpm'=>$sd['rpm'], 'idle'=>$sd['idle'], 'gps_distance'=>$sd['gps_distance'], 'driving_time'=>secondsToHourMinute($sd['driving_time']), 'fuel'=>$sd['fuel'], 'co2'=>$sd['co2']);
                        }
                    }
                }
        
                //echo json_encode($sheet['dataArray']); exit;
                $sheet['columnFormat']=[
                    'B'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
                    'C'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
                    'D'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
                    'E'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
                ];
                if(isset($request->scoreType) && !empty($request->scoreType) && $request->scoreType=='efficiency'){
                    $sheet['columnFormat']['G']=\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
                    $sheet['columnFormat']['H']=\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
                    $headingLabelArrayFilterRange="A2:H2";
                }else{
                    $sheet['columnFormat']['F']=\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
                }

                if($startDate!=null && $endDate!=null){
                    $sheet['headingLabelArray']=['Date Range',\Carbon\Carbon::parse($commonHelper->convertBstToUtc($startDate))->format('d F Y').' - '.\Carbon\Carbon::parse($commonHelper->convertBstToUtc($endDate))->format('d F Y')];
                    $sheet['headingLabelArrayFilterRange']=$headingLabelArrayFilterRange;
                }
                
                array_push($sheetArray, $sheet);
                $_commonHelperObj=new Common;
                //$exportFile=$_commonHelperObj->downloadDesktopExcel($excelFileDetail, $sheetArray, 'xlsx', 'yes');
                $exportFile=$_commonHelperObj->downloadDesktopExcel($excelFileDetail, $sheetArray, 'xlsx', 'no','no','no');
                return base64_encode($exportFile);
            }
           return '';
        }else{
            $score_data[$index] = $trend_score_data;
            return response()->json($score_data);
        }
    }

    public function downloadAndRemoveFile(Request $request){
        $thisFile=base64_decode($request->exportFile);
         return response()->download($thisFile)->deleteFileAfterSend(true);
        
    }

    public function getJourneyData(Request $request) {
        return GridEncoder::encodeRequestedData(new TelematicsJourneysRepository($request), $request->all());
    }
    
    public function getVehicleData(Request $request) {
        return GridEncoder::encodeRequestedData(new TelematicsVehiclesRepository($request), $request->all());
    }

    public function getTelematicsLiveTabVehicleData(Request $request){
        $query=new TelematicsVehiclesRepository();
        $data=$query->retrieveVehicleList($request);
        if (isset($request->vRegistration) && !empty($request->vRegistration)) {
            $singleRowFetch=true;
        }else{
            $singleRowFetch=false;
        }
        if (isset($request->isListingBlock) && !empty($request->isListingBlock) && $request->isListingBlock=='yes') {
            $showHideBlock='block';
        }else{
            $showHideBlock='none';
        }
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.vehicles.index',compact('data','singleRowFetch','showHideBlock'))->render();
        $result=array(
            'viewHtml'=>$viewHtml
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    public function getTelematicsLiveTabVehicleDetail(Request $request)
    {
        $vehicle = Vehicle::find($request->vehicleId);
        $query = new TelematicsVehiclesRepository();
        $data=$query->fetchVehicleDetailByVehicle($request);
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.vehicles.vehicle_details',compact('data', 'vehicle'))->render();
        $result=array(
            'registration'=> $vehicle->registration,
            'viewHtml'=>$viewHtml
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    public function getTelematicsLiveTabUserData(Request $request){
        $query=new UsersRepository();
        $data=$query->retriveLiveTabUserList($request);
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.users.index',compact('data'))->render();
        $result=array(
            'viewHtml'=>$viewHtml
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }
    
    public function getTelematicsLiveTabUserVehicleDetail(Request $request){
        $query=new TelematicsVehiclesRepository();
        $data=$query->fetchVehicleDetailByVehicle($request);
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.users.user_vehicle_details',compact('data'))->render();
        $result=array(
            'registration'=>isset($data->registration)?$data->registration:'',
            'viewHtml'=>$viewHtml
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    public function getTelematicsLiveTabVehicleJourneyDetail(Request $request){
        $response=array('status'=>0,'data'=>array());
        $query= new TelematicsJourneysRepository();
        $data=$query->fetchVehicleJourneyDetail($request);
        $getSummary=[];
        $journeyCount=0; $incidentCount=0; $gps_distance=0; $engine_duration=0; $fuel=0; $co2=0;
        if(!empty($data)){
            foreach($data as $d){
                $incidentCount+=$d->incident_count;
                $journeyCount+=$d->journeyCount;
                $gps_distance+=$d->gps_distance;
                $engine_duration+=$d->engine_duration;
                $fuel+=$d->fuel;
                $co2+=$d->co2;
            }
            $result=array(
                'data'=>$data,
                'vrn'=>$data[0]->vehicleRegistration,
                'journeyCount'=>$journeyCount,
                'incidentCount'=>$incidentCount,
                'gps_distance'=>valueFormatter($gps_distance,false,2),
                'total_driving_time'=>readableTimeFomat($engine_duration),
                'fuel'=>valueFormatter($fuel,false,2),
                'co2'=>valueFormatter($co2,false,2),
            );
            $response=array('status'=>1,'data'=>$result);
        }
        return response()->json($response);
    }

    public function getTelematicsLiveTabVehicleJourneyDetailByLatLong(Request $request){
        $response=array('status'=>0,'data'=>array());
        $query= new TelematicsJourneysRepository();
        $data=$query->fetchVehicleJourneyDetailLatLong($request);
        if(!empty($data)){
            $response=array('status'=>1,'data'=>$data);
        }
        return response()->json($response);
    }

    public function getTelematicsLiveTabLocationCategoryList(Request $request){
        /* $query=LocationCategory::with(['location'=>function ($query) {
            $query->whereNotNull('id');
        }]); */
        $cl=20;
        $query=LocationCategory::with('location')->has('location');
        $locationCategoryId=(isset($request->locationCategoryId) && !empty($request->locationCategoryId))?$request->locationCategoryId:'';
        if(isset($locationCategoryId) && !empty($locationCategoryId)) {
            $query= $query->where('location_categories.id', $locationCategoryId);
        }
        if(isset($request->contentLimit)){
            $cl=$request->contentLimit;
        }
        $query->limit($cl);
        $data=$query->get();
        //Debug
        $locations=[];
        $locationsPluck=collect(array_pluck($data,'location'));
        $locationsValues=$locationsPluck->map(function($v,$k){
            return $v->map(function ($item, $key) {
                return array(
                    'id'=>$item->id,
                    'locationCategoryId'=>isset($item->location_category_id)?$item->location_category_id:null,
                    'locationName'=>$item->name,
                    'latitude'=>$item->latitude,
                    'longitude'=>$item->longitude,
                    ''
                );
            });
        });
        if(!empty($locationsValues)){
            $locations=array_collapse($locationsValues);
        }
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.locations.index',compact('data'))->render();
        $result=array(
            'viewHtml'=>$viewHtml,
            'locations'=>$locations
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    public function getTelematicsLiveTabCategoryLocationList(Request $request){
        $data=array();
        $query=new LocationRepository();
        $data=$query->fetchLocationByCategory($request);
        if(!empty($data)){
            $selectedLocation=collect($data)->pluck('id')->toArray();
        }else{
            $selectedLocation=[];
        }
        $category_name=isset($data[0]->category_name)?$data[0]->category_name:'NA';
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.locations.locations_list',compact('data','category_name'))->render();
        $result=array(
            'viewHtml'=>$viewHtml,
            'data'=>$data,
            'selectedLocation'=>$selectedLocation
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    public function getTelematicsLiveTabLocationDetail(Request $request){
        $data=array();
        $data=array();
        $query=new LocationRepository();
        $data=$query->fetchLocationById($request);
        $viewHtml = \Illuminate\Support\Facades\View::make('_partials.telematics.mapui.locations.location_info',compact('data'))->render();
        $result=array(
            'viewHtml'=>$viewHtml,
            'locationLatitude'=>$data->latitude,
            'locationLongitude'=>$data->longitude,
        );
        $response=array('status'=>1,'data'=>$result);
        return response()->json($response);
    }

    private function processJourneyData($data) {

        $finalData = array();
        if (count($data) > 0) {
            foreach ($data as $key => $journey) {
                $single = array();
                $single = $journey;
                $journeyTelematics = TelematicsJourneys::with('user')->whereHas('vehicle', function ($query) {
                                $query->where('is_telematics_enabled','=','1');
                            })->where('journey_id',$journey['journey_id'])->first();
                $driver = TelematicsJourneys::getDriverToShow($journeyTelematics->id);
                $single['user'] = $user = $driver['first_name'] ." ".$driver['last_name'];
                
                $single['gps_idle_duration']  = $single['gps_idle_duration'] == null || $single['gps_idle_duration'] == "" ? 0 : number_format($single['gps_idle_duration']/60,0);
                $single['co2']  = $single['co2'] == null || $single['co2'] == "" ? 0 : $single['co2'];
                $single['fuel']  = $single['fuel'] == null || $single['fuel'] == "" ? 0 : $single['fuel'];
                $single['mxmph'] = isset($single['mxmph']) ? $this->mpsToMph(number_format($single['mxmph'],2)) : 0;
                $single['avgmph'] = isset($single['avgmph']) ? $this->mpsToMph(number_format($single['avgmph'],2)) : 0;
                $single['gps_distance'] = $single['gps_distance'] == null? 0 : number_format($single['gps_distance']* 0.00062137,2);
                $single['start_time'] = Carbon::parse($single['start_time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
                $single['end_time'] = Carbon::parse($single['end_time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
                $single['registraion'] = $single['vrn'];
                array_push($finalData,$single);
            }
        }
        return $finalData;
    }

    public function getJourneyDetails(Request $request) {
        $journeyId = $request->journeyId;
        $journeyDetailsMain = TelematicsJourneyDetails::where('telematics_journey_id','=',$journeyId)->where('ns','!=','tm8.jny.sum.ex1')->orderBy('time')->get();
        $journeyDetails = $journeyDetailsMain->toArray();
        $telematicsJourney = $journeyDetailsMain->first()->telematicsJourneys()->first();
        //$user = $journeyDetailsMain->first()->telematicsJourneys()->first()->user()->first();
        $driver = TelematicsJourneys::getDriverToShow($telematicsJourney->id);
        $user = $driver['first_name'] ." ".$driver['last_name'];
        $miles = 0;
        $finalJourneyData = [];
        $incidents = [
            'tm8.dfb2.dec.l',
            'tm8.dfb2.acc.l',
            'tm8.dfb2.spdinc',
            'tm8.dfb2.cnrl.l',
            'tm8.dfb2.cnrr.l',
            'tm8.dfb2.rpm',
            'tm8.gps.idle.start',
            'tm8.gps.idle.end',
            'tm8.gps.heartbeat',
            'tm8.fnol'
        ];
        $incidentsData = [];
        $previousKey = 0;

        foreach ($journeyDetails as $key => $point) {
            if($point['lat'] !="" && $point['lon'] !="") {
                //$maxAccelaration  = $this->getSpeedLimit($point['lat'],$point['lon']);
                //$point['speed_limit'] = $maxAccelaration;
		          // $point['speed_limit'] = $point['speed'];
                $point['speed_limit'] = $point['street_speed'];
                $journeyDetails[$key] = $point;
                
                $class = 'entry js-point';
                if ($key != 0) {
                    $m = $this->calculateDistance((float)$journeyDetails[$previousKey]['lat'], (float)$journeyDetails[$previousKey]['lon'], (float)$point['lat'], (float)$point['lon'], 'M');
                    if (is_numeric($m)) {
                        $miles = $miles + $m;
                    }

                }
                if($key==0) {
                    $class .= " start-point";
                } elseif($key == count($journeyDetails)-1) {
                    $class .= " end-point";
                }
                $point['original_time'] = Carbon::parse($point['time'])->format('Y-m-d H:i:s');
                $point['time'] = Carbon::parse($point['time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
                $single = $point;
                
                $single['registration'] = $point['vrn'];
                $single['miles'] = $miles;
                $single['class'] = $class;
                $single['label'] = $this->getIncidentTypeFromNs($point['ns']);
                $single['driving'] = \Carbon\Carbon::parse($journeyDetails[0]['time'])->diffInMinutes(\Carbon\Carbon::parse($point['original_time']));

                $single['idling'] = readableTimeFomat($point['idle_duration']);
                $previousKey = $key;
                $single['is_incident'] = false;
                $single['journey_id'] = $journeyId;

                if (in_array($single['ns'],$incidents)) {
                    $single['is_incident'] = true;
                    $single['data'] = $single;
                    $single['data']['incident_type'] = $point['ns'];
                    $single['incident_type'] = $single['label'];
                    $single['user'] = $user;
                    if($point['post_code'] != ''){
                        $single['location'] = $point['street'].", ".$point['post_code'];
                    }
                    else{
                        $single['location'] = 'NA';
                    }
                    $single['latitude'] = $point['lat'];
                    $single['longitude'] = $point['lon'];
                    $single['icon'] = $this->getIconFromIncidentType($point['ns']);
                    $single['date'] = $point['time'];

                    array_push($incidentsData,$single);
                }

                array_push($finalJourneyData,$single);

            }

        }
        
        $html = \Illuminate\Support\Facades\View::make('_partials.telematics.journeyDetailsList',compact('finalJourneyData'))->render();

        $journeySummary = $telematicsJourney->toArray();

        $data = array();
        $data['total_gps_distance'] = 0;
        $data['total_driving_time'] = 0;
        $data['total_idling_time'] = 0;
        $data['odometer_start'] = 0;
        $data['odometer_end'] = 0;
        $data['vrn'] = '';

        if($journeySummary['gps_distance'] != 0){
            $data['total_gps_distance'] = number_format($journeySummary['gps_distance']* 0.00062137, 2);
        }
        if($journeySummary['engine_duration'] != 0){
            $data['total_driving_time'] = readableTimeFomat($journeySummary['engine_duration']);
        }
        if($journeySummary['gps_idle_duration'] != 0){
            $data['total_idling_time'] = readableTimeFomat($journeySummary['gps_idle_duration']);
        }
        $data['html'] = $html;
        $data['journeyData'] = $journeyDetails;
        $data['journeySummary'] = $telematicsJourney;
        $data['incidentData'] = $incidentsData;
        $data['odometer_start'] = $journeySummary['odometer_start'];
        $data['odometer_end'] = $journeySummary['odometer_end'];
        $data['vrn'] = $journeySummary['vrn'];
        $data['driver_name'] = $user;

        return $data;
    }

    public function getMultipleJourneyDetails(Request $request) {
        $_journeyIds = $request->journeyIds;
        $journeyDetailsMain = TelematicsJourneyDetails::whereIn('telematics_journey_id',$_journeyIds)->where('ns','!=','tm8.jny.sum.ex1')->orderBy('time')->get();
        
        $journeyDetails = $journeyDetailsMain->toArray();
        //$telematicsJourney = $journeyDetailsMain->first()->telematicsJourneys()->first();
        $telematicsJourneyCollection=collect(TelematicsJourneys::whereIn('id',$_journeyIds)->get()->toArray());
        //$user = $journeyDetailsMain->first()->telematicsJourneys()->first()->user()->first();
        $driver = TelematicsJourneys::getDriverToShow($journeyDetailsMain->first()->telematicsJourneys()->first()->id);
        $user = $driver['first_name'] ." ".$driver['last_name'];
        
        $finalJourneyData = [];
        $incidents = [
            'tm8.dfb2.dec.l',
            'tm8.dfb2.acc.l',
            'tm8.dfb2.spdinc',
            'tm8.dfb2.cnrl.l',
            'tm8.dfb2.cnrr.l',
            'tm8.dfb2.rpm',
            'tm8.gps.idle.start',
            'tm8.gps.idle.end',
            'tm8.gps.heartbeat'
        ];
        
        $masterJourneyDetailByIdArray=[];
        if(count($_journeyIds)>0){
            foreach($_journeyIds as $_jId){
                $masterJourneyDetailByIdArray[$_jId]=[];
            }
        
            foreach ($journeyDetails as $key => $point) {
                $incidentsData = [];
                $previousKey = 0;
                $miles = 0;
                if($point['lat'] !="" && $point['lon'] !="") {
                    //$maxAccelaration  = $this->getSpeedLimit($point['lat'],$point['lon']);
                    //$point['speed_limit'] = $maxAccelaration;
                    // $point['speed_limit'] = $point['speed'];
                    $point['speed_limit'] = $point['street_speed'];
                    $journeyDetails[$key] = $point;
                    
                    $class = 'entry js-point';
                    if ($key != 0) {
                        $m = $this->calculateDistance((float)$journeyDetails[$previousKey]['lat'], (float)$journeyDetails[$previousKey]['lon'], (float)$point['lat'], (float)$point['lon'], 'M');
                        if (is_numeric($m)) {
                            $miles = $miles + $m;
                        }
        
                    }
                    if($key==0) {
                        $class .= " start-point";
                    } elseif($key == count($journeyDetails)-1) {
                        $class .= " end-point";
                    }
                    $point['original_time'] = Carbon::parse($point['time'])->format('Y-m-d H:i:s');
                    $point['time'] = Carbon::parse($point['time'])->setTimezone(config('config-variables.displayTimezone'))->format('Y-m-d H:i:s');
                    $single = $point;
                    
                    $single['registration'] = $point['vrn'];
                    $single['miles'] = $miles;
                    $single['class'] = $class;
                    $single['label'] = $this->getIncidentTypeFromNs($point['ns']);
                    $single['driving'] = \Carbon\Carbon::parse($journeyDetails[0]['time'])->diffInMinutes(\Carbon\Carbon::parse($point['original_time']));
        
                    $single['idling'] = readableTimeFomat($point['idle_duration']);
                    $previousKey = $key;
                    $single['is_incident'] = false;
                    //$single['journey_id'] = $journeyId;
                    $single['journey_id'] = $point['telematics_journey_id'];
        
                    if (in_array($single['ns'],$incidents)) {
                        $single['is_incident'] = true;
                        $single['data'] = $single;
                        $single['data']['incident_type'] = $point['ns'];
                        $single['incident_type'] = $single['label'];
                        $single['user'] = $user;
                        if($point['post_code'] != ''){
                            $single['location'] = $point['street'].", ".$point['post_code'];
                        }
                        else{
                            $single['location'] = 'NA';
                        }
                        $single['latitude'] = $point['lat'];
                        $single['longitude'] = $point['lon'];
                        $single['icon'] = $this->getIconFromIncidentType($point['ns']);
                        $single['date'] = $point['time'];
        
                        array_push($incidentsData,$single);
                        array_push($finalJourneyData,$single);
                        $masterJourneyDetailByIdArray[$point['telematics_journey_id']]['incidentData'][]=$single;
                    }
                    array_push($finalJourneyData,$single);
                    $masterJourneyDetailByIdArray[$point['telematics_journey_id']]['journeyData'][]=$single;
                }
        
            }
            foreach($_journeyIds as $_jId){
                $masterJourneyDetailByIdArray[$_jId]['journeySummary']=$telematicsJourneyCollection->where('id',(int)$_jId)->first();
                if(!isset($masterJourneyDetailByIdArray[$_jId]['incidentData'])){
                    $masterJourneyDetailByIdArray[$_jId]['incidentData']=[];
                }
            }
            return $masterJourneyDetailByIdArray;
        }else{
            return null;
        }
    }
    private function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return number_format($miles,2);
        }
    }

    private function fetchScore($startDate,$endDate){
        
    }

    private function getTrendScoreData($trendJourneyIds, $registration=null, $userId=null, $vehicleId=null)
    {
        // $trendJourneyIds = explode(",", $trendJourneyIds);
        // $journeySummarySql = TelematicsJourneys::whereIn('journey_id', $trendJourneyIds)
        //                                 ->selectRaw('AVG(acceleration_score) as acceleration, AVG(braking_score) as braking, AVG(cornering_score) as cornering, AVG(speeding_score) as speeding, AVG(safety_score) as safety, AVG(idle_score) as idle, AVG(rpm_score) as rpm, AVG(efficiency_score) as efficiency, sum(fuel) as fuel, sum(co2) as co2, sum(gps_distance) as gps_distance, sum(engine_duration) as engine_duration');
                                      
        // if($registration != '' && $registration != null) {
        //     $journeySummarySql = $journeySummarySql->where('vrn', $registration);
        // }
        // $journeySummary = $journeySummarySql->first();
        // return $journeySummary->toArray();
        if($vehicleId == '') {
            $vehicleId = null;
        }
        if($userId == '') {
            $userId = null;
        }
        if($registration == '') {
            $registration = null;
        }
        return $this->getUserVehicleScoreData($trendJourneyIds, $userId, $vehicleId, $registration);

    }

    private function getUserVehicleScoreData($trendJourneyIds, $user_id = null, $vehicle_id = null, $registration = null)
    {
        /*** FOR RAW DATA CALCULATION *****/
        $trendJourneyIds = explode(",", $trendJourneyIds);
        // $journeySummary = TelematicsJourneys::whereIn('journey_id', $trendJourneyIds)
        //                                     ->where('user_id',$user_id)
        //                                     ->where('vehicle_id',$vehicle_id)
        //                                     ->get();
        // $data = $this->telematicsService->calculateJourneyScore($journeySummary);
        // return $data;

        $journeySummary = TelematicsJourneys::whereIn('id', $trendJourneyIds)->whereNotNull('end_time');
        if($user_id) {
            $journeySummary = $journeySummary->where('user_id',$user_id);
        }

        if($vehicle_id) {
            $journeySummary = $journeySummary->where('vehicle_id',$vehicle_id);
        }

        if($registration) {
            $journeySummary = $journeySummary->where('vrn', $registration);
        }

        $journeySummary = $journeySummary->selectRaw('SUM(harsh_acceleration_count) as harsh_acceleration_count,
                                                SUM(harsh_cornering_count) as harsh_cornering_count,
                                                SUM(harsh_acceleration_count) as harsh_acceleration_count,
                                                SUM(speeding_incident_count) as speeding_count,
                                                SUM(harsh_breaking_count) as harsh_breaking_count,
                                                SUM(rpm_count) as rpm_count, 
                                                SUM(idling_count) as idling_count,
                                                SUM(gps_distance) as gps_distance,
                                                SUM(engine_duration) as engine_duration,
                                                SUM(gps_idle_duration) as gps_idle_duration,
                                                SUM(fuel) as fuel,
                                                SUM(co2) as co2')
                                            ->first();

        return $this->telematicsService->calculateJourneyScore($journeySummary);

        // $trendJourneyIds = explode(",", $trendJourneyIds);
        // $journeySummary = TelematicsJourneys::whereIn('journey_id', $trendJourneyIds)
        //                                 ->where('user_id',$user_id)
        //                                 ->where('vehicle_id',$vehicle_id)
        //                                 ->selectRaw('AVG(acceleration_score) as acceleration, AVG(braking_score) as braking, AVG(cornering_score) as cornering, AVG(speeding_score) as speeding, AVG(safety_score) as safety, AVG(idle_score) as idle, AVG(rpm_score) as rpm, AVG(efficiency_score) as efficiency, sum(fuel) as fuel, sum(co2) as co2, sum(gps_distance) as gps_distance, sum(engine_duration) as engine_duration')
        //                                 ->first();

        // return $journeySummary->toArray();
    }

    public function searchJourneyLocation(Request $request)
    {
        $googleApiKey = config('services.google.api_key');
        $googleApiURL = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$request['postcode'].',&key='.$googleApiKey;

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $googleApiURL);

        if($response) {
            $content = json_decode($response->getBody()->getContents(), true);
            $latitude = head($content['results'])['geometry']['location']['lat'];
            $longitude = head($content['results'])['geometry']['location']['lng'];
            $bounds = array_key_exists("bounds", head($content['results'])['geometry']) ? head($content['results'])['geometry']['bounds'] : NULL;
            $data = array("latitude" => $latitude, "longitude" => $longitude, "bounds" => json_encode($bounds));
        }

        $decodedBounds = json_decode($data['bounds'], true);
        $latitude = [$decodedBounds['southwest']['lat'], $decodedBounds['northeast']['lat']];
        $longitude = [$decodedBounds['southwest']['lng'], $decodedBounds['northeast']['lng']];

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    public function getAllLocations()
    {
         return response()->json(Location::all());
    }

    public function getAllCompanies() {
        $allOtherCompanies = Company::whereNotIn('name',['Aecor','Other'])->where('user_type', 'Other')->orderBy('name')->lists('name', 'id')->unique()->toArray();
        $lastTwo = Company::whereIn('name',['Aecor','Other'])->lists('name', 'id')->unique()->toArray();
        return ['' => ''] + $allOtherCompanies + $lastTwo;
    }
}
