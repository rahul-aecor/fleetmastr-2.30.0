<?php
namespace App\Http\Controllers;

use App\Models\MaintenanceEvents;
use Gate;
use DB;
use Auth;
use Input;
use JavaScript;
use PDF;
use Spatie\Period\Period;
use View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\StoreVehiclePlanningHistoryRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Check;
use App\Models\Defect;
use App\Models\Role;
use App\Models\VehicleVORLog;
use App\Models\ColumnManagements;
use App\Models\VehicleLocations;
use App\Models\VehicleRepairLocations;
use App\Models\VehicleType;
use App\Models\PrivateUseLogs;
use App\Models\VehicleUsageHistory;
use App\Models\VehiclePlanningComment;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\TemporaryImage;
use App\Repositories\VehiclesRepository;
use App\Repositories\VehiclesPlanningRepository;
use App\Events\PushNotification;
use App\Custom\Facades\GridEncoder;
use Spatie\MediaLibrary\Media;
use App\Custom\Helper\Common;
use App\Custom\Helper\P11dReportHelper;
use App\Models\Settings;
use App\Repositories\VehicleMaintenanceHistoryRepository;
use App\Models\VehicleMaintenanceHistory;
use App\Services\VehicleService;
use App\Services\Report;
use App\Services\UserService;
use App\Services\TelematicsService;
use App\Models\VehicleArchiveHistory;
use App\Models\VehicleAssignment;
use App\Repositories\VehicleAssignmentRepository;
use App\Repositories\VehicleHistoryRepository;
use App\Repositories\CustomReportRepository;
use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneys;
use App\Repositories\VehicleDocumentRepository;

class VehiclesController extends Controller
{
    public $title= 'Vehicle Search';

     public function __construct(VehicleService $vehicleService) {
       //$this->middleware('auth');
       //$this->middleware('can:user.manage');
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
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicles.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        $show = '';
        if ($request->has('show')) {
            $show = $request->get('show');
            $filters = $this->getVehicleFilters($request->get('show'));
        }
        $vehicles = Vehicle::all();
        $user = Auth::user();

        $column_management = ColumnManagements::where('user_id',$request->user()->id)
        ->where('section','vehicles')
        ->select('data')
        ->first();

          // $manufacturers = VehicleType::lists('manufacturer', 'manufacturer')->sort();
        $manufacturers = VehicleType::select('manufacturer as id', 'manufacturer as text')->distinct()->orderBy('manufacturer')->get();
        // $manufacturers_for_select = collect(['' => ''])->merge($manufacturers);

        // $models = VehicleType::lists('model', 'model')->sort();

        $vehicleManufacturerInfo = [];
        $vehicleModelInfo = [];
        $vehicleTypeInfo = [];
        $checksSearch = [];
        $userLastNameSearchInfo = [];

        if($user->isUserInformationOnly()) {
            $checkVehicles = Check::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();

            $checkRegistrationSearch = DB::table('checks')->where('checks.created_by',Auth::user()->id)
                ->join('vehicles', 'checks.vehicle_id', '=', 'vehicles.id')->select('vehicles.registration')
                ->distinct('registration')->get();

            foreach ($checkRegistrationSearch as $key => $checkRegistration) {
                $checksSearch[$key]['id'] = $checkRegistration->registration;
                $checksSearch[$key]['text'] = $checkRegistration->registration;
            }

            $defectVehicles = Defect::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();

            $vehicleIds = array_unique (array_merge ($checkVehicles, $defectVehicles));
            $userInformationOnly = Vehicle::whereIn('vehicles.id',$vehicleIds)->get();
            // $userInformationOnly = Vehicle::where('vehicles.created_by',Auth::user()->id)->get();

            foreach ($userInformationOnly as $key => $userInformation) {
                $vehicleData = VehicleType::where('id', $userInformation->vehicle_type_id)->get();
                foreach ($vehicleData as $key => $vehicle) {
                    $vehicleManufacturer = [];
                    $vehicleManufacturer['id'] = $vehicle->id;
                    $vehicleManufacturer['text'] = $vehicle->manufacturer;
                    array_push($vehicleManufacturerInfo,$vehicleManufacturer);

                    $vehicleModel = [];
                    $vehicleModel['id'] = $vehicle->id;
                    $vehicleModel['text'] = $vehicle->model;
                    array_push($vehicleModelInfo,$vehicleModel);

                    $vehicleType = [];
                    $vehicleType['id'] = $vehicle->id;
                    $vehicleType['text'] = $vehicle->vehicle_type;
                    array_push($vehicleTypeInfo,$vehicleType);
                }
            }
        }

        $models = VehicleType::select('model as id', 'model as text')->distinct()->orderBy('model')->get();

        $models_with_manufacturers = VehicleType::select('model as id', 'model as text', 'manufacturer')->distinct()->orderBy('model')->get()->toArray();

        $manufacturer_model = array();

        foreach ($models_with_manufacturers as $models_with_manufacturer) {
            $manufacturer_model[$models_with_manufacturer['manufacturer']][] = array("id"=>$models_with_manufacturer['id'],"text"=>$models_with_manufacturer['text']);
        }
        $models_for_select =  collect(['' => ''])->merge($models);

        // $types = VehicleType::lists('vehicle_type', 'vehicle_type')->sort();
        $types = VehicleType::select('vehicle_type as id', 'vehicle_type as text')->distinct()->orderBy('vehicle_type')->get();
        $types_for_select = collect(['' => ''])->merge($types);



        $categories = VehicleType::lists('vehicle_category', 'vehicle_category')->sort();
        $categories_for_select =  collect(['' => '', 'hgv' => 'HGV', 'non-hgv' => 'Non-HGV']);
        $status_for_select = collect(config('config-variables.vehicleStatus'));

        //$division_for_select = collect(config('config-variables.vehicleDivisions'));
        //$region_for_select = config('config-variables.userAccessibleRegions');
        // $data=$this->vehicleService->getData();
        // $division_for_select=collect($data['vehicleDivisions']);
        // $region_for_select_array=['' => ''] + $data['vehicleRegions'];
        // $region_for_select=collect($region_for_select_array);

        $data = $this->vehicleService->getDataDivRegLoc();
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $region_for_select = $this->vehicleService->regionForSelect($data);
        } else {
            $region_for_select = $data['vehicleRegions'];
        }
        $region_for_select = ['' => ''] + $region_for_select;
        $region_for_select = collect($region_for_select);

        //$vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicles.vehicle_region', Auth::user()->accessible_regions)->get();
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicles.vehicle_region_id', $userRegions)->get();
        $vehicleRegistrationsAll = Vehicle::withTrashed()->select('registration as id', 'registration as text')->get();
        $vehicleSubCategories = config('config-variables.vehicleSubCategoriesNonHGV');

        $user = Auth::user();



        if($user->isSuperAdmin()){
            $status_for_select['Archived']='Archived';
            $status_for_select['Archived - De-commissioned']='Archived - De-commissioned';
            $status_for_select['Archived - Written off']='Archived - Written off';
        }

        $userRecord = user::get();
        foreach ($vehicles as $key => $vehicle) {
            $userLastNameSearch = [];
            if($vehicle->nominated_driver != '' || $vehicle->nominated_driver != null) {
                $userLastNameSearch['id'] = $vehicle->nominatedDriver['last_name'];
                $userLastNameSearch['text'] = $vehicle->nominatedDriver['last_name'];
                array_push($userLastNameSearchInfo,$userLastNameSearch);
            }
        }
        JavaScript::put([
            'manufacturers' => $manufacturers,
            'models' => $models,
            'types' => $types,
            'categories' => $categories,
            'vehicleRegistrations' => $vehicleRegistrations,
            'vehicleRegistrationsAll' => $vehicleRegistrationsAll,
            'manufacturers_model' => $manufacturer_model,
            'filters' => $filters,
            'show' => $show,
            'isUserInformationOnly' => $user->isUserInformationOnly(),
            'vehicleManufacturerInfo' => $vehicleManufacturerInfo,
            'vehicleModelInfo' => $vehicleModelInfo,
            'vehicleTypeInfo' => $vehicleTypeInfo,
            'column_management' => $column_management,
            'checksSearch' => $checksSearch,
            'vehicleSubCategories' => $vehicleSubCategories,
            'isRegionLinkedInVehicle'=>env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'regionForSelect'=>$region_for_select,
            'userLastNameSearchInfo' => $userLastNameSearchInfo,
        ]);

        // return view('vehicles.index', compact('vehicles', 'manufacturers_for_select', 'models_for_select', 'types_for_select', 'categories_for_select', 'status_for_select', 'region_for_select'));
        return view('vehicles.index', compact('vehicles', 'categories_for_select', 'status_for_select', 'region_for_select'));
    }

    public function profileStatus(Request $request, $id)
    {
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicles.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        if ($request->has('show')) {
            $filters = $this->getVehicleFilters($request->get('show'));
        }
        $vehicles = Vehicle::withTrashed()->get();
        // $manufacturers = VehicleType::lists('manufacturer', 'manufacturer')->sort();
        $manufacturers = VehicleType::select('manufacturer as id', 'manufacturer as text')->distinct()->orderBy('manufacturer')->get();
        // $manufacturers_for_select = collect(['' => ''])->merge($manufacturers);

        // $models = VehicleType::lists('model', 'model')->sort();
        $models = VehicleType::select('model as id', 'model as text')->distinct()->orderBy('model')->get();
        $models_with_manufacturers = VehicleType::select('model as id', 'model as text', 'manufacturer')->distinct()->orderBy('model')->get()->toArray();
        $manufacturer_model = array();
        foreach ($models_with_manufacturers as $models_with_manufacturer) {
            $manufacturer_model[$models_with_manufacturer['manufacturer']][] = array("id"=>$models_with_manufacturer['id'],"text"=>$models_with_manufacturer['text']);
        }
        $models_for_select =  collect(['' => ''])->merge($models);

        // $types = VehicleType::lists('vehicle_type', 'vehicle_type')->sort();
        $types = VehicleType::select('vehicle_type as id', 'vehicle_type as text')->distinct()->orderBy('vehicle_type')->get();

        $types_for_select = collect(['' => ''])->merge($types);

        $categories = VehicleType::lists('vehicle_category', 'vehicle_category')->sort();
        $categories_for_select =  collect(['' => '', 'hgv' => 'HGV', 'non-hgv' => 'Non-HGV']);
        $status_for_select = collect(config('config-variables.vehicleStatus'));
        //$region_for_select = collect(config('config-variables.userAccessibleRegions'));
        $data=$this->vehicleService->getDataDivRegLoc();
        $region_for_select=['' => '']+$data['vehicleRegions'];
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $region_for_select=['' => '']+$this->vehicleService->regionForSelect($data);
        }
        $region_for_select=collect($region_for_select);

        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->get();
        $vehicleRegistrationsAll = Vehicle::withTrashed()->select('registration as id', 'registration as text')->get();

        $vehicleProfile = VehicleType::where('id', $id)->first();
        $vehicleType = $vehicleProfile->vehicle_type;

        $user = Auth::user();
        if($user->isSuperAdmin()){
            $status_for_select['Archived']='Archived';
            $status_for_select['Archived - De-commissioned']='Archived - De-commissioned';
            $status_for_select['Archived - Written off']='Archived - Written off';
        }
        $userLastNameSearchInfo = [];
        $userRecord = user::get();
        foreach ($vehicles as $key => $vehicle) {
            $userLastNameSearch = [];
            if($vehicle->nominated_driver != '' || $vehicle->nominated_driver != null) {
                $userLastNameSearch['id'] = $vehicle->nominatedDriver['last_name'];
                $userLastNameSearch['text'] = $vehicle->nominatedDriver['last_name'];
                array_push($userLastNameSearchInfo,$userLastNameSearch);
            }
        }
        $vehicleSubCategories = config('config-variables.vehicleSubCategoriesNonHGV');
        JavaScript::put([
            'manufacturers' => $manufacturers,
            'models' => $models,
            'types' => $types,
            'categories' => $categories,
            'vehicleRegistrations' => $vehicleRegistrations,
            'vehicleRegistrationsAll' => $vehicleRegistrationsAll,
            'manufacturers_model' => $manufacturer_model,
            'filters' => $filters,
            'vehicleType' => $vehicleType,
            'userLastNameSearchInfo'=> $userLastNameSearchInfo,
            'vehicleSubCategories'=>$vehicleSubCategories

        ]);
        $flowFromPage = 'vehicleProfiles';
        $division_for_select = collect(config('config-variables.vehicleDivisions'));
        // return view('vehicles.index', compact('vehicles', 'manufacturers_for_select', 'models_for_select', 'types_for_select', 'categories_for_select', 'status_for_select', 'region_for_select'));
        return view('vehicles.index', compact('vehicles', 'categories_for_select', 'status_for_select', 'division_for_select', 'region_for_select','flowFromPage','id'));

    }

     /**
     * Display a listing of the fleet planning.
     *
     * @return \Illuminate\Http\Response
     */
    public function fleet(Request $request)
    {
        View::share ('title', 'Fleet Planning');
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicles.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        $sortname = 'vehicles.created_at';
        $period = NULL;

        if ($request->has('field') && $request->field != 'next-service') {
            $planningFilters = $this->getVehiclePlanningFilters(
                $request->input('field'),
                $request->input('period', 'red'),
                $request->input('region', 'All')
            );
            $filters = $planningFilters['filters'];
            $sortname = $planningFilters['search_field'];
            $period = $planningFilters['period'];
        }

        $vehicles = Vehicle::all();
        $types = VehicleType::select('vehicle_type as id', 'vehicle_type as text')->distinct()->orderBy('vehicle_type')->get();
        $categories = VehicleType::lists('vehicle_category', 'vehicle_category')->sort();
        $data = $this->vehicleService->getDataDivRegLoc();
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $region_for_select = $this->vehicleService->regionForSelect($data);
        } else {
            $region_for_select = $data['vehicleRegions'];
        }
        $region_for_select = ['' => ''] + $region_for_select;
        $region_for_select = collect($region_for_select);
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))->get();
        $user = Auth::user();
        if($user->isSuperAdmin()){
            $status_for_select['Archived']='Archived';
            $status_for_select['Archived - De-commissioned']='Archived - De-commissioned';
            $status_for_select['Archived - Written off']='Archived - Written off';
        }

        // if($user->isUserInformationOnly()) {
            $checkVehicles = Check::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $defectVehicles = Defect::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $vehicleIds = array_unique (array_merge ($checkVehicles, $defectVehicles));

            $userRegions = Auth::user()->regions->lists('id')->toArray();
            $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')->whereIn('vehicles.vehicle_region_id', $userRegions)->get();

            // $vehicleRegistrations = Vehicle::whereIn('vehicles.id',$vehicleIds)->select('registration as id', 'registration as text')->get();
        // }

        $column_management = ColumnManagements::where('user_id', $request->user()->id)
        ->where('section','fleet_planning')
        ->select('data')
        ->first();

        JavaScript::put([
            'types' => $types,
            'categories' => $categories,
            'vehicleRegistrations' => $vehicleRegistrations,
            'filters' => $filters,
            'sortname' => $sortname,
            'period' => $period,
            'column_management' => $column_management,
        ]);

        $maintenanceEvents = MaintenanceEvents::whereIn('is_standard_event',[1,2])->orderBy('name')->get()->lists('name','slug')->toArray();

        $maintenanceEvents = ['' => ''] + $maintenanceEvents;
        //dd($maintenanceEvents);
        $eventsForFilter = config('config-variables.planner_events');

        $dates = $this->vehicleService->getYearDatesArray(Carbon::now()->format('Y'));
        // return view('vehicles.index', compact('vehicles', 'manufacturers_for_select', 'models_for_select', 'types_for_select', 'categories_for_select', 'status_for_select', 'region_for_select'));
        return view('fleet_planning.index', compact('vehicles', 'categories_for_select', 'status_for_select', 'region_for_select','eventsForFilter','maintenanceEvents','dates'));
    }
    /**
     * Display a listing of the vehicle planning.
     *
     * @return \Illuminate\Http\Response
     */
    public function planning(Request $request)
    {
        View::share ('title', 'Vehicle Planning');
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicles.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        $sortname = 'vehicles.created_at';
        $period = NULL;

        if ($request->has('field') && $request->field != 'next-service') {


            $planningFilters = $this->getVehiclePlanningFilters(
                $request->input('field'),
                $request->input('period', 'red'),
                $request->input('region', 'All')
            );
            $filters = $planningFilters['filters'];
            $sortname = $planningFilters['search_field'];
            $period = $planningFilters['period'];
        }
        $vehicles = Vehicle::all();
        $types = VehicleType::select('vehicle_type as id', 'vehicle_type as text')->distinct()->orderBy('vehicle_type')->get();
        $categories = VehicleType::lists('vehicle_category', 'vehicle_category')->sort();
        //$region_for_select = collect(config('config-variables.userAccessibleRegions'));
        $data=$this->vehicleService->getDataDivRegLoc();
        $region_for_select=['' => '']+$data['vehicleRegions'];
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $region_for_select=$this->vehicleService->regionForSelect($data);
        }
        $region_for_select=collect($region_for_select);

        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')
            // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->get();
        $user = Auth::user();
        if($user->isSuperAdmin()){
            $status_for_select['Archived']='Archived';
            $status_for_select['Archived - De-commissioned']='Archived - De-commissioned';
            $status_for_select['Archived - Written off']='Archived - Written off';
        }

        if($user->isUserInformationOnly()) {
            $checkVehicles = Check::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $defectVehicles = Defect::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $vehicleIds = array_unique (array_merge ($checkVehicles, $defectVehicles));

            $vehicleRegistrations = Vehicle::whereIn('vehicles.id',$vehicleIds)->select('registration as id', 'registration as text')->get();
        }

        $maintenanceEvents = MaintenanceEvents::whereIn('is_standard_event',[1,2])->orderBy('name')->get()->lists('name','slug')->toArray();
        $maintenanceEvents = ['' => ''] + $maintenanceEvents;

        JavaScript::put([
            'types' => $types,
            'categories' => $categories,
            'vehicleRegistrations' => $vehicleRegistrations,
            'filters' => $filters,
            'sortname' => $sortname,
            'period' => $period,
        ]);
        // return view('vehicles.index', compact('vehicles', 'manufacturers_for_select', 'models_for_select', 'types_for_select', 'categories_for_select', 'status_for_select', 'region_for_select'));
        return view('vehicles.planning', compact('vehicles', 'status_for_select', 'region_for_select','maintenanceEvents'));
    }

    /**
     * Return the vehicles data for the grid
     *
     * @return [type] [description]
     */
    public function anyData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new VehiclesRepository($request->all()), Input::all());
    }

    /**
     * Return the vehicles planning data for the grid
     *
     * @return [type] [description]
     */
    public function planningData()
    {
        return GridEncoder::encodeRequestedData(new VehiclesPlanningRepository(), Input::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $isConfigurationTabEnabled = 0;
        if (auth()->user()->checkRole('App version handling')) {
            $isConfigurationTabEnabled = 1;
        }

        $allRegions=$allLocations=[];
        $data=$this->vehicleService->getDataDivRegLoc();
        $vehicleDivisions = $data['vehicleDivisions'];
        asort($vehicleDivisions);

        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleRegions'] as $regionId => $regions) {
                asort($regions);
                $data['vehicleRegions'][$regionId] = $regions;
            }
        } else {
            asort($data['vehicleRegions']);
        }

        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleBaseLocations'] as $locationId => $locations) {
                asort($locations);
                $data['vehicleBaseLocations'][$locationId] = $locations;
            }
        } else {
            asort($data['vehicleBaseLocations']);
        }

        $vehicleRegions = $data['vehicleRegions'];
        $userList = ['' => ''] + User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->select(\DB::raw("CONCAT(first_name,' ', last_name, CASE WHEN email IS NULL THEN '' ELSE IF(LOCATE('".env('BRAND_NAME')."-imastr', email) > 0, '', CONCAT(' (', email, ')')) END) AS full_name, id")
        )->orderBy('full_name', 'asc')->lists('full_name', 'id')->toArray();

        $vehicleTypes = VehicleType::orderBy('vehicle_type', 'asc')->lists('vehicle_type', 'id')->toArray();
        $vehicleTypesList = ['' => ''] + $vehicleTypes;

        $vehicleLocations = VehicleLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        //$vehicleLocationsList = collect(['' => ''])->merge($vehicleLocations);
        $vehicleLocationsList = ['' => ''] + $vehicleLocations;

        $vehicleRepairLocations = VehicleRepairLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        //$vehicleRepairLocationsList = collect(['' => ''])->merge($vehicleRepairLocations);
        $vehicleRepairLocationsList = ['' => ''] + $vehicleRepairLocations;
        $vehicleStatusList = config('config-variables.vehicleStatus');
        $ownershipStatusList = config('config-variables.ownershipStatus');
        $usageTypeList = config('config-variables.usageType');

        //$vehicleLocationValue = config('config-variables.vehicleLocation');

        $vehicle = new Vehicle();

        $vehicleType = new VehicleType();
        $p11dReportHelper = new P11dReportHelper();

        // $vehicleBaseLocations = config('config-variables.vehicleLocation');
        // $vehicleRegions = config('config-variables.vehicleRegions');
        // //$privateUseLogs = null;

        // $vehicleDivisionArray = array();
        // foreach (config('config-variables.vehicleDivisions') as $key => $value) {
        //     array_push($vehicleDivisionArray, ['id'=>$key, 'text'=>$value]);
        // }

        // $vehicleRegionArray = array();
        // foreach ($vehicleRegions as $key => $value) {
        //     array_push($vehicleRegionArray, ['id'=>$key, 'text'=>$value]);
        // }

        // $vehicleBaseLocationsArray = array();
        // foreach ($vehicleLocations as $key => $value) {
        //     array_push($vehicleBaseLocationsArray, ['id'=>$key, 'text'=>$value]);
        // }
        $vehicleNominatedDriver = vehicle::all()->unique('nominated_driver')->pluck('nominated_driver')->toArray();
        $usersList = User::whereHas('roles', function ($query) {
                    $query->where('name', '!=', 'Workshops');
                    })->select(\DB::raw("CONCAT(first_name,' ', last_name, CASE WHEN email IS NULL THEN CONCAT(' (', username, ')') ELSE IF(LOCATE('".env('BRAND_NAME')."-imastr', email) > 0, CONCAT(' (', username, ')'), CONCAT(' (', email, ')')) END) AS full_name, id")
        )->orderBy('full_name', 'asc')->lists('full_name', 'id')->toArray();
        $nominatedDriverArray = array();
        foreach ($usersList as $key => $value) {
            if(in_array($key, $vehicleNominatedDriver)){
                array_push($nominatedDriverArray, ['id'=>$key, 'text'=>$value , 'disabled'=>true]);
            } else {
                array_push($nominatedDriverArray, ['id'=>$key, 'text'=>$value]);
            }
        }

        $vehicleRepairLocationsArray = array();
        foreach ($vehicleRepairLocations as $key => $value) {
            array_push($vehicleRepairLocationsArray, ['id'=>$key, 'text'=>$value]);
        }

        $vehicleStatusArray = array();
        foreach ($vehicleStatusList as $key => $value) {
            array_push($vehicleStatusArray, ['id'=>$key, 'text'=>$value]);
        }

        $vehicleTypesArray = array();
        foreach ($vehicleTypes as $key => $value) {
            array_push($vehicleTypesArray, ['id'=>$key, 'text'=>$value]);
        }

        $monthlyInsurance = Settings::where('key', 'fleet_cost_area_detail')->first();
        $monthlyInsuranceJson = $monthlyInsurance->value;
        $monthlyFleetCostData = json_decode($monthlyInsuranceJson, true);
        $vehicleId = 0;
        $vehicleArchiveHistory = 0;
        $vehicleDtAddedToFleet = '';
        $isInsuranceCostOverride = 0;
        $isTelematicsCostOverride = 0;

        $insuranceValueDisplay = [];
        $insuranceFieldCurrentCost = 0;
        $insuranceFieldCurrentDate = '';
        // if(isset($monthlyFleetCostData['annual_insurance_cost'])) {
        //     $insuranceValueDisplay = $monthlyFleetCostData['annual_insurance_cost'];
        //     $insuranceFieldCurrentCost = 0;
        //     $insuranceFieldCurrentDate = '';
        //     $insuranceValue = json_encode($monthlyFleetCostData['annual_insurance_cost']);
        //     if(isset($insuranceValue)){
        //         $commonHelper = new Common();
        //        // $insuranceCurrentData = $this->calcMonthlyCurrentData($insuranceValue,$vehicleId, $vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride);
        //         $insuranceCurrentData = $commonHelper->getFleetCostValueForDate($insuranceValue,Carbon::now()->format('Y-m-d'), $vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride);
        //         $insuranceFieldCurrentCost = $insuranceCurrentData['currentCost'];
        //         $insuranceFieldCurrentDate = $insuranceCurrentData['currentDate'];
        //     }
        // }

        $telematicsValueDisplay = [];
        $telematicsFieldCurrentCost = 0;
        $telematicsFieldCurrentDate = '';
        if(isset($monthlyFleetCostData['telematics_insurance_cost'])) {
            $telematicsValueDisplay = $monthlyFleetCostData['telematics_insurance_cost'];
            $telematicsValue = json_encode($monthlyFleetCostData['telematics_insurance_cost']);
            if(isset($telematicsValue)){
                $commonHelper = new Common();
                $telematicsCurrentData = $commonHelper->getFleetCostValueForDate($telematicsValue,Carbon::now()->format('Y-m-d'), $vehicleArchiveHistory,$vehicleDtAddedToFleet,$isTelematicsCostOverride);
                $telematicsFieldCurrentCost = $telematicsCurrentData['currentCost'];
                $telematicsFieldCurrentDate = $telematicsCurrentData['currentDate'];
            }
        }
        $supplierTelematics = config('config-variables.supplier_telematics');
        $deviceTelematics = config('config-variables.device_telematics');
        $lastDateUpdateDevice = '';
        $installationDate = '';

        $locationArr = [];
        foreach ($data['vehicleBaseLocations'] as $id => $location) {
            if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
                $k = 0;
                foreach ($location as $key => $value) {
                    unset($data['vehicleBaseLocations'][$id][$key]);
                    // $data['vehicleBaseLocations'][$id][$k] = ['id'=> $key, 'text' => $value];
                    $locationArr[$id][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                unset($data['vehicleBaseLocations'][$id]);
                // $data['vehicleBaseLocations'][] = ['id'=> $id, 'text' => $location];
                $locationArr[] = ['id'=> $id, 'text' => $location];
            }
        }
        $data['vehicleBaseLocations'] = $locationArr;

        $vehicleRegionsArray = [];
        foreach ($vehicleRegions as $id => $region) {
            if(env('IS_DIVISION_REGION_LINKED_IN_USER')) {
                $k = 0;
                foreach ($region as $key => $value) {
                    $vehicleRegionsArray[$id][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                $vehicleRegionsArray[] = ['id'=> $id, 'text' => $region];
            }
        }

        $vehicleAdHocCosts = config('config-variables.vehicle_ad_hoc_costs');

        $fromPage = "add";
        JavaScript::put([
            'vehicleBaseLocations'=>$data['vehicleBaseLocations'],
            'vehicleRegions'=>$vehicleRegionsArray,
            'vehicleDivisions'=>$data['vehicleDivisions'],
            //'vehicleLocationValue' => $vehicleLocationValue,
            //'vehicleBaseLocations'=>$vehicleBaseLocations,
            //'vehicleRegions'=>$vehicleRegions,
           // 'vehicleDivisionList' => $vehicleDivisionArray,
            //'vehicleRegionList' => $vehicleRegionArray,
            //'vehicleBaseLocationsList' => $vehicleBaseLocationsArray,
            'nominatedDriverList' => $nominatedDriverArray,
            'vehicleRepairLocationsList' => $vehicleRepairLocationsArray,
            'vehicleStatusList' => $vehicleStatusArray,
            'vehicleTypesList' => $vehicleTypesArray,
            'brandName' => env('BRAND_NAME'),
            'isLocationLinkedInVehicle'=>env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'),
            'isRegionLinkedInVehicle'=>env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'fromPage' => $fromPage,
            'isConfigurationTabEnabled' => $isConfigurationTabEnabled,
        ]);
        return view('vehicles.create', compact('vehicleTypesList', 'vehicleLocationsList', 'vehicleRepairLocationsList', 'vehicleStatusList', 'vehicle', 'userList',
            'usageTypeList','vehicleDivisions','vehicleRegions','insuranceValueDisplay',
            'telematicsValueDisplay', 'ownershipStatusList'))
            ->with('hmrcTaxYear',$p11dReportHelper->calcTaxYear())
            ->with('privateUseLogs',null)
            ->with('vehicleType',$vehicleType)
            ->with('monthlyFleetCostData',$monthlyFleetCostData)
            ->with('insuranceFieldCurrentCost',$insuranceFieldCurrentCost)
            ->with('telematicsFieldCurrentCost',$telematicsFieldCurrentCost)
            ->with('insuranceFieldCurrentDate',$insuranceFieldCurrentDate)
            ->with('telematicsFieldCurrentDate',$telematicsFieldCurrentDate)
            ->with('isConfigurationTabEnabled',$isConfigurationTabEnabled)
            ->with('supplierTelematics',$supplierTelematics)
            ->with('lastDateUpdateDevice',$lastDateUpdateDevice)
            ->with('installationDate',$installationDate)
            ->with('deviceTelematics',$deviceTelematics)
            ->with('vehicleAdHocCosts',$vehicleAdHocCosts)
            ->with('fromPage',$fromPage);
    }

    public function checkRegistration(Request $request) {
        if ($request->registration !== null && !empty($request->registration)) {
            if ($request->id) {
                //$user = User::where('registration', $request->registration)->where('id', '!=', $request->id)->first();
                $vehicle = DB::table('vehicles')->where('registration', $request->registration)->where('id', '!=', $request->id)->first();
            }
            else {
                $vehicle = DB::table('vehicles')->where('registration', $request->registration)->first();
            }
            if ($vehicle) {
                return "false";
            }
        }
        return "true";
    }
    public function checkWebfleetRegistration(Request $request) {
        if ($request->registration !== null && !empty($request->registration)) {
            if ($request->id) {
                //$user = User::where('registration', $request->registration)->where('id', '!=', $request->id)->first();
                $vehicle = DB::table('vehicles')->where('webfleet_registration', $request->registration)->where('id', '!=', $request->id)->first();
            }
            else {
                $vehicle = DB::table('vehicles')->where('webfleet_registration', $request->registration)->first();
            }
            if ($vehicle) {
                return "false";
            }
        }
        return "true";
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVehicleRequest $request)
    {
        $isConfigurationTabEnabled = 0;
        if (auth()->user()->checkRole('App version handling')) {
            $isConfigurationTabEnabled = 1;
        }
        $vehicle = new Vehicle();
        $vehicle->registration = $request->registration;
        // $vehicle->vehicle_category = $request->vehicle_category;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->status = $request->status;
        $vehicle->dt_added_to_fleet = $request->dt_added_to_fleet;
        $vehicle->last_odometer_reading = $request->last_odometer_reading != '' ? $request->last_odometer_reading : 0 ;
        $vehicle->P11D_list_price = (!empty($request->P11D_list_price))?$request->P11D_list_price:null;
        $vehicle->dt_registration = $request->dt_registration;
        $vehicle->operator_license = (!empty($request->operator_license))?$request->operator_license:null;
        $vehicle->chassis_number = (!empty($request->chassis_number))?$request->chassis_number:null;
        $vehicle->contract_id = (!empty($request->contract_id))?$request->contract_id:null;
        $vehicle->notes = (!empty($request->notes))?$request->notes:null;
        $vehicle->adr_test_date = (!empty($request->adr_test_date))?$request->adr_test_date:null;
        
        //$vehicle->vehicle_division = (!empty($request->vehicle_division))?$request->vehicle_division:null;

        $vehicle->vehicle_division_id = (!empty($request->vehicle_division_id))?$request->vehicle_division_id:null;

        //$vehicle->vehicle_location_id = (!empty($request->vehicle_location_id))?$request->vehicle_location_id:null;
        $vehicle->vehicle_location_id = (!empty($request->vehicle_location_id))?$request->vehicle_location_id:null;

        $vehicle->lease_expiry_date = (!empty($request->lease_expiry_date))?$request->lease_expiry_date:null;
        $vehicle->dt_loler_test_due = (!empty($request->dt_loler_test_due))?$request->dt_loler_test_due:null;
        $vehicle->dt_first_use_inspection = (!empty($request->dt_first_use_inspection))?$request->dt_first_use_inspection:null;
        $vehicle->dt_vehicle_disposed = (!empty($request->dt_vehicle_disposed))?$request->dt_vehicle_disposed:null;

        //$vehicle->vehicle_region = $request->vehicle_region;
        //print_r($request->vehicle_region_id);die;
        $vehicle->vehicle_region_id = $request->vehicle_region_id;

        $vehicle->vehicle_repair_location_id = (!empty($request->vehicle_repair_location_id))?$request->vehicle_repair_location_id:null;
        $vehicle->dt_repair_expiry = $request->dt_repair_expiry;
        $vehicle->dt_mot_expiry = $request->dt_mot_expiry!='' ? $request->dt_mot_expiry : null;

        $vehicle->dt_tax_expiry = $request->dt_tax_expiry;
        $vehicle->dt_annual_service_inspection = $request->dt_annual_service_inspection;
        $vehicle->dt_next_service_inspection = $request->dt_next_service_inspection;
        if ($vehicle->type->service_interval_type == 'Distance') {
            $vehicle->next_service_inspection_distance =  $request->next_service_inspection_distance ? str_replace(",","",$request->next_service_inspection_distance) : null;
        }
        //dd($vehicle->next_service_inspection_distance);
        $vehicle->on_road = $request->on_road;
        $vehicle->is_telematics_enabled = $request->is_telematics_enabled;
        if($request->is_telematics_enabled == 1 && $isConfigurationTabEnabled == 1) {
            $vehicle->supplier = $request->supplier;
            $vehicle->device = $request->device;
            $vehicle->serial_id = $request->serial_id;
            $vehicle->installation_date = Carbon::createFromFormat('d M Y',$request->installation_date)->format('Y-m-d');
        }
        $vehicle->webfleet_object_id = $request->webfleet_object_id;

        $vehicle->masternaut = $request->masternaut;

        $vehicle->manual_cost_adjustment = (!empty($request->vehicle_fleet_cost_adjustments))?$request->vehicle_fleet_cost_adjustments:null;
        // $vehicle->miles_per_month = (!empty($request->miles_per_month))?$request->miles_per_month:null;
        $vehicle->fuel_use = (!empty($request->vehicle_fuel_use_value))?$request->vehicle_fuel_use_value:null;
        $vehicle->oil_use = (!empty($request->vehicle_oil_cost_adjustments))?$request->vehicle_oil_cost_adjustments:null;
        $vehicle->adblue_use = (!empty($request->vehicle_ad_blue_adjustments))?$request->vehicle_ad_blue_adjustments:null;
        $vehicle->screen_wash_use = (!empty($request->vehicle_screen_wash))?$request->vehicle_screen_wash:null;
        $vehicle->fleet_livery_wash = (!empty($request->vehicle_fleet_livery))?$request->vehicle_fleet_livery:null;
        $vehicle->annual_vehicle_cost = (!empty(str_replace(",", "",$request->annual_vehicle_cost)))?str_replace(",", "",$request->annual_vehicle_cost):null;
        $vehicle->monthly_lease_cost = (!empty(str_replace(",", "",$request->monthly_lease_cost)))?str_replace(",", "", $request->monthly_lease_cost):null;
        $vehicle->permitted_annual_mileage = (!empty(str_replace(",", "",$request->permitted_annual_mileage)))?
            str_replace(",", "",$request->permitted_annual_mileage):null;
        $vehicle->excess_cost_per_mile = (!empty(str_replace(",", "",$request->excess_cost_per_mile)))?
            str_replace(",", "",$request->excess_cost_per_mile):null;

        $vehicle->staus_owned_leased = $request->staus_owned_leased;
        $vehicle->next_pto_service_date = $request->next_pto_service_date;
        $vehicle->next_invertor_service_date = $request->next_invertor_service_date;
        $vehicle->next_compressor_service = $request->next_compressor_service;
        $vehicle->is_insurance_cost_override = (isset($request->is_insurance_cost_override) == 1) ? 1 : 0;
        $vehicle->is_telematics_cost_override = (isset($request->is_telematics_cost_override) == 1) ? 1 : 0;
        //$vehicle->CO2 = (!empty($request->CO2))?$request->CO2:null;
        if ($request->usage_override_flag == '1') {
            $vehicle->usage_type = (!empty($request->usage_type))?$request->usage_type:null;
        }
        //$vehicle->usage_type = (!empty($request->usage_type))?$request->usage_type:null;
        // $vehicle->nominated_driver = (!empty($request->nominated_driver))? $request->nominated_driver:null;
        if (!empty($request->nominated_driver)) {
            $vehicle->nominated_driver = $request->nominated_driver;
        }
        else{
            $vehicle->nominated_driver = null;
        }

        $vehicle->created_by = Auth::id();
        $vehicle->updated_by = Auth::id();

        $vehicle->tank_test_date = (!empty($request->tank_test_date)) ? $request->tank_test_date : null;

        $vechileCategory = VehicleType::select(['vehicle_category'])
                                    ->find($request->vehicle_type_id);

        $vechileCategory =  $vechileCategory->vehicle_category;
        $vehicle->next_pmi_date = $request->next_pmi_date;
        $vehicle->first_pmi_date = $request->first_pmi_date;
        if ($vechileCategory == 'non-hgv') {
            $vehicle->dt_tacograch_calibration_due = NULL;
            $vehicle->service_inspection_interval_hgv = NULL;
            // $vehicle->service_inspection_interval_non_hgv = $request->service_inspection_interval_non_hgv;
            $vehicle->service_inspection_interval_non_hgv = config('config-variables.inspectionInterval.nonhgv');
        } else {
            $vehicle->dt_tacograch_calibration_due = $request->dt_tacograch_calibration_due;
            // $vehicle->service_inspection_interval_hgv = $request->service_inspection_interval_hgv;
            $vehicle->service_inspection_interval_hgv = config('config-variables.inspectionInterval.hgv');
            $vehicle->service_inspection_interval_non_hgv = NULL;
        }

        $request['maintenanceCostRepeater'] = json_decode($request->monthly_maintenance_cost_json,true);

        if(isset($request['maintenanceCostRepeater']) && count($request['maintenanceCostRepeater']) > 0 && $request['maintenanceCostRepeater']){
            $maintenanceCostArray = [];
            $editMonthlyMaintenanceCost = $request['maintenanceCostRepeater'];
            foreach ($editMonthlyMaintenanceCost as $key => $editMaintenanceCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editMaintenanceCost['cost_value']);
                $finalArray['cost_from_date'] = $editMaintenanceCost['cost_from_date'];
                $finalArray['cost_to_date'] = $editMaintenanceCost['cost_to_date'];
                $finalArray['cost_continuous'] = $editMaintenanceCost['cost_continuous'];
                array_push($maintenanceCostArray, $finalArray);
                //$maintenanceCostArray[] = $finalArray;
            }
            $vehicle->maintenance_cost = json_encode($maintenanceCostArray);
        }

        $request['leaseCostRepeater'] = json_decode($request->monthly_lease_cost_json,true);

        if(isset($request['leaseCostRepeater']) && count($request['leaseCostRepeater']) > 0 && $request['leaseCostRepeater']){

            $leaseCostArray = [];
            $editMonthlyLeaseCost = $request['leaseCostRepeater'];
            foreach ($editMonthlyLeaseCost as $key => $editLeaseCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editLeaseCost['cost_value']);
                $finalArray['cost_from_date'] = $editLeaseCost['cost_from_date'];
                $finalArray['cost_to_date'] = $editLeaseCost['cost_to_date'];
                $finalArray['cost_continuous'] = $editLeaseCost['cost_continuous'];
                array_push($leaseCostArray, $finalArray);
                //$leaseCostArray[] = $finalArray;
            }
            $vehicle->lease_cost = json_encode($leaseCostArray);
        }

        $request['monthlyInsuranceCostRepeater'] = json_decode($request->insurance_cost_json,true);
        $insuranceCostArray = [];
        if(isset($request['monthlyInsuranceCostRepeater']) && $request['is_insurance_cost_override'] == 1){
            $monthlyInsuranceCost = $request['monthlyInsuranceCostRepeater'];
            foreach ($monthlyInsuranceCost as $key => $monthlyInsurance) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $monthlyInsurance['cost_value']);
                $finalArray['cost_from_date'] = $monthlyInsurance['cost_from_date'];
                $finalArray['cost_to_date'] = $monthlyInsurance['cost_to_date'];
                $finalArray['cost_continuous'] = $monthlyInsurance['cost_continuous'];
                $finalArray['json_type'] = 'monthlyInsurance';

                array_push($insuranceCostArray, $finalArray);
            }
        }
        $vehicle->insurance_cost = json_encode($insuranceCostArray);

        $telematicsCostArray = [];
        $request['monthlyTelematicsCostRepeater'] = $request->is_telematics_enabled == 1 ? json_decode($request->telematics_cost_json,true) : [];
        if(isset($request['monthlyTelematicsCostRepeater']) && $request['is_telematics_cost_override'] == 1){
            $monthlyTelematicsCost = $request['monthlyTelematicsCostRepeater'];
            foreach ($monthlyTelematicsCost as $key => $monthlyTelematics) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $monthlyTelematics['cost_value']);
                $finalArray['cost_from_date'] = $monthlyTelematics['cost_from_date'];
                $finalArray['cost_to_date'] = $monthlyTelematics['cost_to_date'];
                $finalArray['cost_continuous'] = $monthlyTelematics['cost_continuous'];
                $finalArray['json_type'] = 'monthlyTelematics';
                array_push($telematicsCostArray, $finalArray);
            }
        }
        $vehicle->telematics_cost = json_encode($telematicsCostArray);

        $request['monthlyDepreciationCostRepeater'] = json_decode($request->monthly_depreciation_cost_json,true);
        if(isset($request['monthlyDepreciationCostRepeater']) && count($request['monthlyDepreciationCostRepeater']) > 0 && $request['monthlyDepreciationCostRepeater']){
            $depreciationCostArray = [];
            if(isset($request['monthlyDepreciationCostRepeater'])){
                $deprectionCost = $request['monthlyDepreciationCostRepeater'];
                foreach ($deprectionCost as $key => $deprection) {
                    $finalArray = [];
                    $finalArray['cost_value'] = str_replace(',', '', $deprection['cost_value']);
                    $finalArray['cost_from_date'] = $deprection['cost_from_date'];
                    $finalArray['cost_to_date'] = $deprection['cost_to_date'];
                    $finalArray['cost_continuous'] = $deprection['cost_continuous'];
                    array_push($depreciationCostArray, $finalArray);
                }
            }
            $vehicle->monthly_depreciation_cost = json_encode($depreciationCostArray);
        }

        if(isset($request['is_insurance_cost_override']) != 1) {
            $vehicle->insurance_cost = null;
        }

        if(isset($request['is_telematics_cost_override']) != 1) {
            $vehicle->telematics_cost = null;
        }

        // if($request->staus_owned_leased == 'Leased') {
        //     $vehicle->monthly_depreciation_cost = null;
        // } else if($request->staus_owned_leased == 'Owned') {
        //     $vehicle->lease_cost = null;
        // } else if($request->staus_owned_leased == 'Contract' || $request->staus_owned_leased == 'Hired') {
        //     $vehicle->maintenance_cost = null;
        //     $vehicle->monthly_depreciation_cost = null;
        // } else if($request->staus_owned_leased == 'Hire purchase') {
        //     $vehicle->monthly_depreciation_cost = null;
        // }

        if($vehicle->save()){

            //#FLEE-6600 - Create/Edit vehicle - Create maintenance events when user enter the date in Vehicle Planning fields.
            $this->createVehicleMaintenanceEvents($vehicle);

            // remove this comment while commit 
            event(new PushNotification(config('config-variables.pushNotification.messages.vehicle_added')));

            // storing vehicle usage history
            if ($vehicle->nominated_driver != null) {
                $VehicleUsageHistory = new VehicleUsageHistory();
                $VehicleUsageHistory->user_id = $vehicle->nominated_driver;
                $VehicleUsageHistory->vehicle_id = $vehicle->id;
                $VehicleUsageHistory->from_date = Carbon::now();
                $VehicleUsageHistory->save();
            }
            if ($request->is_telematics_enabled) {
                $dataArray['vehicle_id'] = $vehicle->id;
                $dataArray['hardware_id'] = null;
                $dataArray['registration'] = $vehicle->registration;
                $dataArray['client'] = env('BRAND_NAME');
                $dataArray['clientURL'] = env('API_URL').'/api/'.env('API_VERSION');
                // remove this comment while commit 
                $this->telematicsVehiclePush($dataArray);
            }

            if (isset($request->private_use)) {
                $p11dReportHelper = new P11dReportHelper();
                $privateUse = new PrivateUseLogs();
                $privateUse->vehicle_id = $vehicle->id;
                $privateUse->user_id = $request->nominated_driver?$request->nominated_driver:0;
                $privateUse->tax_year = $p11dReportHelper->calcTaxYear();
                $privateUse->start_date = Carbon::now()->format('d M Y');
                $privateUse->save();
            }

            return redirect("vehicles");
            // flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        // return redirect("vehicles/upload/".$lastInsertedId);
    }

    private function createVehicleMaintenanceEvents($vehicle)
    {
        $maintenanceEvents = config('config-variables.automaticMaintenanceEvent');
        $user = User::where('first_name','System')->first();
        $vehicleType = $vehicle->type;
        $vehicle = $vehicle->toArray();
        
        foreach ($maintenanceEvents as $key => $eventData) {

            $vehicleEvent = $vehicle[$eventData['date']];

            if($vehicleEvent && $vehicleEvent != '' && $eventData['date'] != 'next_pmi_date') {
                $startDate = Carbon::today()->addDays(29)->format('Y-m-d');
                if($eventData['event'] == 'adr_test') {
                    $startDate = Carbon::today()->addDays(89)->format('Y-m-d');
                }
                $maintenanceEvent = MaintenanceEvents::where('slug', $eventData['event'])->first();
                if ($maintenanceEvent) {
                    $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $vehicle['id'])
                                                        ->where('event_type_id', $maintenanceEvent->id)
                                                        ->where('event_status', 'Incomplete')
                                                        ->first();

                    if(!isset($vehicleMaintenanceHistory)) {
                        \Log::info('Creating entry for event '.$eventData['event'].' and start date is '.$startDate);
                        $vehicleHistory = new VehicleMaintenanceHistory();
                        $vehicleHistory->vehicle_id = $vehicle['id'];
                        $vehicleHistory->event_type_id = $maintenanceEvent->id;
                        $vehicleHistory->event_plan_date = $vehicleEvent;
                        $vehicleHistory->event_status = 'Incomplete';
                        $vehicleHistory->created_by = $user->id;
                        $vehicleHistory->updated_by = $user->id;
                        $vehicleHistory->save();
                    } else {
                        if($eventData['date'] != 'first_pmi_date') {
                            \Log::info('Updating entry for event '.$eventData['event'].' and start date is '.$startDate);
                            $vehicleMaintenanceHistory->event_plan_date = $vehicleEvent;
                            $vehicleMaintenanceHistory->updated_by = $user->id;
                            $vehicleMaintenanceHistory->save();
                        }
                    }
                }
            }
            if((!$vehicleEvent || $vehicleEvent == '') && $eventData['date'] != 'next_pmi_date') {
                $mEvent = MaintenanceEvents::where('slug', $eventData['event'])->first();
                if ($mEvent) {
                    $vMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $vehicle['id'])
                    ->where('event_type_id', $mEvent->id)
                    ->where('event_status', 'Incomplete')
                    ->first();
                    if($vMaintenanceHistory){
                        \Log::info("Deleting vehicle event : ".$eventData['date'].'('.$mEvent->id.')');
                        $vMaintenanceHistory->delete();
                    }
                }
            }
        }

        if($vehicleType->service_interval_type == 'Distance') {

            $nextServiceInspectionDistanceEvent = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();
            $vehicleMaintenanceHistoryCount = VehicleMaintenanceHistory::where('vehicle_id', $vehicle['id'])->where('event_type_id', $nextServiceInspectionDistanceEvent->id)->where('event_status', 'Incomplete')->count();
            if($vehicleMaintenanceHistoryCount > 0) {
                return;
            }

            $serviceInspectionInterval = $vehicle['next_service_inspection_distance'] ? $vehicle['next_service_inspection_distance'] : $vehicleType->service_inspection_interval;
            $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $vehicle['id'])->where('event_planned_distance', $serviceInspectionInterval)->first();

            if(!$vehicleMaintenanceHistory) {
                $serviceInspectionInterval = $serviceInspectionInterval ? str_replace(",", "", $serviceInspectionInterval) : null;
                $calculateDistance = (is_null($serviceInspectionInterval) ? 0 : $serviceInspectionInterval) - $vehicle['last_odometer_reading'];
                // if ($calculateDistance < config('config-variables.minimum_service_interval')) {
                    $vehicleHistory = new VehicleMaintenanceHistory();
                    $vehicleHistory->vehicle_id = $vehicle['id'];
                    $vehicleHistory->event_type_id = $nextServiceInspectionDistanceEvent->id;
                    $vehicleHistory->event_status = 'Incomplete';
                    $vehicleHistory->event_planned_distance = $serviceInspectionInterval;
                    // $vehicleHistory->odomerter_reading = $serviceInspectionInterval ? str_replace(",", "", $serviceInspectionInterval) : null;
                    $vehicleHistory->created_by = $user->id;
                    $vehicleHistory->updated_by = $user->id;
                    $vehicleHistory->save();
                // }
            }
        }
    }

    private function telematicsVehiclePush($dataArray){
        $url = env('TELEMATICS_SERVER_URL').'TelematicsVehiclePush.php';
        //callAPI('POST',$url,$data);
        $username = 'trackm8';
        $password = 'trackm8';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataArray));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        //print_r($output);
        curl_close($ch);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vehicleUserId = $id;
        $user = Auth::user();
        $vehicle = Vehicle::with('type', 'creator', 'updater', 'location', 'repair_location','division','region', 'nominatedDriver')->withTrashed()
                    ->where('id', $id)
                    ->first();

        if(!$vehicle || !$user->isHavingRegionAccess($vehicle->vehicle_region_id)) {
            return redirect('/vehicles');
        }

        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$id)->orderBy('id','DESC')->first();
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $user = \Auth::user();
        if($user->isUserInformationOnly()){
            $checkvehicleexist = Check::where('created_by',Auth::user()->id)->where('vehicle_id',$vehicle->id)->first();
            $defectvehicleexist = Defect::where('created_by',Auth::user()->id)->where('vehicle_id',$vehicle->id)->first();
            if(isset($checkvehicleexist) || isset($defectvehicleexist)){}
            else{
                return redirect('/vehicles');
            }
        }

        $files = $vehicle->getMedia();

        $vehicleVORLogData = DB::table('vehicle_vor_logs')->where(['vehicle_id'=> $vehicle->id, 'dt_back_on_road' => NULL])->select(DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"))->first();

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
        $comments = VehiclePlanningComment::with('creator')->where('vehicle_id', $id)->orderBy('comment_datetime', 'desc')->orderBy('id', 'desc')->get();
        foreach ($comments as $key => $comment) {
            if ($comment->creator == null) {
                $comment->creator = $user;
            }
        }

        $p11dReportHelper = new P11dReportHelper();
        $privateUseDays = $p11dReportHelper->calcVehiclePrivateUseDays($vehicle->id, $vehicle->nominated_driver);
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);

        $currentYearValue = substr($currTaxYearParts[1], 2);
        $currentYearFormat = $currTaxYearParts[0] . '-' . $currentYearValue;

        // vehicle cost summary period field
        $currentYear = Carbon::now();
        $lastMonth = Carbon::now()->subMonths(1)->format('M Y');
        $previousMonth = Carbon::now()->subMonth(12)->format('Y-m-d');
        $listCurrentYear = (new \DateTime($currentYear))->modify('first day of this month');
        $listPreviousMonth = (new \DateTime($previousMonth))->modify('first day of this month');
        $monthInterval = \DateInterval::createFromDateString('1 month');

        $period = new \DatePeriod($listPreviousMonth, $monthInterval, $listCurrentYear);
        $displayMonthYearArray = [];
        //$maintenanceEventTypes = config('config-variables.maintenanceEventTypes');

        $hiddenSlug = 'next_service_inspection';

        if ($vehicle->type->service_interval_type == 'Time') {
            $hiddenSlug = 'next_service_inspection_distance';
        }

        $maintenanceEventTypesForSearchOption = MaintenanceEvents::whereIn('is_standard_event',[0,1])->where('slug','!=',$hiddenSlug)->orderBy('name')->get();
        $maintenanceEventTypesForSearchOption = $maintenanceEventTypesForSearchOption->lists('name','id')->toArray();
        $maintenanceEventTypesForSearchOption = ['' => 'All events'] + $maintenanceEventTypesForSearchOption;

        $maintenanceEventTypes = MaintenanceEvents::where('is_standard_event', 0)->where('slug','!=',$hiddenSlug)->orderBy('name')->get();
        //asort($maintenanceEventTypes);

        $maintenanceHistoryEventTypes = $maintenanceEventTypes->lists('name','id')->toArray();

        $maintenanceHistoryEventTypes = ['' => 'All events'] + $maintenanceHistoryEventTypes;

        foreach ($period as $month) {
            $displayMonthYearArray[] = $month->format("M Y");
        }
        $displayMonthYear = array_reverse($displayMonthYearArray);
        // $data=$this->vehicleService->getData();
        $data=$this->vehicleService->getDataDivRegLoc();
        asort($data['vehicleDivisions']);
        $vehicleDivisions = $data['vehicleDivisions'];
        asort($data['vehicleRegions']);
        $vehicleRegions = $data['vehicleRegions'];
        //$vehicleLocation = ['' => ''] + $data['vehicleBaseLocations'];
        $vehicleLocation = [];

        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleRegions'] as $regionId => $regions) {
                asort($regions);
                $data['vehicleRegions'][$regionId] = $regions;
            }
        } else {
            asort($data['vehicleRegions']);
        }

        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleBaseLocations'] as $locationId => $locations) {
                asort($locations);
                $data['vehicleBaseLocations'][$locationId] = $locations;
            }
        } else {
            asort($data['vehicleBaseLocations']);
        }

        $vehicleBaseLocationsArray = [];
        foreach ($data['vehicleBaseLocations'] as $locationId => $location) {
            if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
                $k = 0;
                foreach ($location as $key => $value) {
                    $vehicleBaseLocationsArray[$locationId][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                $vehicleBaseLocationsArray[] = ['id'=> $locationId, 'text' => $location];
            }
        }

        $vehicleRegionsArray = [];
        foreach ($vehicleRegions as $regionId => $region) {
            if(env('IS_DIVISION_REGION_LINKED_IN_USER')) {
                $k = 0;
                foreach ($region as $key => $value) {
                    $vehicleRegionsArray[$regionId][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                $vehicleRegionsArray[] = ['id'=> $regionId, 'text' => $region];
            }
        }

        $vehicleDisplay = request()->get('vehicleDisplay');

        $vehicleTaxValueData = VehicleType::withTrashed()->where('id', $vehicle->type->id)->first();
        $vehicleTaxArray = json_decode($vehicleTaxValueData->vehicle_tax, true);

        $vehicleTaxValue = 0;
        if(!is_null($vehicle->type->vehicle_tax) && $vehicle->type->vehicle_tax != ''){
            //$vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicle->type->vehicle_tax,$vehicleId,null,null);
            $commenHelper = new Common();
            $vehicleTaxCurrentData = $commenHelper->getFleetCostValueForDate($vehicle->type->vehicle_tax,Carbon::now()->format('Y-m-d'));
            $vehicleTaxValue = $vehicleTaxCurrentData['currentCost'];
            // $currentDate = $vehicleTaxCurrentData['currentDate'];
        }

        $pmitIntervalWeeks = $vehicle->type->pmi_interval;
        $currentDate = Carbon::now();

        $assignmentDeleteRecordId = [];
        $assignmentHistory = VehicleAssignment::selectRaw('GROUP_CONCAT(vehicle_assignment.id ORDER BY vehicle_assignment.id DESC) as assignmentId')
                                ->where('vehicle_id', $vehicle->id)
                                ->groupBy('from_date')
                                ->havingRaw('COUNT(vehicle_assignment.id)>1')
                                ->orderBy('id','DESC')
                                ->get();

        foreach ($assignmentHistory as $assignment) {
            $assignmentId = explode(",",$assignment->assignmentId);
            unset($assignmentId[0]);
            $assignmentDeleteRecordId = array_merge($assignmentDeleteRecordId,$assignmentId);
        }

        // $docList = $this->getVehicleDocs($vehicle);
        JavaScript::put([
            'isUserInformationOnly' => $user->isUserInformationOnly(),
            'vehicleUserId' => $vehicleUserId,
            'vehicle' => $vehicle,
            'authUserName' => $user->first_name . ' ' . $user->last_name,
            'maintenanceEventTypes' => $maintenanceEventTypes,
            'vehicleDivisions'=>$vehicleDivisions,
            'vehicleBaseLocations' => $vehicleBaseLocationsArray,
            'vehicleRegions' => $vehicleRegionsArray,
            'pmitIntervalWeeks' => $pmitIntervalWeeks,
            'maintenanceHistoryEventTypes' => $maintenanceHistoryEventTypes,
            'assignmentDeleteRecordId' => $assignmentDeleteRecordId,
            'isLocationLinkedInVehicle'=>env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'),
            'isRegionLinkedInVehicle'=>env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'dt_added_to_fleet' => $vehicle->dt_added_to_fleet,
            'vehicle_type' => $vehicle->type->service_interval_type,
            // 'docList' => $docList
        ]);

        /*$maintenanceHistory = (object)VehicleMaintenanceHistory::selectRaw('MAX(event_date) as event_date, MAX(event_plan_date) as event_plan_date,  slug as event_type,name as event_name')
                                ->leftjoin('maintenance_events','maintenance_events.id','=','vehicle_maintenance_history.event_type_id')
                                ->where('vehicle_id', $vehicle->id)
                                ->groupBy('event_type_id')
                                ->get()->keyBy('event_type')
                                ->toArray();*/



        $maintenanceEvents = MaintenanceEvents::where('is_standard_event',1)->get();

        $vehicleMaintenancehistory = VehicleMaintenanceHistory::with('eventType')->where('vehicle_id',$id)
            ->whereIn('event_type_id',$maintenanceEvents->pluck('id')->toArray())
            ->where('event_status','Complete')
            ->orderBy('event_date','DESC')->get();

        $vehicleMaintenancehistory = $vehicleMaintenancehistory->groupBy('event_type_id');

        $maintenanceHistory = [];

        foreach ($maintenanceEvents as $event) {

            if (isset($vehicleMaintenancehistory[$event->id]))
            $maintenanceHistory[$event->slug] = $vehicleMaintenancehistory[$event->id]->first();
        }

        $maintenanceHistory = (object)$maintenanceHistory;

        $isFirstPmiEventComplete = false;

        $pmiEventId = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();
        $firstPMIEvent = VehicleMaintenanceHistory::where('event_type_id',$pmiEventId->id)
            ->where('event_plan_date',Carbon::parse($vehicle->first_pmi_date)->format('Y-m-d'))
            ->where('vehicle_id',$vehicle->id)
            ->where('event_status','Complete')
            ->first();

        if ($firstPMIEvent) {
            $isFirstPmiEventComplete = true;
        }


        $vehicleMaintenancehistory = $vehicleMaintenancehistory->groupBy('event_type_id');

        if ($vehicle->type->service_interval_type == 'Distance') {
            $distanceEvent = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();
            $vehicleNextDistanceEvent = VehicleMaintenanceHistory::with('eventType')->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Incomplete')
                ->where('event_planned_distance', $vehicle->next_service_inspection_distance)
                ->orderBy('event_plan_date', 'DESC')->orderBy('event_date', 'DESC')->first();

            $vehicleCompletedNextDistanceEvent = VehicleMaintenanceHistory::with('eventType')
                ->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Complete')
                // ->orderBy('event_plan_date', 'DESC')
                ->orderBy('event_date', 'DESC')
                ->orderBy('id', 'desc')
                ->first();

            $isDistanceBanIcon = false;

            $lastServiceDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);

            $vehicleCompletedNextDistanceEventCheckIcon = VehicleMaintenanceHistory::with('eventType')
                ->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Incomplete')
                ->where('event_planned_distance', $lastServiceDistance)
                // ->orderBy('event_plan_date', 'DESC')
                ->orderBy('event_date', 'DESC')
                ->orderBy('id', 'desc')
                ->first();

            if ($vehicleCompletedNextDistanceEventCheckIcon) {
                $isDistanceBanIcon = true;
            }

        } else {
            $vehicleNextDistanceEvent = null;
            $vehicleCompletedNextDistanceEvent = null;
            $isDistanceBanIcon = false;
        }

        $vehicleMaintenancehistory  = VehicleMaintenanceHistory::where('vehicle_id',$id)->first();

        $selectedTab = isset($_COOKIE['vehicleShowRefTab']) ? str_replace("#", "", $_COOKIE['vehicleShowRefTab']) : 'vehicle_summary';

        $maintenanceHistoryStatus = config('config-variables.maintenance_history_status');

        $isDVSAConfigurationTabEnabled = $this->vehicleService->isDVSAConfigurationTabEnabled();

        // change date format
        $installationDate = (isset($vehicle->installation_date)) ? Carbon::parse($vehicle->installation_date)->format('d M Y') : '';
        $telematicsJourney = TelematicsJourneys::where('vehicle_id',$id)->orderBy('id', 'desc')->first();
        $lastDateUpdateDevice = 'N/A';
        if(!empty($telematicsJourney)) {
            $lastDateUpdateDevice = Carbon::parse($telematicsJourney['updated_at'])->format('h:i:s d M Y');
        }

        return view('vehicles.show', compact(
            'vehicle',
            'files',
            'vorDuration',
            'privateUseDays',
            'vehicleDisplay',
            'displayMonthYear',
            'vehicleTaxValue',
            'maintenanceEventTypes',
            'comments',
            'vehicleMaintenancehistory',
            'maintenanceHistory',
            'selectedTab',
            'maintenanceHistoryStatus',
            'maintenanceHistoryEventTypes',
            'maintenanceEventTypesForSearchOption',
            'currentDate',
            'vehicleDivisions',
            'vehicleRegions',
            'vehicleLocation',
            'isDVSAConfigurationTabEnabled',
            'vehicleNextDistanceEvent',
            'vehicleCompletedNextDistanceEvent',
            'isDistanceBanIcon',
            'vehicleCompletedNextDistanceEventCheckIcon',
            'isFirstPmiEventComplete',
            'installationDate',
            'lastDateUpdateDevice'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        
        if(!$user->isHavingRegionAccess($vehicle->vehicle_region_id)) {
            return redirect('/vehicles');
        }
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$id)->orderBy('id','DESC')->first();
        $data=$this->vehicleService->getDataDivRegLoc();
        asort($data['vehicleDivisions']);

        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleRegions'] as $regionId => $regions) {
                asort($regions);
                $data['vehicleRegions'][$regionId] = $regions;
            }
        } else {
            asort($data['vehicleRegions']);
        }

        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleBaseLocations'] as $locationId => $locations) {
                asort($locations);
                $data['vehicleBaseLocations'][$locationId] = $locations;
            }
        } else {
            asort($data['vehicleBaseLocations']);
        }

        $vehicleDivisions=$data['vehicleDivisions'];
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isInsuranceCostOverride = $vehicle->is_insurance_cost_override;
        $isTelematicsCostOverride = $vehicle->is_telematics_cost_override;

        if($vehicle->type->profile_status == "Archived"){
            $vehicleTypes = VehicleType::orderBy('vehicle_type', 'asc')->withTrashed()->lists('vehicle_type', 'id')->toArray();
        }
        else{
            $vehicleTypes = VehicleType::orderBy('vehicle_type', 'asc')->lists('vehicle_type', 'id')->toArray();
        }
        // $vehicleTypes = VehicleType::orderBy('vehicle_type', 'asc')->lists('vehicle_type', 'id')->toArray();
        $vehicleTypesList = ['' => ''] + $vehicleTypes;

        //$userList = ['' => ''] + User::orderBy('email', 'asc')->lists('email', 'id')->toArray();
        $userList = ['' => ''] + User::where('job_title', '!=', 'Workshop User')->where('is_disabled', 0)->select(\DB::raw("CONCAT(first_name,' ', last_name, CASE WHEN email IS NULL THEN '' ELSE IF(LOCATE('".env('BRAND_NAME')."-imastr', email) > 0, '', CONCAT(' (', email, ')')) END) AS full_name, id"))->orderBy('full_name', 'asc')->lists('full_name', 'id')->toArray();

        $vehicleNominatedDriver = vehicle::all()->unique('nominated_driver')->pluck('nominated_driver')->toArray();

        $vehicleLocations = VehicleLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        //$vehicleLocationsList = collect(['' => ''])->merge($vehicleLocations);
        $vehicleLocationsList = ['' => ''] + $vehicleLocations;

        $vehicleRepairLocations = VehicleRepairLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        //$vehicleRepairLocationsList = collect(['' => ''])->merge($vehicleRepairLocations);
        $vehicleRepairLocationsList = ['' => ''] + $vehicleRepairLocations;

        $vehicleType = VehicleType::withTrashed()->findOrFail($vehicle->type->id);
        $vehicleTypeStatus = ($vehicleType->profile_status == "Active")? 0 : 1 ;

        $vehicleStatusList = config('config-variables.vehicleStatus');
        $ownershipStatusList = config('config-variables.ownershipStatus');
        $usageTypeList = config('config-variables.usageType');

        if (Gate::allows('archived.vehicle')) {
            $vehicleStatusList = config('config-variables.vehicleStatus');
        }
        if($user->isSuperAdmin()){
            unset($vehicleStatusList['Other']);
            $vehicleStatusList['Archived']='Archived';
            $vehicleStatusList['Archived - De-commissioned']='Archived - De-commissioned';
            $vehicleStatusList['Archived - Written off']='Archived - Written off';
            $vehicleStatusList['Archived - Sold']='Archived - Sold';
            ksort($vehicleStatusList);
            $vehicleStatusList['Other']='Other';

        }
        $p11dReportHelper = new P11dReportHelper();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);
        $currentYearValue = substr($currTaxYearParts[1], 2);
        $currentYearFormat = $currTaxYearParts[0] . '-' . $currentYearValue;

        $privateUseLogs = PrivateUseLogs::where(['vehicle_id'=>$vehicle->id, 'end_date'=>null])->first();

        $vehicleVORLogData = VehicleVORLog::orderBy('id','DESC')->where('vehicle_id',$vehicle->id)->first();

        $vehicleDateOffRoad = (isset($vehicleVORLogData->dt_off_road)) ? Carbon::parse($vehicleVORLogData->dt_off_road)->format('d M Y') : '';

        $isSuperAdmin = $user->isSuperAdmin();

        $vehicleVORLogData = VehicleVORLog::orderBy('id','DESC')->where('vehicle_id',$vehicle->id)->first();

        $vehicleDateOffRoad = (isset($vehicleVORLogData->dt_off_road)) ? Carbon::parse($vehicleVORLogData->dt_off_road)->format('d M Y') : '';

        $vehicleStatusRecords = defect::with('vehicle','defectMaster')->where('vehicle_id', $vehicle->id)->where('status','!=','Resolved')->get();
        asort($data['vehicleRegions']);
        $vehicleRegions = $data['vehicleRegions'];
        // $vehicleBaseLocations = config('config-variables.vehicleLocation');
        // $vehicleRegions = config('config-variables.vehicleRegions');

        // $vehicleDivisionArray = array();
        // foreach (config('config-variables.vehicleDivisions') as $key => $value) {
        //     array_push($vehicleDivisionArray, ['id'=>$key, 'text'=>$value]);
        // }

        // $vehicleRegionArray = array();
        // foreach ($vehicleRegions as $key => $value) {
        //     array_push($vehicleRegionArray, ['id'=>$key, 'text'=>$value]);
        // }

        // $vehicleBaseLocationsArray = array();
        // foreach ($vehicleLocations as $key => $value) {
        //     array_push($vehicleBaseLocationsArray, ['id'=>$key, 'text'=>$value]);
        // }

        $nominatedDriverArray = array();
        $usersList = User::where('job_title', '!=', 'Workshop User')->where('is_disabled', 0)->select(\DB::raw("CONCAT(first_name,' ', last_name, CASE WHEN email IS NULL THEN CONCAT(' (', username, ')') ELSE IF(LOCATE('".env('BRAND_NAME')."-imastr', email) > 0, CONCAT(' (', username, ')'), CONCAT(' (', email, ')')) END) AS full_name, id"))->orderBy('full_name', 'asc')->lists('full_name', 'id')->toArray();
        foreach ($usersList as $key => $user) {
            if(in_array($key, $vehicleNominatedDriver) && $key != $vehicle->nominated_driver){
                array_push($nominatedDriverArray, ['id'=>$key, 'text'=>$user , 'disabled'=>true]);
            }else{
                array_push($nominatedDriverArray, ['id'=>$key, 'text'=>$user , 'selected'=>true]);
            }
        }

        $vehicleRepairLocationsArray = array();
        foreach ($vehicleRepairLocations as $key => $value) {
            if(in_array($key, $vehicleRepairLocations) && $key != $vehicle->vehicle_repair_location_id){
                array_push($vehicleRepairLocationsArray, ['id'=>$key, 'text'=>$value, 'disabled'=>true]);
            }else{
                array_push($vehicleRepairLocationsArray, ['id'=>$key, 'text'=>$value, 'selected'=>true]);
            }
        }

        $vehicleTypesArray = array();
        foreach ($vehicleTypes as $key => $value) {
            array_push($vehicleTypesArray, ['id'=>$key, 'text'=>$value]);
        }

        $vehicleStatusArray = array();
        foreach ($vehicleStatusList as $key => $value) {
            array_push($vehicleStatusArray, ['id'=>$key, 'text'=>$value]);
        }

        $motEvent = MaintenanceEvents::where('slug','mot')->first();

        $vehicleMaintenanceHistoryData = VehicleMaintenanceHistory::where('vehicle_id',$id)->where('event_type_id','=',$motEvent->id)->get();

        $firstPmiDate = $vehicle->next_pmi_date;
        $monthlyInsuranceOverride = $vehicle->is_insurance_cost_override;

        $currentDate = Carbon::now();
        $currentMonthStartEndDateArray = [$currentDate->startOfMonth()->format('Y-m-d'), $currentDate->endOfMonth()->format('Y-m-d')];
        $odometerMilesPerMonthValue = 0;

        $monthlyOdometerReadings = Check::select('odometer_reading')
                        ->where('vehicle_id',$id)
                        ->whereIn('type',['Vehicle Check','Return Check'])
                        ->whereBetween('created_at',$currentMonthStartEndDateArray)
                        ->orderBy('created_at','DESC')->get();

        if ($monthlyOdometerReadings != null && !empty($monthlyOdometerReadings) && count($monthlyOdometerReadings) >= 2) {
            $odometerMilesPerMonthValue = $monthlyOdometerReadings->first()['odometer_reading'] - $monthlyOdometerReadings->last()['odometer_reading'];
        }

        $commonHelper = new Common();

        $vehicleTaxValue = 0;
        if(!is_null($vehicle->type->vehicle_tax) && $vehicle->type->vehicle_tax != ''){
            $vehicleTaxCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->type->vehicle_tax,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet);
            //$vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicle->type->vehicle_tax,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A','N/A','vehicle_tax');
            $vehicleTaxValue = $vehicleTaxCurrentData['currentCost'];
            // $currentDate = $vehicleTaxCurrentData['currentDate'];
        }

        $maintenanceCurrentCost = 0;
        $maintenanceCurrentDate = '';
        $maintenanceCurrentDateValue = '';
        if(isset($vehicle->maintenance_cost)){

            $maintenanceCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->maintenance_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
            //$maintenanceCurrentData = $this->calcMonthlyCurrentData($vehicle->maintenance_cost,$vehicleId,$vehicleArchiveHistory);
            $maintenanceCurrentCost = $maintenanceCurrentData['currentCost'];
            $maintenanceCurrentDate = $maintenanceCurrentData['currentDate'];
            // $maintenanceCurrentDateValue = $maintenanceCurrentData['currentDateValue'];
        }
        $vehicleMaintenance = json_decode($vehicle->maintenance_cost, true);
        if(isset($vehicleMaintenance)){
            foreach ($vehicleMaintenance as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $maintenanceCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $leaseCurrentCost = 0;
        $leaseCurrentDate = '';
        $leaseCurrentDateValue = '';
        if(isset($vehicle->lease_cost)){
            $leaseCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->lease_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
//            $leaseCurrentData = $this->calcMonthlyCurrentData($vehicle->lease_cost,$vehicleId,$vehicleArchiveHistory);
            $leaseCurrentCost = $leaseCurrentData['currentCost'];
            $leaseCurrentDate = $leaseCurrentData['currentDate'];
            $leaseCurrentDateValue = $leaseCurrentData['currentDateValue'];
        }

        $vehicleLease = json_decode($vehicle->lease_cost, true);
        if(isset($vehicleLease)){
            foreach ($vehicleLease as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $leaseCurrentDate = $fleetCost['cost_from_date'];
                }
            }
        }

        //Monthly insurance cost
        $monthlyInsuranceData = '';
        $monthlyInsurance = Settings::where('key', 'fleet_cost_area_detail')->first();
        $monthlyInsuranceJson = $monthlyInsurance->value;
        $monthlyInsuranceData = json_decode($monthlyInsuranceJson, true);
        $insuranceValue = '';
        $insuranceValueDisplay = [];
        // if(isset($monthlyInsuranceData['annual_insurance_cost'])){
        //     if($vehicle->is_insurance_cost_override != 1) {
        //         $insuranceValue = json_encode($monthlyInsuranceData['annual_insurance_cost']);
        //         $insuranceValueDisplay = $monthlyInsuranceData['annual_insurance_cost'];

        //     } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost == ''){
        //             $insuranceValue = json_encode($monthlyInsuranceData['annual_insurance_cost']);
        //             $insuranceValueDisplay = $monthlyInsuranceData['annual_insurance_cost'];
        //     }

        //     $finalInsuranceValueDisplay = [];

        //     foreach ($insuranceValueDisplay as $row) {
        //         if ($row['cost_to_date'] != '' && \Carbon\Carbon::parse($row['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet))) {
        //             // Do nothing
        //         } else {
        //             array_push($finalInsuranceValueDisplay, $row);
        //         }
        //     }

        //     $insuranceValueDisplay = $finalInsuranceValueDisplay;
        // }

        if(isset($vehicle->insurance_cost) && $vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost != ''){
                $insuranceValue = $vehicle->insurance_cost;
                $insuranceValueDisplay = json_decode($vehicle->insurance_cost,true);
        } else {
            $insuranceValue = $vehicle->type->annual_insurance_cost;
            $insuranceValueDisplay = json_decode($insuranceValue, true);
            $finalInsuranceValueDisplay = [];

            if(isset($insuranceValueDisplay)) {
                foreach ($insuranceValueDisplay as $row) {
                    if ($row['cost_to_date'] != '' && \Carbon\Carbon::parse($row['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet))) {
                        // Do nothing
                    } else {
                        array_push($finalInsuranceValueDisplay, $row);
                    }
                }
            }

            $insuranceValueDisplay = $finalInsuranceValueDisplay;
        }

        $insuranceFieldCurrentCost = 0;
        $insuranceFieldCurrentDate = '';
        $insuranceFieldCurrentDateValue = '';
        if(isset($insuranceValue) && $insuranceValue != ""){
            $insuranceCurrentData = $commonHelper->getFleetCostValueForDate($insuranceValue,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride);
//            $insuranceCurrentData = $this->calcMonthlyCurrentData($insuranceValue,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,'N/A','ins_cost');
            $insuranceFieldCurrentCost = $insuranceCurrentData['currentCost'];
            $insuranceFieldCurrentDate = $insuranceCurrentData['currentDate'];
            // $insuranceFieldCurrentDateValue = $insuranceCurrentData['currentDateValue'];
        }

        $vehicleInsuranceValue = json_decode($insuranceValue, true);
        if(isset($vehicleInsuranceValue)){
            foreach ($vehicleInsuranceValue as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $insuranceFieldCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $telematicsValue = '';
        $telematicsValueDisplay = [];
        if(isset($monthlyInsuranceData['telematics_insurance_cost'])){
            if($vehicle->is_telematics_cost_override != 1) {
                $telematicsValue = json_encode($monthlyInsuranceData['telematics_insurance_cost']);
                $telematicsValueDisplay = $monthlyInsuranceData['telematics_insurance_cost'];
            } else if($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost == ''){
                $telematicsValue = json_encode($monthlyInsuranceData['telematics_insurance_cost']);
                $telematicsValueDisplay = $monthlyInsuranceData['telematics_insurance_cost'];
            } else {
                $telematicsValue = json_encode($monthlyInsuranceData['telematics_insurance_cost']);
            }

            $finalTelematicsValueDisplay = [];
            foreach (json_decode($telematicsValue,true) as $row) {
                if ($row['cost_to_date'] != '' && \Carbon\Carbon::parse($row['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet))) {
                    // Do nothing
                } else {
                    array_push($finalTelematicsValueDisplay, $row);
                }
            }

            $telematicsValue = $finalTelematicsValueDisplay;
        }

        if(isset($vehicle->telematics_cost)){
            if($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost != ''){
                    $telematicsValue = $vehicle->telematics_cost;
                    $telematicsValueDisplay = json_decode($vehicle->telematics_cost,true);
            }
        } else {
            $telematicsValue = json_encode($telematicsValue);
        }

        $telematicsFieldCurrentCost = 0;
        $telematicsFieldCurrentDate = '';
        $telematicsFieldCurrentDateValue = '';
        if(isset($telematicsValue) && $telematicsValue != ""){
            $telematicsCurrentData = $commonHelper->getFleetCostValueForDate($telematicsValue,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isTelematicsCostOverride);
//            $telematicsCurrentData = $this->calcMonthlyCurrentData($telematicsValue,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A',$isTelematicsCostOverride);
            $telematicsFieldCurrentCost = $telematicsCurrentData['currentCost'];
            $telematicsFieldCurrentDate = $telematicsCurrentData['currentDate'];
        }

        $vehicleTelematicsValue = json_decode($telematicsValue, true);
        if(isset($vehicleTelematicsValue) && $vehicleTelematicsValue != ""){
            foreach ($vehicleTelematicsValue as $fleetCost) {
                $currentDate = Carbon::now();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $telematicsFieldCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $depreciationCurrentCost = 0;
        $depreciationCurrentDate = '';
        $depreciationCurrentDateValue = '';
        if(isset($vehicle->monthly_depreciation_cost)){
            $depreciationData = $commonHelper->getFleetCostValueForDate($vehicle->monthly_depreciation_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
            //$depreciationData = $this->calcMonthlyCurrentData($vehicle->monthly_depreciation_cost,$vehicleId,$vehicleArchiveHistory);
            $depreciationCurrentCost = $depreciationData['currentCost'];
            $depreciationCurrentDate = $depreciationData['currentDate'];
            // $depreciationCurrentDateValue = $depreciationData['currentDateValue'];
        }

        $vehicleDepreciation = json_decode($vehicle->monthly_depreciation_cost, true);
        if(isset($vehicleDepreciation) && $vehicleDepreciation != ""){
            foreach ($vehicleDepreciation as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $depreciationCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $depreciationDataDisplay = json_decode($vehicle->monthly_depreciation_cost,true);

        $maintenanceHistoryPMI = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();
        $pmiMaitenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceHistoryPMI->id)
                                                        ->where('vehicle_id',$vehicle->id)
                                                        ->whereNotNull('event_date')
                                                        ->orderBy('event_date', 'DESC')
                                                        ->first();

        $telematicsCostOverride = $vehicle->is_telematics_cost_override;
        $insuranceCostOverride = $vehicle->is_insurance_cost_override;

        $calculateNextServiceOdometer = true;

        $nextServiceDistanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
        $serviceInspectionDistanceEvents = VehicleMaintenanceHistory::where('event_type_id',$nextServiceDistanceEvent->id)
            ->where('vehicle_id',$vehicle->id)->count();
        if ($serviceInspectionDistanceEvents > 0) {
            $calculateNextServiceOdometer = false;
        }

        // if user has right to edit telematics data
        $isConfigurationTabEnabled = 0;
        if (auth()->user()->checkRole('App version handling')) {
            $isConfigurationTabEnabled = 1;
        }

        
        $supplierTelematics = config('config-variables.supplier_telematics');
        $deviceTelematics = config('config-variables.device_telematics');
        // change date format
        $installationDate = (isset($vehicle->installation_date)) ? Carbon::parse($vehicle->installation_date)->format('d M Y') : '';
        $telematicsJourney = TelematicsJourneys::where('vehicle_id',$id)->orderBy('id', 'desc')->first();
        $lastDateUpdateDevice = 'N/A';
        if(!empty($telematicsJourney)) {
            $lastDateUpdateDevice = Carbon::parse($telematicsJourney['updated_at'])->format('h:i:s d M Y');
        }
        $fromPage = "edit";

        $vehicleBaseLocationsArray = [];
        foreach ($data['vehicleBaseLocations'] as $locationId => $location) {
            if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
                $k = 0;
                foreach ($location as $key => $value) {
                    $vehicleBaseLocationsArray[$locationId][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                $vehicleBaseLocationsArray[] = ['id'=> $locationId, 'text' => $location];
            }
        }

        $vehicleRegionsArray = [];
        foreach ($vehicleRegions as $regionId => $region) {
            if(env('IS_DIVISION_REGION_LINKED_IN_USER')) {
                $k = 0;
                foreach ($region as $key => $value) {
                    $vehicleRegionsArray[$regionId][$k] = ['id'=> $key, 'text' => $value];
                    $k++;
                }
            } else {
                $vehicleRegionsArray[] = ['id'=> $regionId, 'text' => $region];
            }
        }

        $vehicleAdHocCosts = config('config-variables.vehicle_ad_hoc_costs');

        JavaScript::put([
            'status' => $vehicleTypeStatus,
            'vehicleStatusRecords' => $vehicleStatusRecords,
            'vehicleLocationSelectedId' => $vehicle->vehicle_location_id,
            'vehicleBaseLocations'=>$vehicleBaseLocationsArray,
            'vehicleRegions'=>$vehicleRegionsArray,
            'vehicleDivisions'=>$data['vehicleDivisions'],
            //'vehicleBaseLocations'=>$vehicleBaseLocations,
            //'vehicleRegions'=>$vehicleRegions,
            'nominatedDriverList' => $nominatedDriverArray,
            //'vehicleDivisionList' => $vehicleDivisionArray,
            //'vehicleRegionList' => $vehicleRegionArray,
            //'vehicleBaseLocationsList' => $vehicleBaseLocationsArray,
            'vehicleRepairLocationsList' => $vehicleRepairLocationsArray,
            'pmiMaitenanceHistory' => $pmiMaitenanceHistory,
            'vehicleTypesList' => $vehicleTypesArray,
            'vehicleStatusList' => $vehicleStatusArray,
            'brandName' => env('BRAND_NAME'),
            'isLocationLinkedInVehicle'=>env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'),
            'isRegionLinkedInVehicle'=>env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'vehicleId' => $id,
            'vehicleTypeId' => $vehicle->vehicle_type_id,
            'vehicleDivisionId' => $vehicle->vehicle_division_id,
            'vehicleRegionId' => $vehicle->vehicle_region_id,
            'vehicleLocationId' => $vehicle->vehicle_location_id,
            'vehicleMaintenanceHistoryData' => $vehicleMaintenanceHistoryData,
            'firstPmiDate' => $firstPmiDate,
            'monthlyInsuranceOverride' => $monthlyInsuranceOverride,
            'telematicsCostOverride' => $telematicsCostOverride,
            'insuranceCostOverride' => $insuranceCostOverride,
            'isConfigurationTabEnabled' => $isConfigurationTabEnabled,
            'fromPage' => $fromPage,
        ]);

        return view('vehicles.edit', compact('vehicleTypesList', 'vehicleLocationsList', 'vehicleRepairLocationsList', 'vehicleStatusList','vehicle', 'userList','vehicleNominatedDriver', 'usageTypeList','vehicleDateOffRoad', 'vehicleStatusRecords','odometerMilesPerMonthValue','isSuperAdmin','vehicleTaxValue','vehicleDivisions','vehicleRegions','monthlyInsuranceData','insuranceValueDisplay','telematicsValueDisplay','ownershipStatusList'))
            ->with('privateUseLogs',$privateUseLogs)
            ->with('hmrcTaxYear',$p11dReportHelper->calcTaxYear())
            ->with('maintenanceCurrentDate',$maintenanceCurrentDate)
            ->with('currentMonthMaintenanceCost',$maintenanceCurrentCost)
            ->with('leaseCurrentDate',$leaseCurrentDate)
            ->with('currentMonthLeaseCost',$leaseCurrentCost)
            ->with('vehicleType',$vehicleType)
            ->with('insuranceFieldCurrentCost',$insuranceFieldCurrentCost)
            ->with('insuranceFieldCurrentDate',$insuranceFieldCurrentDate)
            ->with('telematicsFieldCurrentCost',$telematicsFieldCurrentCost)
            ->with('telematicsFieldCurrentDate',$telematicsFieldCurrentDate)
            ->with('depreciationCurrentDate',$depreciationCurrentDate)
            ->with('depreciationCurrentCost',$depreciationCurrentCost)
            ->with('depreciationDataDisplay',$depreciationDataDisplay)
            ->with('insuranceFieldCurrentDateValue',$insuranceFieldCurrentDateValue)
            ->with('telematicsFieldCurrentDateValue',$telematicsFieldCurrentDateValue)
            ->with('maintenanceCurrentDateValue',$maintenanceCurrentDateValue)
            ->with('leaseCurrentDateValue',$leaseCurrentDateValue)
            ->with('depreciationCurrentDateValue',$depreciationCurrentDateValue)
            ->with('calculateNextServiceOdometer',$calculateNextServiceOdometer)
            ->with('isConfigurationTabEnabled',$isConfigurationTabEnabled)
            ->with('supplierTelematics',$supplierTelematics)
            ->with('installationDate',$installationDate)
            ->with('lastDateUpdateDevice',$lastDateUpdateDevice)
            ->with('deviceTelematics',$deviceTelematics)
            ->with('vehicleAdHocCosts',$vehicleAdHocCosts)
            ->with('fromPage',$fromPage);
    }
    public function calcMonthlyFieldCurrentData(Request $request)
    {
        //return $this->calcMonthlyCurrentData($request->field);
        $commonHelper = new Common();
        return $commonHelper->getFleetCostValueForDate($request->field,Carbon::now()->format('Y-m-d'));
    }

    private function calcMonthlyCurrentData($costs_json,$vehicleId=null,$vehicleArchiveHistory=null,$vehicleDtAddedToFleet=null,$isInsuranceCostOverride='N/A',$isTelematicsCostOverride='N/A',$typeOfCost=null){
        $commonHelper = new Common();
        $formated_month = Carbon::now()->format("M Y");
        return $commonHelper->calcMonthlyCurrentData($costs_json, $formated_month,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,true,$typeOfCost);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreVehicleRequest $request, $id)
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);

        $oldVehicleDivisionId = $vehicle->vehicle_division_id;
        $oldVehicleLocationId = $vehicle->vehicle_location_id;
        $oldVehicleRegionId = $vehicle->vehicle_region_id;
        $oldRegDt = $vehicle->dt_registration;
        $vehicle->registration = $request->registration;
        $p11dReportHelper = new P11dReportHelper();
        $oldVehicleStatus = $vehicle->status;

        $vehicleStatusArchived = config('config-variables.vehicleStatusArchived');
        $vehicleStatusUnArchived = config('config-variables.vahicleStatusUnArchived');

        // saving vehicle history if nominated driver gets changed
        if($vehicle->nominated_driver != $request->nominated_driver) {
            if($vehicle->nominated_driver) {
                // updating to date
                $vehicleUsageHistory = VehicleUsageHistory::where('user_id', $vehicle->nominated_driver)->where('vehicle_id', $id)->whereNull('to_date')->first();

                if($vehicleUsageHistory) {
                    $vehicleUsageHistory->to_date = Carbon::now();
                    $vehicleUsageHistory->save();
                }
                //updating privateuse log entry
                $privateUseLog = PrivateUseLogs::where(['vehicle_id'=>$vehicle->id, 'user_id'=>$vehicle->nominated_driver, 'end_date'=>null])->first();
                if($privateUseLog) {
                    $privateUseLog->end_date = Carbon::now()->format('d M Y');
                    $privateUseLog->save();
                }
            }

            if($request->nominated_driver) {
                // saving new vehicle usage history if nominated driver gets changed
                $vehicleHistory = new VehicleUsageHistory();
                $vehicleHistory->user_id = $request->nominated_driver ? $request->nominated_driver : NULL;
                $vehicleHistory->vehicle_id = $id;
                $vehicleHistory->from_date = Carbon::now();
                $vehicleHistory->save();
                if (isset($request->private_use)) {
                    $privateUse = new PrivateUseLogs();
                    $privateUse->vehicle_id = $vehicle->id;
                    $privateUse->user_id = $request->nominated_driver;
                    $privateUse->tax_year = $p11dReportHelper->calcTaxYear();
                    $privateUse->start_date = Carbon::now()->format('d M Y');
                    $privateUse->save();
                }
            }
        } else {
            if($request->privateuse_entry_flag == '1'){
                $privateUse = PrivateUseLogs::where(['vehicle_id'=>$vehicle->id, 'user_id'=>$vehicle->nominated_driver, 'end_date'=>null])->first();
                //$privateUse1 is the variable which is used so as to maintain single entry if user unchecks and checks again same day.
                $privateUse1 = PrivateUseLogs::where(['vehicle_id'=>$vehicle->id, 'user_id'=>$vehicle->nominated_driver, 'end_date'=>Carbon::now()->format('Y-m-d')])->first();
                if ($privateUse1 != null) {
                    $privateUse = $privateUse1;
                    $privateUse->end_date = null;
                }
                if($privateUse == null){
                    $privateUse = new PrivateUseLogs();
                    $privateUse->vehicle_id = $vehicle->id;
                    $privateUse->user_id = $vehicle->nominated_driver;
                    $privateUse->tax_year = $p11dReportHelper->calcTaxYear();
                }
                if (isset($request->private_use)) {
                    if ($privateUse1 == null) {//prevents reset of already existing entry
                        $privateUse->start_date = Carbon::now()->format('d M Y');
                    }
                } else {
                    $privateUse->end_date = Carbon::now()->format('d M Y');
                }
                $privateUse->save();
            }
        }

        // $vehicle->vehicle_category = $request->vehicle_category;
        // check if vehicle status got updated
        if (isset($request->status) && $vehicle->status !== $request->status) {
            // check if status change is archive related or not
            if(strtolower($vehicle->status) == 'archived' || strtolower($request->status) == 'archived'
                || strtolower($vehicle->status) == 'archived - de-commissioned' || strtolower($request->status) == 'archived - de-commissioned' || strtolower($vehicle->status) == 'archived - written off' || strtolower($request->status) == 'archived - written off' || strtolower($vehicle->status) == 'archived - sold' || strtolower($request->status) == 'archived - sold') {
                // check if user is permitted to archive or unarchive
                if (Gate::allows('archive.vehicle')) {
                    // store archived date and status in a variable before update it.
                    $archived_date = $vehicle->archived_date;
                    $status = $vehicle->status;
                    // check if vehicle just got unarchived
                    if(strtolower($vehicle->status) != 'archived' || strtolower($vehicle->status) != 'archived - de-commissioned' || strtolower($vehicle->status) != 'archived - written off' || strtolower($vehicle->status) != 'archived - sold') {
                        $vehicle->deleted_at = null;
                        $vehicle->status = $request->status;
                        $vehicle->archived_date = null;
                        $vehicle->save();
                    }
                    // check if vehicle just got archived
                    if(strtolower($request->status) == 'archived' || strtolower($request->status) == 'archived - de-commissioned' || strtolower($request->status) == 'archived - written off' || strtolower($request->status) == 'archived - sold') {
                        Vehicle::where('id', $id)->delete();
                        $vehicle->status = $request->status;
                        if(strtolower($status) == 'archived' || strtolower($status) == 'archived - de-commissioned' || strtolower($status) == 'archived - written off' || strtolower($status) == 'archived - sold') {
                            $vehicle->archived_date = $archived_date;
                        } else {
                            $vehicle->archived_date = Carbon::now()->format('Y-m-d');
                        }
                    }
                }
            }
            else {
                $vehicle->status = $request->status;
            }
        }
        $vehicle->dt_added_to_fleet = $request->dt_added_to_fleet;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->last_odometer_reading = $request->last_odometer_reading != "" ? $request->last_odometer_reading : 0;
        $vehicle->P11D_list_price = (!empty($request->P11D_list_price))?$request->P11D_list_price:null;
        $vehicle->dt_registration = $request->dt_registration;
        $vehicle->operator_license = (!empty($request->operator_license))?$request->operator_license:null;
        $vehicle->chassis_number = (!empty($request->chassis_number))?$request->chassis_number:null;
        $vehicle->contract_id = (!empty($request->contract_id))?$request->contract_id:null;
        $vehicle->notes = (!empty($request->notes))?$request->notes:null;
        //$vehicle->vehicle_division = (!empty($request->vehicle_division))?$request->vehicle_division:null;
        $vehicle->vehicle_division_id = (!empty($request->vehicle_division_id))?$request->vehicle_division_id:null;
        $vehicle->vehicle_location_id = (!empty($request->vehicle_location_id))?$request->vehicle_location_id:null;
        $vehicle->lease_expiry_date = (!empty($request->lease_expiry_date))?$request->lease_expiry_date:null;
        $vehicle->dt_loler_test_due = (!empty($request->dt_loler_test_due))?$request->dt_loler_test_due:null;
        $vehicle->dt_first_use_inspection = (!empty($request->dt_first_use_inspection))?$request->dt_first_use_inspection:null;
        $vehicle->dt_vehicle_disposed = (!empty($request->dt_vehicle_disposed))?$request->dt_vehicle_disposed:null;
        //$vehicle->vehicle_region = $request->vehicle_region;
        $vehicle->vehicle_region_id = $request->vehicle_region_id;
        $vehicle->vehicle_repair_location_id = (!empty($request->vehicle_repair_location_id))?$request->vehicle_repair_location_id:null;
        $vehicle->dt_repair_expiry = $request->dt_repair_expiry;

        $vehicle->dt_mot_expiry = $request->dt_mot_expiry!='' ? $request->dt_mot_expiry : null;
        $vehicle->dt_tax_expiry = $request->dt_tax_expiry;
        $vehicle->dt_annual_service_inspection = $request->dt_annual_service_inspection;
        $vehicle->dt_next_service_inspection = $request->dt_next_service_inspection;

        $vehicle->annual_vehicle_cost = (!empty(str_replace(",", "",$request->annual_vehicle_cost)))?str_replace(",", "",$request->annual_vehicle_cost):null;
        $vehicle->monthly_lease_cost = (!empty(str_replace(",", "",$request->monthly_lease_cost)))?str_replace(",", "",$request->monthly_lease_cost):null;

        $vehicle->permitted_annual_mileage = (!empty(str_replace(",", "",$request->permitted_annual_mileage)))?str_replace(",", "",$request->permitted_annual_mileage):null;
        $vehicle->excess_cost_per_mile = (!empty(str_replace(",", "",$request->excess_cost_per_mile)))?str_replace(",", "",$request->excess_cost_per_mile):null;
        $vehicle->staus_owned_leased = $request->staus_owned_leased;
        $vehicle->next_pto_service_date = $request->next_pto_service_date;
        $vehicle->next_invertor_service_date = $request->next_invertor_service_date;
        $vehicle->next_compressor_service = $request->next_compressor_service;
        $vehicle->adr_test_date = $request->adr_test_date;

        /* if($request->staus_owned_leased == 'Leased') {
            $vehicle->monthly_depreciation_cost = null;
        } else if($request->staus_owned_leased == 'Owned') {
            $vehicle->lease_cost = null;
        } else if($request->staus_owned_leased == 'Contract' || $request->staus_owned_leased == 'Hired') {
            $vehicle->maintenance_cost = null;
            $vehicle->monthly_depreciation_cost = null;
        } else if($request->staus_owned_leased == 'Hire purchase') {
            $vehicle->monthly_depreciation_cost = null;
        }*/


        if (!empty($request->nominated_driver)) {
            $vehicle->nominated_driver = $request->nominated_driver;
        } else {
            $vehicle->nominated_driver = null;
        }
        $vehicle->on_road = $request->on_road;
        $vehicle->masternaut = $request->masternaut;
        $vehicle->is_telematics_enabled = $request->is_telematics_enabled;

        // check if user has right to change the telematics data
        $isConfigurationTabEnabled = 0;
        if (auth()->user()->checkRole('App version handling')) {
            $isConfigurationTabEnabled = 1;
        }
        
        if($request->is_telematics_enabled == 1 && $isConfigurationTabEnabled == 1) {
            $vehicle->supplier = $request->supplier;
            $vehicle->device = $request->device;
            $vehicle->serial_id = $request->serial_id;
            $vehicle->installation_date = Carbon::createFromFormat('d M Y',$request->installation_date)->format('Y-m-d');
        } elseif($request->is_telematics_enabled == 0) {
            $vehicle->supplier = NULL;
            $vehicle->device = NULL;
            $vehicle->serial_id = NULL;
            $vehicle->installation_date = NULL;
        }     
        $vehicle->webfleet_object_id = $request->webfleet_object_id;
        $vehicle->usage_type = (!empty($request->usage_type))?$request->usage_type:null;
        $vehicle->updated_by = Auth::id();

        $vechileCategory = VehicleType::select(['vehicle_category'])
                           ->withTrashed()->find($request->vehicle_type_id);

        $vechileCategory =  $vechileCategory->vehicle_category;
        $vehicle->first_pmi_date = $request->first_pmi_date;
        $vehicle->next_pmi_date = $request->next_pmi_date;
        if ($vechileCategory == 'non-hgv') {
            $vehicle->dt_tacograch_calibration_due = NULL;
        } else {
            $vehicle->dt_tacograch_calibration_due = $request->dt_tacograch_calibration_due;
        }
        if ($request->webfleet_registration != "") {
            $vehicle->webfleet_registration = $request->webfleet_registration;
        }
        $vehicle->tank_test_date = (!empty($request->tank_test_date)) ? $request->tank_test_date : null;

        if($vehicle->save()){
            if ($request->webfleet_registration != "") {
                //code to fetch and store webfleet obect id start//

                $webfleetClient = new Webfleet();
                $data = $webfleetClient->getVehicles();

                if (count($data) > 0) {
                    $vrns = collect($data)->pluck('objectno');

                    $vehicles = Vehicle::whereIn('webfleet_registration',$vrns->toArray())->get()->keyBy('webfleet_registration');
                    foreach ($data as $vehicleTemp) {
                        $vehicleDB = isset($vehicles[$vehicleTemp['objectno']]) ? $vehicles[$vehicleTemp['objectno']] : null;
                        if ($vehicleDB) {
                            if ($vehicleDB->is_telematics_enabled == 1 && $vehicleDB->webfleet_object_id == $vehicleTemp['objectuid']) {
                                //$out->writeln('Vehicle is up to date.');
                            } else {
                                //$out->writeln('Enabling telematics with webfleet uid : '.$vehicleTemp['objectuid'].'');
                                $vehicleDB->is_telematics_enabled = 1;
                                $vehicleDB->webfleet_object_id = $vehicleTemp['objectuid'];
                                $vehicleDB->save();
                            }

                        } else {
                            //$out->writeln('Vehicle not found.');
                        }
                    }
                }  
                //code to fetch and store webfleet obect id end//
            }
            $archiveStatusesArray = config('config-variables.vehicleStatusArchived');
            if (in_array($request->status, $archiveStatusesArray)) {
                Vehicle::where('id', $id)->delete();
            } else {
                // $vehicle->update(['deleted_at' => null]);
                $vehicle->restore();
            }

            //#FLEE-6600 - Create/Edit vehicle - Create maintenance events when user enter the date in Vehicle Planning fields.
            $this->createVehicleMaintenanceEvents($vehicle);

            event(new PushNotification(config('config-variables.pushNotification.messages.vehicle_updated')));

            if ($request->is_telematics_enabled) {
                # code...
                $dataArray['vehicle_id'] = $vehicle->id;
                $dataArray['hardware_id'] = null;
                $dataArray['registration'] = $vehicle->registration;
                $dataArray['client'] = env('BRAND_NAME');
                $dataArray['clientURL'] = env('API_URL').'/api/'.env('API_VERSION');
                $this->telematicsVehiclePush($dataArray);
            }

            if($request->vehicle_division_id != $oldVehicleDivisionId || $request->vehicle_location_id != $oldVehicleLocationId || $request->vehicle_region_id != $oldVehicleRegionId) {
                $checkAssignment = VehicleAssignment::where('vehicle_id',$vehicle->id)->orderBy('id', 'DESC')->first();
                if ($checkAssignment) {
                    $vehicleValue = $checkAssignment;
                    $toDate = Carbon::now()->subDays('1');
                    $fromDate = Carbon::parse($vehicleValue->from_date);

                    if ($toDate->lt($fromDate)) {
                        $toDate = $fromDate;
                    }
                    $vehicleValue->to_date = $toDate->format('d M Y');
                    $vehicleValue->save();
                } else {
                    $vehicleAssignment = new VehicleAssignment();
                    $vehicleAssignment->vehicle_id = $vehicle->id;
                    $vehicleAssignment->vehicle_division_id = $oldVehicleDivisionId;
                    $vehicleAssignment->vehicle_location_id = $oldVehicleLocationId;
                    $vehicleAssignment->vehicle_region_id = $oldVehicleRegionId;
                    $vehicleAssignment->from_date = Carbon::parse($vehicle->dt_added_to_fleet)->format('d M Y');
                    $toDate = Carbon::now()->subDays('1');
                    $fromDate = Carbon::parse($vehicleAssignment->from_date);

                    if ($toDate->lt($fromDate)) {
                        $toDate = $fromDate;
                    }
                    $vehicleAssignment->to_date = $toDate->format('d M Y');
                    $vehicleAssignment->save();
                }

                $vehicleAssignmentLast = new VehicleAssignment();
                $vehicleAssignmentLast->vehicle_id = $vehicle->id;
                $vehicleAssignmentLast->vehicle_division_id = $vehicle->vehicle_division_id;
                $vehicleAssignmentLast->vehicle_location_id = $vehicle->vehicle_location_id;
                $vehicleAssignmentLast->vehicle_region_id = $vehicle->vehicle_region_id;
                $vehicleAssignmentLast->from_date = Carbon::now()->format('d M Y');
                $vehicleAssignmentLast->to_date = null;
                $vehicleAssignmentLast->save();
            }

            if(in_array($request->status, $vehicleStatusArchived) && in_array($oldVehicleStatus, $vehicleStatusUnArchived)){
                $VehicleArchiveHistory = new VehicleArchiveHistory();
                $VehicleArchiveHistory->vehicle_id = $vehicle->id;
                $VehicleArchiveHistory->event = 'Archived';
                $VehicleArchiveHistory->event_date_time = Carbon::now()->format("Y-m-d H:i:s");
                $VehicleArchiveHistory->save();
            } else if(in_array($request->status, $vehicleStatusUnArchived) && in_array($oldVehicleStatus, $vehicleStatusArchived)){
                    $VehicleArchiveHistory = new VehicleArchiveHistory();
                    $VehicleArchiveHistory->vehicle_id = $vehicle->id;
                    $VehicleArchiveHistory->event = 'Unarchived';
                    $VehicleArchiveHistory->event_date_time = Carbon::now()->format("Y-m-d H:i:s");
                    $VehicleArchiveHistory->save();
            }
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        return redirect()->route('vehicles.show', $vehicle);
    }
    /**
     * Update the maintenance cost of a vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editMaintenanceCost(Request $request) {
        $maintenanceCostArray = [];
        $field = $request['field'];
        $vehicle = Vehicle::where("id",$request['vehicle_id'])->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request['vehicle_id'])->orderBy('id','DESC')->first();
        $maintenanceCostField = json_decode($field, true);
        $maintenanceCostArray = [];
        if($request['field']){
            foreach ($maintenanceCostField as $key => $maintenanceCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $maintenanceCost['cost_value']);
                $finalArray['cost_from_date'] = $maintenanceCost['cost_from_date'];
                $finalArray['cost_to_date'] = $maintenanceCost['cost_to_date'];
                $finalArray['cost_continuous'] = $maintenanceCost['cost_continuous'];
                $maintenanceCostArray[] = $finalArray;
            }
        }
        $maintenanceCostField = $maintenanceCostArray;
        $vehicle->maintenance_cost = json_encode($maintenanceCostField);
        $vehicle->save();

        $currentCost = 0;
        $currentDate = '';
        $maintenanceCurrentDateValue = '';
        $commonHelper = new Common();
        $maintenanceCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->maintenance_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
        //$maintenanceCurrentData = $this->calcMonthlyCurrentData($vehicle->maintenance_cost,$vehicleId,$vehicleArchiveHistory);
        $currentCost = $maintenanceCurrentData['currentCost'];
        $currentDate = $maintenanceCurrentData['currentDate'];
        // $maintenanceCurrentDateValue = $maintenanceCurrentData['currentDateValue'];

        $vehicleMaintenance = json_decode($vehicle->maintenance_cost, true);
        if(isset($vehicleMaintenance)){
            foreach ($vehicleMaintenance as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $maintenanceCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }


        return view('_partials.vehicles.maintenance_cost_history')
            ->with('currentMonthMaintenanceCost',$currentCost)
            ->with('maintenanceCurrentDate',$currentDate)
            ->with('vehicle',$vehicle)
            ->with('maintenanceCurrentDateValue',$maintenanceCurrentDateValue);
    }
    /**
     * Update the lease cost of a vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editLeaseCost(Request $request)
    {
        $maintenanceCostArray = [];
        $field = $request['field'];
        $vehicle = Vehicle::where("id",$request['vehicle_id'])->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request['vehicle_id'])->orderBy('id','DESC')->first();

        $leaseCostField = json_decode($field, true);
        $leaseCostArray = [];
        if($request['field']){
            foreach ($leaseCostField as $key => $leaseCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $leaseCost['cost_value']);
                $finalArray['cost_from_date'] = $leaseCost['cost_from_date'];
                $finalArray['cost_to_date'] = $leaseCost['cost_to_date'];
                $finalArray['cost_continuous'] = $leaseCost['cost_continuous'];
                $leaseCostArray[] = $finalArray;
            }
        }

        $leaseCostField = $leaseCostArray;
        $vehicle->lease_cost = json_encode($leaseCostField);
        $vehicle->save();

        $currentCost = 0;
        $currentDate = '';
        $leaseCurrentDataValue = '';
        $commonHelper = new Common();
        //$leaseCurrentData = $this->calcMonthlyCurrentData($vehicle->lease_cost,$vehicleId,$vehicleArchiveHistory);
        $leaseCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->lease_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
        $currentCost = $leaseCurrentData['currentCost'];
        $currentDate = $leaseCurrentData['currentDate'];
        // $leaseCurrentDataValue = $leaseCurrentData['currentDateValue'];

        $vehicleLease = json_decode($vehicle->lease_cost, true);
        if(isset($vehicleLease)){
            foreach ($vehicleLease as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $currentDate = $fleetCost['cost_from_date'];
                }
            }
        }
        return view('_partials.vehicles.lease_cost_history')
            ->with('currentMonthLeaseCost',$currentCost)
            ->with('leaseCurrentDate',$currentDate)
            ->with('vehicle',$vehicle);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    /**
     * Fetch vehicle type data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getVehicleTypeData($vehicleId,$id) {
        //dd('here');
        $vehicleTypeData = VehicleType::withTrashed()->findOrFail($id);
        $vehicle = Vehicle::withTrashed()->where('id',$vehicleId)->first();
        $vehicleDtAddedToFleet = null;
        if ($vehicle) {
            $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        }

        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('id','DESC')->first();
        $currentTaxYearValue = 0;
        if(!is_null($vehicleTypeData->vehicle_tax) && $vehicleTypeData->vehicle_tax != ''){
            $commonHelper = new Common();
            $vehicleTaxCurrentData = $commonHelper->getFleetCostValueForDate($vehicleTypeData->vehicle_tax,Carbon::now()->format('Y-m-d'));
            //$vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleTypeData->vehicle_tax,$vehicleId,null,null);
            $currentTaxYearValue = $vehicleTaxCurrentData['currentCost'];
            // $currentDate = $vehicleTaxCurrentData['currentDate'];
        }
        $vehicleTypeOdometerSetting = config('config-variables.vehicle_type_odometer_setting');

        return view('vehicles.vehicletypedata', compact('vehicleTypeData','currentTaxYearValue', 'vehicleTypeOdometerSetting'));
    }

    public function getVehicleTypeDataJson($vehicleId,$id) {
        $vehicleTypeData = VehicleType::withTrashed()->findOrFail($id);
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('id','DESC')->first();
        $currentTaxYearValue = 0;
        $vehicleDateAddedToFleet = null;

        $vehicle = Vehicle::withTrashed()->find($vehicleId);

        if ($vehicle) {
            $vehicleDateAddedToFleet = $vehicle->dt_added_to_fleet;
        }
        if(!is_null($vehicleTypeData->vehicle_tax) && $vehicleTypeData->vehicle_tax != ''){
            $commonHelper = new Common();
            $vehicleTaxCurrentData = $commonHelper->getFleetCostValueForDate($vehicleTypeData->vehicle_tax,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDateAddedToFleet);
            //$vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleTypeData->vehicle_tax,$id,$vehicleArchiveHistory,$vehicleDateAddedToFleet);
            $currentTaxYearValue = $vehicleTaxCurrentData['currentCost'];
        }
        $insuranceFieldCurrentCost = 0;
        $insuranceFieldCurrentDate = '';
        if(isset($vehicleTypeData->annual_insurance_cost)) {
            $insuranceValue = $vehicleTypeData->annual_insurance_cost;

            if(isset($insuranceValue)){
                $commonHelper = new Common();
               // $insuranceCurrentData = $this->calcMonthlyCurrentData($insuranceValue,$vehicleId, $vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride);
                $insuranceCurrentData = $commonHelper->getFleetCostValueForDate($insuranceValue,Carbon::now()->format('Y-m-d'), $vehicleArchiveHistory,$vehicleDateAddedToFleet);
                $insuranceFieldCurrentCost = $insuranceCurrentData['currentCost'];
                $insuranceFieldCurrentDate = $insuranceCurrentData['currentDate'];
            }
        }
        return json_encode([$vehicleTypeData, $currentTaxYearValue, 'insurance_cost' => $insuranceFieldCurrentCost]);
    }

    /**
     * [anyUpload description]
     * @return [type] [description]
     */
    public function anyUpload($id)
    {
        $isVechiclePresent = Vehicle::findOrFail($id);

        return view('vehicles.upload')
                ->with('id', $id);
    }

    /**
     * [storeVechileDocs description]
     * @param  Request $request [description]
     * @param  [type]  $id      [description]
     * @return [type]           [description]
     */
    public function anyVechileDocs(Request $request, $id)
    {
        if($request->isMethod('post')){
            // add vehicle docs

            $files = $request->file()['files'];
            //$caption = preg_replace('/\s+/', '_', trim($request->name));
            $caption = $request->name;

            $vehicle = Vehicle::withTrashed()->find($id);

            $user = Auth::user();
            if (!empty($files)) {
                $i = 0;
                foreach ($files as $key => $value) {
                    $fileName = $value->getClientOriginalName();
                    $customFileName = preg_replace('/\s+/', '_', $fileName);
                    $fileMime = $value->getMimeType();
                    $fileSize = $value->getClientSize();
                    $lastInsertedMedia = $vehicle->addMedia($value)
                        ->setFileName($customFileName)
                        ->withCustomProperties(['mime-type' => $value->getMimeType(), 'caption' => $caption, 'createdBy' => $user->id])
                        ->toCollectionOnDisk('vehicles', 'S3_uploads');

                    // $lastInsertedMedia = $vehicle->getMedia('vehicles')->last();
                    if ($lastInsertedMedia->hasCustomProperty('caption') && !empty($lastInsertedMedia->custom_properties['caption'])) {
                        $fileData['files'][$i]['name'] = $lastInsertedMedia->custom_properties['caption'] .".". pathinfo($fileName, PATHINFO_EXTENSION);
                    }
                    else {
                        $fileData['files'][$i]['name'] = $fileName;
                    }

                    $fileData['files'][$i]['created'] = $lastInsertedMedia->created_at->format('H:i:s d M Y');
                    $fileData['files'][$i]['size'] = $lastInsertedMedia->getHumanReadableSizeAttribute();
                    $fileData['files'][$i]['type'] = $fileMime;
                    $fileData['files'][$i]['url'] = getPresignedUrl($lastInsertedMedia);
                    $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $lastInsertedMedia->id);
                    $fileData['files'][$i]['deleteType'] = 'DELETE';
                    $fileData['files'][$i]['uploadedBy'] = $user->first_name . ' ' . $user->last_name;
                    $fileData['files'][$i]['extension'] = pathinfo($fileName, PATHINFO_EXTENSION);
                    $i++;
                }

                return json_encode($fileData);
            }

            flash()->success(config('config-variables.flashMessages.dataSaved'));

            return 0;
        } else {
            // get vehicle docs
            $vehicle = Vehicle::withTrashed()->find($id);
            $files = $vehicle->getMedia();
            $maintenanceHistory = $vehicle->maintenanceHistories;
            $fileColection = collect([]);
            $fileColection = $fileColection->merge($files);
            foreach($maintenanceHistory as $history) {
                $fileColection = $fileColection->merge($history->getMedia());
            }

            $i = 0;
            $fileData = [];
            foreach ($fileColection->sortByDesc('created_at') as $key => $value) {

                if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                    $fileData['files'][$i]['name'] = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                }
                else {
                    $fileData['files'][$i]['name'] = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                }
                //$fileData['files'][$i]['ext'] = pathinfo($value->file_name, PATHINFO_EXTENSION);
                $fileData['files'][$i]['created'] = $value->created_at->format('H:i:s d M Y');
                $fileData['files'][$i]['size'] = $value->humanReadableSize;
                $fileData['files'][$i]['type'] = $value->custom_properties['mime-type'];
                $fileData['files'][$i]['url'] = getPresignedUrl($value);
                $fileData['files'][$i]['uploadedBy'] = '-';
                if($value->collection_name == 'vehicle_maintenance_docs') {
                    $fileData['files'][$i]['section'] = 'Maintenance';
                    $fileData['files'][$i]['deleteUrl'] = 'Maintenance';
                    $fileData['files'][$i]['deleteType'] = '';
                } else {
                    $fileData['files'][$i]['section'] = 'Documents';
                    $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $value->id);
                    $fileData['files'][$i]['deleteType'] = 'DELETE';
                }

                if ($value->hasCustomProperty('createdBy') && !empty($value->custom_properties['createdBy'])) {
                    $uploadedBy = User::find($value->custom_properties['createdBy']);
                    if($uploadedBy) {
                        $fileData['files'][$i]['uploadedBy'] = $uploadedBy->first_name . ' ' . $uploadedBy->last_name;
                    }
                }
                $fileData['files'][$i]['extension'] = pathinfo($value->file_name, PATHINFO_EXTENSION);

                $i++;
            }
            return json_encode($fileData);
        }
    }
        /**
     * Return the vehicles data for the grid
     *
     * @return [type] [description]
     */
    public function getVechileDocs(Request $request,$id)
    {
        // $data = ['vehicle_id'=>$id];
        return GridEncoder::encodeRequestedData(new VehicleDocumentRepository($request->all()), Input::all());
    }

    public function getMediaUrl(Request $request, $id)
    {
        $media = Media::find($id);
        return getPresignedUrl($media);
    }

   /* public function getVechileDocs(Request $request, $id)
    {       
        // get vehicle docs
        $vehicle = Vehicle::withTrashed()->find($id);
        $files = $vehicle->getMedia();
        $maintenanceHistory = $vehicle->maintenanceHistories;
        $fileColection = collect([]);
        $fileColection = $fileColection->merge($files);
        foreach($maintenanceHistory as $history) {
            $fileColection = $fileColection->merge($history->getMedia());
        }

        $i = 0;
        $fileData = [];
        foreach ($fileColection->sortByDesc('created_at') as $key => $value) {

            if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                $fileData['files'][$i]['name'] = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            else {
                $fileData['files'][$i]['name'] = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            //$fileData['files'][$i]['ext'] = pathinfo($value->file_name, PATHINFO_EXTENSION);
            $fileData['files'][$i]['created'] = $value->created_at->format('H:i:s d M Y');
            $fileData['files'][$i]['size'] = $value->humanReadableSize;
            $fileData['files'][$i]['type'] = $value->custom_properties['mime-type'];
            $fileData['files'][$i]['url'] = getPresignedUrl($value);
            $fileData['files'][$i]['uploadedBy'] = '-';
            if($value->collection_name == 'vehicle_maintenance_docs') {
                $fileData['files'][$i]['section'] = 'Maintenance';
                $fileData['files'][$i]['deleteUrl'] = '';
                $fileData['files'][$i]['deleteType'] = '';
            } else {
                $fileData['files'][$i]['section'] = 'Documents';
                $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $value->id);
                $fileData['files'][$i]['deleteType'] = 'DELETE';
            }

            if ($value->hasCustomProperty('createdBy') && !empty($value->custom_properties['createdBy'])) {
                $uploadedBy = User::find($value->custom_properties['createdBy']);
                if($uploadedBy) {
                    $fileData['files'][$i]['uploadedBy'] = $uploadedBy->first_name . ' ' . $uploadedBy->last_name;
                }
            }
            $fileData['files'][$i]['extension'] = pathinfo($value->file_name, PATHINFO_EXTENSION);
            $i++;
        }
//        print_r($fileData);exit;

        return view('_partials.vehicles.documents_list',compact('fileData'));
    }*/
    /*public function getVechileDocs(Request $request, $id)
    {       
        // get vehicle docs
        $vehicle = Vehicle::withTrashed()->find($id);
        $files = $vehicle->getMedia();
        $maintenanceHistory = $vehicle->maintenanceHistories;
        $fileColection = collect([]);
        $fileColection = $fileColection->merge($files);
        foreach($maintenanceHistory as $history) {
            $fileColection = $fileColection->merge($history->getMedia());
        }

        $i = 0;
        $fileData = [];
        foreach ($fileColection->sortByDesc('created_at') as $key => $value) {

            if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                $fileData['files'][$i]['name'] = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            else {
                $fileData['files'][$i]['name'] = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            //$fileData['files'][$i]['ext'] = pathinfo($value->file_name, PATHINFO_EXTENSION);
            $fileData['files'][$i]['created'] = $value->created_at->format('H:i:s d M Y');
            $fileData['files'][$i]['size'] = $value->humanReadableSize;
            $fileData['files'][$i]['type'] = $value->custom_properties['mime-type'];
            $fileData['files'][$i]['url'] = getPresignedUrl($value);
            $fileData['files'][$i]['uploadedBy'] = '-';
            if($value->collection_name == 'vehicle_maintenance_docs') {
                $fileData['files'][$i]['section'] = 'Maintenance';
                $fileData['files'][$i]['deleteUrl'] = '';
                $fileData['files'][$i]['deleteType'] = '';
            } else {
                $fileData['files'][$i]['section'] = 'Documents';
                $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $value->id);
                $fileData['files'][$i]['deleteType'] = 'DELETE';
            }

            if ($value->hasCustomProperty('createdBy') && !empty($value->custom_properties['createdBy'])) {
                $uploadedBy = User::find($value->custom_properties['createdBy']);
                if($uploadedBy) {
                    $fileData['files'][$i]['uploadedBy'] = $uploadedBy->first_name . ' ' . $uploadedBy->last_name;
                }
            }
            $fileData['files'][$i]['extension'] = pathinfo($value->file_name, PATHINFO_EXTENSION);
            $i++;
        }
//        print_r($fileData);exit;
        return view('_partials.vehicles.documents_list',compact('fileData'));
    }*/

    public function vehicleMaintenanceDocs(Request $request, $vehicleMaintenanceHistoryId)
    {
        $vehicleMaintenancehistory = VehicleMaintenanceHistory::with(['eventType','vehicle.type'])->find($vehicleMaintenanceHistoryId);
        if($request->isMethod('post')) {
            $caption = $request->name;
            $files = $request->file()['files-1'];
            if (!empty($files)) {
                $i = 0;
                $user = Auth::user();
                foreach ($files as $key => $value) {
                    $fileName = $value->getClientOriginalName();
                    $customFileName = preg_replace('/\s+/', '_', $fileName);
                    $fileMime = $value->getMimeType();
                    $lastInsertedMedia = $vehicleMaintenancehistory->addMedia($value)
                        ->setFileName($customFileName)
                        ->withCustomProperties(['mime-type' => $value->getMimeType(), 'caption' => $caption, 'createdBy' => $user->id])
                        ->toCollectionOnDisk('vehicle_maintenance_docs', 'S3_uploads');

                    if ($lastInsertedMedia->hasCustomProperty('caption') && !empty($lastInsertedMedia->custom_properties['caption'])) {
                        $fileData['files'][$i]['name'] = $lastInsertedMedia->custom_properties['caption'] .".". pathinfo($fileName, PATHINFO_EXTENSION);
                    }
                    else {
                        $fileData['files'][$i]['name'] = $fileName;
                    }

                    $fileData['files'][$i]['created'] = $lastInsertedMedia->created_at->format('H:i:s d M Y');
                    $fileData['files'][$i]['size'] = $lastInsertedMedia->getHumanReadableSizeAttribute();
                    $fileData['files'][$i]['type'] = $fileMime;
                    $fileData['files'][$i]['url'] = getPresignedUrl($lastInsertedMedia);
                    $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $lastInsertedMedia->id);
                    $fileData['files'][$i]['deleteType'] = 'DELETE';
                    $i++;
                }
                return json_encode($fileData);
            }
            flash()->success(config('config-variables.flashMessages.dataSaved'));
            return 0;
        } else {
            $files = $vehicleMaintenancehistory->getMedia();
            $i = 0;
            $fileData = [];
            foreach ($files as $key => $value) {
                if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                    $fileData['files'][$i]['name'] = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                }
                else {
                    $fileData['files'][$i]['name'] = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                }
                $fileData['files'][$i]['created'] = $value->created_at->format('H:i:s d M Y');
                $fileData['files'][$i]['size'] = $value->humanReadableSize;
                $fileData['files'][$i]['type'] = $value->custom_properties['mime-type'];
                $fileData['files'][$i]['url'] = getPresignedUrl($value);
                $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_maintenance_docs/' . $value->id);
                $fileData['files'][$i]['deleteType'] = 'DELETE';

                $i++;
            }
            return json_encode($fileData);
        }
    }

    public function uploadVehicleMaintenanceDocs(Request $request)
    {
        $fileData = [];

        if($request->isMethod('post')) {
            $files = $request->file()['maintenance_files'];
            if (!empty($files)) {
                $i = 0;
                $caption = $request->name;
                $user = Auth::user();
                foreach ($files as $key => $value) {
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = 0;
                    $tempImage->model_type = VehicleMaintenanceHistory::class;
                    $tempImage->temp_id = time();
                    $tempId = $tempImage->temp_id;
                    $tempImage->save();

                    $fileName = $value->getClientOriginalName();
                    $customFileName = preg_replace('/\s+/', '_', $fileName);
                    $fileMime = $value->getMimeType();
                    $lastInsertedMedia = $tempImage->addMedia($value)
                        ->setFileName($customFileName)
                        ->withCustomProperties(['mime-type' => $value->getMimeType(), 'caption' => $caption, 'createdBy' => $user->id])
                        ->toCollectionOnDisk('vehicle_maintenance_docs', 'S3_uploads');

                    if ($lastInsertedMedia->hasCustomProperty('caption') && !empty($lastInsertedMedia->custom_properties['caption'])) {
                        $fileData['files'][$i]['name'] = $lastInsertedMedia->custom_properties['caption'] .".". pathinfo($fileName, PATHINFO_EXTENSION);
                    }
                    else {
                        $fileData['files'][$i]['name'] = $fileName;
                    }

                    $fileData['files'][$i]['created'] = $lastInsertedMedia->created_at->format('H:i:s d M Y');
                    $fileData['files'][$i]['size'] = $lastInsertedMedia->getHumanReadableSizeAttribute();
                    $fileData['files'][$i]['type'] = $fileMime;
                    $fileData['files'][$i]['url'] = getPresignedUrl($lastInsertedMedia);
                    $fileData['files'][$i]['deleteUrl'] = url('/vehicles/delete_docs/' . $lastInsertedMedia->id);
                    $fileData['files'][$i]['deleteType'] = 'DELETE';
                    $fileData['files'][$i]['tempId'] = $tempId;
                    $i++;
                }
            }
        }
        return json_encode($fileData);
    }

    public function deleteVechileMaintenanceDocs($id)
    {
        $media = Media::find($id);
        if ($media->delete()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * [deleteVechileDocs description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteVechileDocs($id)
    {
        $media = Media::find($id);
        if ($media->delete()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function destroyVehicleDocuments($id) {

        $user = Auth::user();
        $vehicleId = Media::where('id',$id)->pluck('model_id');
        //$userInformationOnly = Vehicle::where('id',$vehicleId)->pluck('created_by');
        $media = Media::where('id', $id)->first();
        if (($media->hasCustomProperty('createdBy') && !empty($media->custom_properties['createdBy'])) || $user->isSuperAdmin()) {
            if($user->isSuperAdmin() || $user->id == $media->custom_properties['createdBy']) {
                if(Media::where('id', $id)->delete()) {
                    flash()->success(config('config-variables.flashMessages.documentDeleted'));
                }else{
                    flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
                }
            } else {
                    flash()->success(config('config-variables.flashMessages.vehicleDeleteDocument'));
            }
        }
        else {
                flash()->success(config('config-variables.flashMessages.vehicleDeleteDocument'));
        }
        return redirect()->back();
    }

    /**
     * [exportPdf description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function exportPdf($id)
    {
        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);


        $vehicle = Vehicle::withTrashed()->with('type', 'creator', 'updater', 'location', 'repair_location')
            // ->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'))
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->findOrFail($id);


        $files = $vehicle->getMedia();
        $pdf = PDF::loadView('pdf.vehicleDetails', array('vehicle' => $vehicle, 'files' => $files))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOrientation('portrait')
            //->setOption('header-right', 'Page [page] of [toPage]')
            //->setOption('header-left', $date->format('H:i:s d M Y'))
            // ->setOption('user-style-sheet', url().'/css/pdf.css');
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));
        return $pdf->download('vehicleDetails.pdf');
    }

    /**
     * [downloadMedia description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function downloadMedia($id)
    {
        $media = Media::findOrFail($id);
        $file_name = $media->file_name;
        $file_url = getPresignedUrl($media);
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"".$file_name."\"");
        readfile($file_url);
        // return redirect(getPresignedUrl($media));
    }

    /**
     * [showVehicleChecks description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function showVehicleChecks($id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::withTrashed()
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', $user->regions->lists('id')->toArray())
            ->where('vehicles.id', $id)
            ->first();

        if(!$vehicle || !$user->isHavingRegionAccess($vehicle->vehicle_region_id)) {
            return redirect('/checks');
        }

        $registration = $vehicle->registration;
        $vehicleRegistrations = Vehicle::withTrashed()->select('registration as id', 'registration as text')->get();

        $checkSearch = [];
        if($user->isUserInformationOnly()) {
            $userInformationOnly = Check::where('checks.created_by', $user->id)->get();

            $checkRegistrationSearch = DB::table('checks')->where('checks.created_by', $user->id)
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
            $customString = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->username . ')';
            array_push($userDataArray, ['id'=>$user->id, 'text'=>$customString]);
        }
        $vehicleDisplay = request()->get('vehicleDisplay');
        JavaScript::put([
            'registration' => $registration,
            'vehicleRegistrations' => $vehicleRegistrations,
            'checkSearch' => $checkSearch,
            'userDataArray' => $userDataArray,
            'vehicleDisplay' => $vehicleDisplay,
        ]);
        $flowFromPage = 'vehicleSearch';
        $data=$this->vehicleService->getDataDivRegLoc();
        $region_for_select=['' => '']+$data['vehicleRegions'];
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $region_for_select=$this->vehicleService->regionForSelect($data);
        }
        $region_for_select=collect($region_for_select);
	$vehicleCheckType = config('config-variables.vehicle_check_type');
        return view('checks.index',compact('flowFromPage','id','region_for_select','vehicleCheckType'));
    }

    /**
     * [showVehicleDefect description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function showVehicleDefects($id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::withTrashed()
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            // ->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'))
            ->where('vehicles.id', $id)
            ->first();

        if(!$vehicle || !$user->isHavingRegionAccess($vehicle->vehicle_region_id)) {
            return redirect('/defects');
        }

        $registration = $vehicle->registration;
        $vehicleRegistrations = Vehicle::withTrashed()->select('registration as id', 'registration as text')->get();

        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshops');
         })->with(['company'])->get();
        $workshopData = [];
        foreach ($workshopusers as $key => $value) {
            $userData = [];
            $userData['id'] = $value->id;
            $userData['text'] = $value->company->name. ' ('.$value->first_name.' '. $value->last_name.')';
            array_push($workshopData, $userData);
        }

        $defectAllocatedTo = [];
        $defectSearch = [];

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
        $vehicleDriverdata = array();
        $vehicleDriverRawData = User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->orderBy('email', 'asc')->get();
        foreach ($vehicleDriverRawData as $key => $value) {
            $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->username . ')';
            array_push($vehicleDriverdata, ['id'=>$value->id, 'text'=>$customString]);
        }

        $vehicleDisplay = request()->get('vehicleDisplay');
        JavaScript::put([
            'registration' => $registration,
            'vehicleRegistrations' => $vehicleRegistrations,
            'workshopData' => $workshopData,
            'defectSearch' => $defectSearch,
            'defectAllocatedTo' => $defectAllocatedTo,
            'vehicleDriverdata' => $vehicleDriverdata,
            'vehicleDisplay' => $vehicleDisplay,
        ]);
        $flowFromPage = 'vehicleSearch';
        $vehicleListing = (new UserService())->getAllVehicleDashboardData();
        return view('defects.index',compact('flowFromPage','id','vehicleListing'));
    }

    public function anyAdvSearchFilter($manufacturer,$model,$type)
    {
        $manufacturersQ = VehicleType::select('manufacturer as id', 'manufacturer as text')->distinct()->orderBy('manufacturer');
        $modelsQ = VehicleType::select('model as id', 'model as text')->distinct()->orderBy('model');
        $typesQ = VehicleType::select('vehicle_type as id', 'vehicle_type as text')->distinct()->orderBy('vehicle_type');

        if (trim($manufacturer) != "-"){
            $manufacturersQ->where('manufacturer',$manufacturer);
            $modelsQ->where('manufacturer',$manufacturer);
            $typesQ->where('manufacturer',$manufacturer);
        }
        if (trim($model) != "-"){
            $manufacturersQ->where('model',$model);
            $modelsQ->where('model',$model);
            $typesQ->where('model',$model);
        }
        if (trim($type) != "-"){
            $manufacturersQ->where('vehicle_type',$type);
            $modelsQ->where('vehicle_type',$type);
            $typesQ->where('vehicle_type',$type);
        }

        $manufacturers = $manufacturersQ->get();
        $models = $modelsQ->get();
        $types = $typesQ->get();

        $response = array(
            "manufacturers" => $manufacturers,
            "models" => $models,
            "types" => $types
        );
        return json_encode($response);
    }

    protected function getVehicleFilters($show)
    {
        $filters = [
            'groupOp' => 'OR',
            'rules' => [],
        ];
        if ($show === 'roadworthy') {
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'Roadworthy']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'Roadworthy (with defects)']);
        }
        if ($show === 'checked-today') {
            array_push($filters['rules'], ['field' => 'items_count.vehicle_id', 'op' => 'ne', 'data' => 'NULL']);
            }
        if ($show === 'unchecked-today') {
            array_push($filters['rules'], ['field' => 'items_count.vehicle_id', 'op' => 'eq', 'data' => null]);
        }
        if ($show === 'off-road') {
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - Accident damage']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - MOT']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - Bodyshop']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - Bodybuilder']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - Service']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'VOR - Quarantined']);
        }
        if ($show === 'other') {
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'Awaiting kit']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'Re-positioning']);
            array_push($filters['rules'], ['field' => 'vehicles.status', 'op' => 'eq', 'data' => 'Other']);
        }

        return $filters;
    }

    protected function getVehiclePlanningFilters($field, $period, $region)
    {
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicles.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        // Set period parameter for query
        if ($period === 'other') {
            $start_range = NULL;
            //$end_range = Carbon::today()->addDays(7)->toDateString();
            $end_range = Carbon::today()->addDays(-1)->toDateString();
        }
        if ($period === 'red') {
            //$start_range = NULL;
            $start_range = Carbon::today()->addDays(0)->toDateString();
            $end_range = Carbon::today()->addDays(6)->toDateString();
        }
        if ($period === 'amber') {
            $start_range = Carbon::today()->addDays(7)->toDateString();
            $end_range = Carbon::today()->addDays(13)->toDateString();
        }
        if ($period === 'green') {
            $start_range = Carbon::today()->addDays(14)->toDateString();
            $end_range = Carbon::today()->addDays(29)->toDateString();
        }

        // Set the field for search query
        $search_field = 'dt_annual_service_inspection'; // Default

        if ($field === 'adr-test') {
            $search_field = 'vehicles.adr_test_date';
        }
        if ($field === 'annual-service') {
            $search_field = 'dt_annual_service_inspection';
        }
        if ($field === 'compressor-service') {
            $search_field = 'next_compressor_service';
        }
        if ($field === 'invertor-service') {
            $search_field = 'next_invertor_service_date';
        }
        if ($field === 'loler-test') {
            $search_field = 'dt_loler_test_due';
        }
        if ($field === 'maintenace-expiry') {
            $search_field = 'dt_repair_expiry';
        }
        if ($field === 'mot-expiry') {
            $search_field = 'dt_mot_expiry';
        }
        if ($field === 'next-service') {
            $search_field = 'dt_next_service_inspection';
        }
        if ($field === 'pto-service') {
            $search_field = 'next_pto_service_date';
        }
        if ($field === 'tax-expiry') {
            $search_field = 'dt_tax_expiry';
        }
        if ($field === 'taco') {
            $search_field = 'dt_tacograch_calibration_due';
        }
        if ($period !== 'other' && $field === 'pmi') {
            $search_field = 'next_pmi_date';
        }
        if ($period === 'other' && $field === 'pmi') {
          $search_field = 'first_pmi_date';
        }
        if ($field === 'tank-test') {
            $search_field = 'tank_test_date';
        }

        // Prepare the filters
        if ($start_range && $field!='pmi') {
            array_push($filters['rules'], ['field' => $search_field, 'op' => 'ge', 'data' => $start_range]);
        }
        if ($end_range && $field!='pmi') {
            array_push($filters['rules'], ['field' => $search_field, 'op' => 'le', 'data' => $end_range]);
        }

        if ($region && strtolower($region) !== 'all') {
            array_push($filters['rules'], ['field' => 'vehicles.vehicle_region_id', 'op' => 'eq', 'data' => $region]);
        }

        return [
            'filters' => $filters,
            'search_field' => $search_field,
            'period' => $period
        ];
    }

        /**
     * [showVehicleIncidents description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function showVehicleIncidents($id)
    {
        $vehicle = Vehicle::withTrashed()
            // ->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'))
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->findOrFail($id);
        $registration = $vehicle->registration;
        $vehicleRegistrations = Vehicle::withTrashed()->select('registration as id', 'registration as text')->get();

        $workshopusers = User::whereHas('roles', function ($query) {
                    $query->where('name', '=', 'Workshops');
         })->with(['company'])->get();
        $workshopData = [];
        foreach ($workshopusers as $key => $value) {
            $user = [];
            $user['id'] = $value->id;
            $user['text'] = $value->company->name. ' ('.$value->first_name.' '. $value->last_name.')';
            array_push($workshopData, $user);
        }

        $defectAllocatedTo = [];
        $incidentSearch = [];
        $user = Auth::user();
        if($user->isUserInformationOnly()) {
            $userInformationOnly = Incident::where('incidents.created_by', Auth::user()->id)->get();

            $incidentRegistrationSearch = DB::table('incidents')->where('incidents.created_by',Auth::user()->id)
                ->join('vehicles', 'incidents.vehicle_id', '=', 'vehicles.id')->select('vehicles.registration')->distinct('registration')->get();

            foreach ($incidentRegistrationSearch as $key => $incidentRegistration) {
                $incidentSearch[$key]['id'] = $incidentRegistration->registration;
                $incidentSearch[$key]['text'] = $incidentRegistration->registration;
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
        $vehicleDriverdata = array();
        $vehicleDriverRawData = User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->orderBy('email', 'asc')->get();
        foreach ($vehicleDriverRawData as $key => $value) {
            $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->username . ')';
            array_push($vehicleDriverdata, ['id'=>$value->id, 'text'=>$customString]);
        }
        $vehicleDisplay = request()->get('vehicleDisplay');
        $vehicleListing = (new UserService())->getAllVehicleDashboardData();
        JavaScript::put([
            'registration' => $registration,
            'vehicleRegistrations' => $vehicleRegistrations,
            'workshopData' => $workshopData,
            'incidentSearch' => $incidentSearch,
            'defectAllocatedTo' => $defectAllocatedTo,
            'vehicleDriverdata' => $vehicleDriverdata,
            'vehicleDisplay' => $vehicleDisplay,
        ]);
        $flowFromPage = 'vehicleSearch';

        $incidentType = config('config-variables.incident_types');

        return view('incidents.index',compact('flowFromPage','id','vehicleListing', 'incidentType'));
    }

    /**
     * Fetch vehicle costs summary data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function getVehicleCostSummary($id) {

        $vehicle = Vehicle::where('id',$id)->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('id','DESC')->first();
        //dd($vehicleArchiveHistory);
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isInsuranceCostOverride = $vehicle->is_insurance_cost_override;
        $isTelematicsCostOverride = $vehicle->is_telematics_cost_override;

        $vehicleRegistrationNumber = $vehicle->registration;
        $ownershipStatus = $vehicle->staus_owned_leased;
        $lineChartDisplayMonth = [];
        $carbon = Carbon::now();
        $lastThreeMonthSelectedDate = Carbon::parse($_GET['selectedDate'])->startOfMonth()->addMonth(1)->format('M Y');

        $lineChartDisplayMonth[] = Carbon::now()->startOfMonth()->subMonth(1)->format('F');
        $lineChartDisplayMonth[] = Carbon::now()->startOfMonth()->subMonth(2)->format('F');
        $lineChartDisplayMonth[] = Carbon::now()->startOfMonth()->subMonth(3)->format('F');

        $vehicleCostSummaryMonth = array_reverse($lineChartDisplayMonth);

        $lineChartDisplayMonthDisplay = [];
        $carbon = Carbon::now();
        $lineChartDisplayMonthDisplay[] = Carbon::parse($lastThreeMonthSelectedDate)->startOfMonth()->subMonth(1)->format('m-Y');
        $lineChartDisplayMonthDisplay[] = Carbon::parse($lastThreeMonthSelectedDate)->startOfMonth()->subMonth(2)->format('m-Y');
        $lineChartDisplayMonthDisplay[] = Carbon::parse($lastThreeMonthSelectedDate)->startOfMonth()->subMonth(3)->format('m-Y');

        $vehicleCostSummaryMonthDisplay = array_reverse($lineChartDisplayMonthDisplay);



        // $vehicle = Vehicle::where('id',$id)->withTrashed()->first();
        $selectedDate = $_GET['selectedDate'];
        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostJson = $fleetCost->value;
        $fleetCostData = json_decode($fleetCostJson, true);
        $vehicleStatusOwnedLeased = $vehicle->staus_owned_leased;
        $isTelematicsEnabled = $vehicle->is_telematics_enabled;

        // Lease cost use
        $leaseCost = 0;
        $leaseCostFormatValue = 0;
        $leaseCostArray = [];
        // if($vehicle->staus_owned_leased != "Owned") {
            if(isset($vehicle->lease_cost)){
                $leaseCostCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->lease_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory,null,null,null,'lease_cost');
                $leaseCost = $leaseCostCurrentData['currentCost'];
                $leaseCostFormatValue = number_format($leaseCostCurrentData['currentCost'],2,'.','');
            }
        // }

        // MaintenanceCost use
        $maintenanceCost = 0;
        $maintenanceCostFormatValue = 0;
        $maintenanceCostArray = [];
        // if($vehicle->staus_owned_leased == "Owned" || $vehicle->staus_owned_leased == "Leased" || $vehicle->staus_owned_leased == "Hire purchase") {
            if(isset($vehicle->maintenance_cost)){
                $maintemamceCostCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->maintenance_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory);
                $maintenanceCost = $maintemamceCostCurrentData['currentCost'];
                $maintenanceCostFormatValue = number_format($maintemamceCostCurrentData['currentCost'],2,'.','');
            }
        // }

        // Manualcost Adjustement
        $costAdjustment = 0;
        $costAdjustmentFormatValue = 0;
        $costAdjustmentArray = [];
        $vehicleManualCostAdjustmentValue = json_decode($vehicle->manual_cost_adjustment, true);
        if(isset($vehicleManualCostAdjustmentValue)){
            $manualCostAdjustmentCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleManualCostAdjustmentValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $costAdjustment = $manualCostAdjustmentCurrentData['currentCost'];
            $costAdjustmentFormatValue = number_format($manualCostAdjustmentCurrentData['currentCost'],2,'.','');
        }

        // Fuel use
        $fuelUseValue = 0;
        $fuelUseFormatValue = 0;
        $fuelUseArray = [];
        $vehicleFuelValue = json_decode($vehicle->fuel_use, true);
        if(isset($vehicleFuelValue)){
            $fuelCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleFuelValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $fuelUseValue = $fuelCurrentData['currentCost'];
            $fuelUseFormatValue = number_format($fuelCurrentData['currentCost'],2,'.','');
        }

        // Oil use
        $oilUseValue = 0;
        $oilUseFormatValue = 0;
        $vehicleOilArray = [];
        $vehicleOilValue = json_decode($vehicle->oil_use, true);
        if(isset($vehicleOilValue)){
            $oilCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleOilValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $oilUseValue = $oilCurrentData['currentCost'];
            $oilUseFormatValue = number_format($oilCurrentData['currentCost'],2,'.','');
        }

        // AdBlue use
        $adBlueUseValue = 0;
        $adBlueFormatValue = 0;
        $vehicleAdblueArray = [];
        $vehicleAdblueValue = json_decode($vehicle->adblue_use, true);
        if(isset($vehicleAdblueValue)){
            $adBlueCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleAdblueValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $adBlueUseValue = $adBlueCurrentData['currentCost'];
            $adBlueFormatValue = number_format($adBlueCurrentData['currentCost'],2,'.','');
        }

        // ScreenWash use
        $screenWashUseValue = 0;
        $screenWashFormatValue = 0;
        $vehicleScreenWashArray = [];
        $vehicleScreenWashValue = json_decode($vehicle->screen_wash_use, true);
        if(isset($vehicleScreenWashValue)){
            $screenWashCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleScreenWashValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $screenWashUseValue = $screenWashCurrentData['currentCost'];
            $screenWashFormatValue = number_format($screenWashCurrentData['currentCost'],2,'.','');
        }

        // Fleetlivery use
        $fleetLiveryUseValue = 0;
        $fleetLiveryFormatValue = 0;
        $vehicleFleetLiveryArray = [];
        $vehicleFleetLiveryValue = json_decode($vehicle->fleet_livery_wash, true);
        if(isset($vehicleFleetLiveryValue)){
            $fleetLiveryCurrentData = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleFleetLiveryValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            $fleetLiveryUseValue = $fleetLiveryCurrentData['currentCost'];
            $fleetLiveryFormatValue = number_format($fleetLiveryCurrentData['currentCost'],2,'.','');
        }

        // FleetInsurance Use
        $insuranceValueJsonValue = '';
        $annualInsuranceCostData = json_decode($vehicle->type->annual_insurance_cost, true);
        if(isset($annualInsuranceCostData) && $vehicle->is_insurance_cost_override != 1) {
            $insuranceValueJsonValue = json_encode($annualInsuranceCostData);
        } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost == ''){
            $insuranceValueJsonValue = json_encode($annualInsuranceCostData);
        } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost != ''){
            $insuranceValueJsonValue = $vehicle->insurance_cost;
        } else {
            if(isset($annualInsuranceCostData)){
                $insuranceValueJsonValue = json_encode($annualInsuranceCostData);
            }
        }

        $fleetInsuranceCost = 0;
        $fleetInsuranceFormatValue = 0;
        $fleetInsuranceArray = [];
        if(isset($insuranceValueJsonValue)){
            $fleetInsuranceCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($insuranceValueJsonValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,'N/A','ins_cost');
            $fleetInsuranceCost = $fleetInsuranceCurrentData['currentCost'];
            $fleetInsuranceFormatValue = $fleetInsuranceCurrentData['currentCost'];
        }


        // TelematicsInsurance Use
        $telematicsJsonValue = '';
        if(isset($fleetCostData['telematics_insurance_cost']) && $vehicle->is_telematics_cost_override != 1) {
            $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
        } else if($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost == ''){
            $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
        } else if($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost != ''){
            $telematicsJsonValue = $vehicle->telematics_cost;
        } else {
            if(isset($fleetCostData['telematics_insurance_cost'])){
                $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
            }
        }

        $fleetTelematicsCost = 0;
        $fleetTelematicsFormatValue = 0;
        $fleetTelematicsCostArray = [];
        if($vehicle->is_telematics_enabled == 1) {
            if(isset($telematicsJsonValue)){

                $fleetTelematicsCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($telematicsJsonValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A',$isTelematicsCostOverride,'telematics');

                $fleetTelematicsCost = $fleetTelematicsCurrentData['currentCost'];
                $fleetTelematicsFormatValue = $fleetTelematicsCurrentData['currentCost'];
            }
        }

        // Depreciation use
        $depreciationUseValue = 0;
        $depreciationFormatValue = 0;
        $depreciationArray = [];
        // if($vehicle->staus_owned_leased == "Owned") {
            if(isset($vehicle->monthly_depreciation_cost)){
                $depreciationCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->monthly_depreciation_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory);
                $depreciationUseValue = $depreciationCurrentData['currentCost'];
                $depreciationFormatValue = $depreciationCurrentData['currentCost'];
            }
        // }

        // Vehicle Tax Value
        $vehicleTaxValue = VehicleType::where('id', $vehicle->type->id)->first();
        $vehicleTaxUseValue = 0;
        $vehicleTaxFormatValue = 0;
        $vehicleTaxArray = [];
        if(isset($vehicleTaxValue->vehicle_tax)){
            $vehicleTaxCurrentData = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicleTaxValue->vehicle_tax,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,null,null,"vehicle_tax",$isVehicleTax = 1);
            $vehicleTaxUseValue = $vehicleTaxCurrentData['currentCost'];
            $vehicleTaxFormatValue = $vehicleTaxCurrentData['currentCost'];
        }

        $monthlyOdometerReadings = Check::where('vehicle_id',$id)
                        ->whereIn('type',['Vehicle Check','Return Check'])
                        ->orderBy('created_at','DESC')->get();

        $firstEntry = Check::where('vehicle_id',$id)
            ->where('type','Vehicle Check')
            ->whereMonth('created_at','=',Carbon::parse($selectedDate)->format('m'))
            ->whereYear('created_at','=',Carbon::parse($selectedDate)->format('Y'))
            ->orderBy('created_at','ASC')->first();

        $lastEntry = Check::where('vehicle_id',$id)
            ->where('type','Return Check')
            ->whereMonth('created_at','=',Carbon::parse($selectedDate)->format('m'))
            ->whereYear('created_at','=',Carbon::parse($selectedDate)->format('Y'))
            ->orderBy('created_at','DESC')->first();

        if ($firstEntry && $lastEntry) {
            $odometerMilesPerMonthValue = (int)$lastEntry->odometer_reading - (int)$firstEntry->odometer_reading;
        } else {
            $odometerMilesPerMonthValue = 0;
        }


        /*$odometerMilesPerMonthValue = 0;
        $monthlyOdometerReadingValueArray = [];
        if ($monthlyOdometerReadings != null && !empty($monthlyOdometerReadings) && count($monthlyOdometerReadings) >= 2) {
            foreach ($monthlyOdometerReadings as $monthlyOdometerReading) {

                $selectedDate = Carbon::parse($selectedDate)->format('M Y');
                $odometerFromDate = Carbon::parse($monthlyOdometerReading['created_at'])->format('M Y');
                $odometerArrayFromDate = Carbon::parse($monthlyOdometerReading['created_at'])->format('m-Y');

                if(!isset($monthlyOdometerReadingValueArray[$odometerArrayFromDate])) {
                    $monthlyOdometerReadingValueArray[$odometerArrayFromDate] = 0;
                }

                $monthlyOdometerReadingValueArray[$odometerArrayFromDate] = $monthlyOdometerReadings->first()['odometer_reading'] - $monthlyOdometerReadings->last()['odometer_reading'];

                if($selectedDate == $odometerFromDate) {
                    $odometerMilesPerMonthValue = $monthlyOdometerReadings->first()['odometer_reading'] - $monthlyOdometerReadings->last()['odometer_reading'];
                }
            }
        }*/

        // Defect/damage cost value
        /*$defects = Defect::where('vehicle_id',$id)->get();
        $defectDamageCostValue = 0;
        $defectDamageArray = [];
        $defectDamageCompareDate = '';
        $actualDefectCost = 0;
        foreach ($defects as $defect) {
            $defectFromDate = Carbon::parse($defect['report_datetime'])->format('M Y');
            $defectDamageArrayFromDate = Carbon::parse($defect['report_datetime'])->format('m-Y');
            $defectCostValue = $defect->actual_defect_cost_value ? $defect->actual_defect_cost_value : 0;

            if($selectedDate == $defectFromDate) {
                $defectDamageCostValue += $defectCostValue;
            }

            if(!isset($defectDamageArray[$defectDamageArrayFromDate])) {
                $defectDamageArray[$defectDamageArrayFromDate] = 0;
            }
            $defectDamageArray[$defectDamageArrayFromDate] += $defectCostValue;
        }*/

        $startDate = Carbon::parse($selectedDate)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d');
        $reportController = new ReportsController(new VehicleService(), new Report(new Common(), new TelematicsService(), new CustomReportRepository()));
        $defectDamageCostValue = $reportController->getDefectCost($vehicleId,$startDate,$endDate,$selectedDate);

        // Fleet cost (Total cost last three month record)
        $vehicleVariableCostMonthDisplay = [];
        $vehicleDefectDamageCostMonthDisplay = [];
        foreach ($vehicleCostSummaryMonthDisplay as $key => $month) {
            if(!isset($vehicleVariableCostMonthDisplay[$month])) {
                $vehicleVariableCostMonthDisplay[$month] =  0;
            }

            $selectedDate = '01-'.$month;
            if(isset($vehicleFuelValue)){
                $fuelUseCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $fuelUseCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleFuelValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$fuelUseCostMonth);
                $fuelUseArray[$month] = $fuelUseCostValue['currentCost'];
            }else if(!isset($fuelUseArray[$month])) {
                $fuelUseArray[$month] =  0;
            }

            if(isset($vehicleOilValue)){
                $oilUseCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $oilUseCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleOilValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$oilUseCostMonth);
                $vehicleOilArray[$month] = $oilUseCostValue['currentCost'];
            }else if(!isset($vehicleOilArray[$month])) {
                $vehicleOilArray[$month] =  0;
            }

            if(isset($vehicleAdblueValue)){
                $adBlueCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $adBlueCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleAdblueValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$adBlueCostMonth);
                $vehicleAdblueArray[$month] = $adBlueCostValue['currentCost'];
            }else if(!isset($vehicleAdblueArray[$month])) {
                $vehicleAdblueArray[$month] =  0;
            }

            if(isset($vehicleScreenWashValue)){
                $screenWashCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $screenWashCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleScreenWashValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$screenWashCostMonth);
                $vehicleScreenWashArray[$month] = $screenWashCostValue['currentCost'];
            }else if(!isset($vehicleScreenWashArray[$month])) {
                $vehicleScreenWashArray[$month] =  0;
            }

            if(isset($vehicleFleetLiveryValue)){
                $fleetLiveryCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $fleetLiveryCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleFleetLiveryValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$fleetLiveryCostMonth);
                $vehicleFleetLiveryArray[$month] = $fleetLiveryCostValue['currentCost'];
            }else if(!isset($vehicleFleetLiveryArray[$month])) {
                $vehicleFleetLiveryArray[$month] =  0;
            }

            if(isset($vehicleManualCostAdjustmentValue)){
                $manualCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $manualCostValue = $this->viewVehicleCostSummaryBasedOnPeriodCalc($vehicleManualCostAdjustmentValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$manualCostMonth);
                $costAdjustmentArray[$month] = $manualCostValue['currentCost'];
            } else if(!isset($costAdjustmentArray[$month])) {
                $costAdjustmentArray[$month] =  0;
            }

            if(isset($vehicle->lease_cost)){// && $vehicle->staus_owned_leased != "Owned"
                $leaseCostMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $leaseCostValue = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->lease_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory,$leaseCostMonth);
                $leaseCostArray[$month] = $leaseCostValue['currentCost'];
            }else if(!isset($leaseCostArray[$month])) {
                $leaseCostArray[$month] =  0;
            }

            if(isset($vehicle->maintenance_cost)){
                $maintenancetMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $maintenanceValue = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->maintenance_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory,$maintenancetMonth);
                $maintenanceCostArray[$month] = $maintenanceValue['currentCost'];
            }else if(!isset($maintenanceCostArray[$month])) {
                $maintenanceCostArray[$month] =  0;
            }

            if(isset($vehicle->monthly_depreciation_cost)){// && $vehicle->staus_owned_leased == "Owned"
                $depreciationMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $depreciationValue = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicle->monthly_depreciation_cost,$selectedDate,$vehicleId,$vehicleArchiveHistory,$depreciationMonth);
                $depreciationArray[$month] = $depreciationValue['currentCost'];
            }else if(!isset($depreciationArray[$month])) {
                $depreciationArray[$month] =  0;
            }

            if(isset($insuranceValueJsonValue)){
                $insuranceMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $insuranceValue = $this->viewVehicleCostSummaryCurrentMonthCalc($insuranceValueJsonValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride);
                $fleetInsuranceArray[$month] = $insuranceValue['currentCost'];
            }else if(!isset($fleetInsuranceArray[$month])) {
                $fleetInsuranceArray[$month] =  0;
            }

            if(isset($telematicsJsonValue) && $vehicle->is_telematics_enabled == 1){
                $telematicsMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $telematicsValue = $this->viewVehicleCostSummaryCurrentMonthCalc($telematicsJsonValue,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isTelematicsCostOverride,$telematicsMonth);
                $fleetTelematicsCostArray[$month] = $telematicsValue['currentCost'];
            }else if(!isset($fleetTelematicsCostArray[$month])) {
                $fleetTelematicsCostArray[$month] =  0;
            }


            if(isset($vehicleTaxValue->vehicle_tax)){
                $vehicleTaxMonth = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
                $vehicleTaxMonthValue = $this->viewVehicleCostSummaryCurrentMonthCalc($vehicleTaxValue->vehicle_tax,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$vehicleTaxMonth);
                $vehicleTaxArray[$month] = $vehicleTaxMonthValue['currentCost'];
            }else if(!isset($vehicleTaxArray[$month])) {
                $vehicleTaxArray[$month] =  0;
            }


            $startDate = Carbon::parse($selectedDate)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d');
            $defectDamageCostValue = $reportController->getDefectCost($vehicleId,$startDate,$endDate,$selectedDate);
            /*if(!isset($defectDamageArray[$month])) {
                $defectDamageArray[$month] =  0;
            }*/

            //$vehicleDefectDamageCostMonthDisplay[$month] = $defectDamageArray[$month];
            $vehicleDefectDamageCostMonthDisplay[$month] = $defectDamageCostValue;

            $vehicleVariableCostMonthDisplay[$month] =  $fuelUseArray[$month] + $vehicleOilArray[$month] + $vehicleAdblueArray[$month] + $vehicleScreenWashArray[$month] + $vehicleFleetLiveryArray[$month] + $costAdjustmentArray[$month] + $leaseCostArray[$month] + $maintenanceCostArray[$month] + $depreciationArray[$month] + $fleetInsuranceArray[$month] + $fleetTelematicsCostArray[$month] + $vehicleTaxArray[$month] + $vehicleDefectDamageCostMonthDisplay[$month];
        }
        //Vehicle Status Leased & Owned Calculation
        $vehicleVeriableCostAdditionAllField = $fleetInsuranceCost + $fleetTelematicsCost + $vehicleTaxFormatValue + $maintenanceCost + $leaseCost + $costAdjustment + $fuelUseValue + $oilUseValue + $adBlueUseValue + $screenWashUseValue + $fleetLiveryUseValue + $defectDamageCostValue + $depreciationUseValue;

        $vehicleVariableCost = number_format($vehicleVeriableCostAdditionAllField,2,'.','');
        $defectDamageCostFormatValue = number_format($defectDamageCostValue,2,'.','');
        //Vehicle Cost Per Mile
        $vehicleCostPerMileValue = 0;
        $vehicleCostPerFormatValue = 0;
        if($odometerMilesPerMonthValue != 0){
            $vehicleCostPerMileValue = $vehicleVariableCost/$odometerMilesPerMonthValue;
            $vehicleCostPerFormatValue = number_format($vehicleCostPerMileValue,2,'.','');
        }
        $viewVehicleCostSummary = ['vehicleVariableCost' => $vehicleVariableCost, 'fleetInsuranceFormatValue' => $fleetInsuranceFormatValue, 'fleetTelematicsFormatValue' => $fleetTelematicsFormatValue, 'vehicleTaxFormatValue' => $vehicleTaxFormatValue, 'maintenanceCostFormatValue' => $maintenanceCostFormatValue, 'leaseCostFormatValue' => $leaseCostFormatValue, 'costAdjustmentFormatValue' => $costAdjustmentFormatValue, 'fuelUseFormatValue' => $fuelUseFormatValue, 'oilUseFormatValue' => $oilUseFormatValue, 'adBlueFormatValue' => $adBlueFormatValue, 'screenWashFormatValue' => $screenWashFormatValue, 'fleetLiveryFormatValue' => $fleetLiveryFormatValue, 'defectDamageCostValue' => $defectDamageCostValue,'vehicleStatusOwnedLeased' => $vehicleStatusOwnedLeased,  'vehicleCostSummaryMonth' => $vehicleCostSummaryMonth, 'vehicleVariableCostMonthDisplay' => $vehicleVariableCostMonthDisplay,'odometerMilesPerMonthValue' => $odometerMilesPerMonthValue, 'vehicleCostPerFormatValue' => $vehicleCostPerFormatValue, 'vehicleDefectDamageCostMonthDisplay' => $vehicleDefectDamageCostMonthDisplay, 'vehicleRegistrationNumber' => $vehicleRegistrationNumber, 'defectDamageCostFormatValue' => $defectDamageCostFormatValue, 'depreciationFormatValue' => $depreciationFormatValue,'ownershipStatus' => $ownershipStatus, 'isTelematicsEnabled' => $isTelematicsEnabled];
        return $viewVehicleCostSummary;

    }

    /**
     * Return the vehicles data for the grid
     *
     * @return [type] [description]
     */
    public function getMaintenanceHistoryData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new VehicleMaintenanceHistoryRepository($request->all()), Input::all());
    }

    /**
     * Return the vehicles data for the grid
     *
     * @return [type] [description]
     */
    public function getAssignmentData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new VehicleAssignmentRepository($request->all()), Input::all());
    }

    /**
     * Return the vehicles history data for the grid
     *
     * @return [type] [description]
     */
    public function getHistoryData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new VehicleHistoryRepository($request->all()), Input::all());
    }

    public function addVehicleRepairLocation(Request $request)
    {
        $addVehicleRepairLocation = new VehicleRepairLocations();
        $addVehicleRepairLocation->name = $request->location_name;
        if($addVehicleRepairLocation->save()){
            return $this->getAllCompaniesJson();
        }
    }

    public function getAllCompaniesJson()
    {
        $allOtherLocations = VehicleRepairLocations::orderBy('name')->get();
        $allOtherLocations = json_encode($allOtherLocations);
      /*  print_r($allOtherLocations);die;
        $returnJsonString = '['.'{"id":"","name":""},';
        foreach ($allOtherLocations as $key => $value) {
            $returnJsonString = $returnJsonString . '{"id":"'.$key.'","name":"'.$value.'"},';
        }
        $returnJsonString = rtrim($returnJsonString,',') .']';*/

        return $allOtherLocations;
    }

    public function calcMonthlyVariableCost(){
        return 0;
    }
    public function calcMonthlyFixedCost(){
        return 0;
    }
    public function calcMonthlyForecastVariableCost(){
        return 0;
    }
    public function calcMonthlyForecastFixedCost(){
        return 0;
    }

    public function addMaintenanceHistory(Request $request)
    {
        $vehicleMaintenancehistory = new VehicleMaintenanceHistory();
        $vehicleMaintenancehistory->vehicle_id = $request['vehicleId'];
        $vehicleMaintenancehistory->event_type_id = $request['vehicleMaintenanceEventType'];
        $vehicleMaintenancehistory->event_date = $request['vehicleMaintenanceEventDate'];
        $vehicleMaintenancehistory->event_plan_date = $request['vehicleMaintenancePlannedDate'];
        $vehicleMaintenancehistory->mot_type = $request['vehicleMaintenanceMotType'];
        $vehicleMaintenancehistory->mot_outcome = $request['vehicleMaintenanceMotOutcome'];
        $vehicleMaintenancehistory->comment = $request['vehicleMaintenanceComment'];
        $vehicleMaintenancehistory->event_status = $request['vehicleMaintenanceStatus'];
        $vehicleMaintenancehistory->is_safety_inspection_in_accordance_with_dvsa = $request['vehicleMaintenanceAcknowledgment'] == 'yes' ? 1 : ($request['vehicleMaintenanceAcknowledgment'] == 'no' ? 0 : null);
        $vehicleMaintenancehistory->odomerter_reading = $request['vehicleMaintenanceOdometerReading'];
        $vehicleMaintenancehistory->created_by = Auth::id();
        $vehicleMaintenancehistory->updated_by = Auth::id();
        $vehicleMaintenancehistory->save();

        $images = $request->vehicleMaintenanceImages;
        if(isset($images) && !empty($images)) {
            foreach ($images as $key => $tempImageId) {
                $tempImage = TemporaryImage::where('temp_id', $tempImageId)->first();
                if ($tempImage) {
                    $media = $tempImage->getMedia()->first();
                    if ($media) {
                        $media->model_id = $vehicleMaintenancehistory->id;
                        $media->model_type = VehicleMaintenanceHistory::class;
                        $media->save();
                    }
                }
            }
        }

        if ($vehicleMaintenancehistory->eventType->slug == 'preventative_maintenance_inspection') {
            $isPmi = 1;
        } else {
            $isPmi = 0;
        }

        if($vehicleMaintenancehistory->eventType->slug == 'preventative_maintenance_inspection' && ($request->is_update_pmi_schedule == 0 || $request->is_update_pmi_schedule == 'N/A')) {
            //do nothing
            return 'true';
        } else {
            return $this->updateServiceNextDate($vehicleMaintenancehistory, $isPmi, 0, $request);
        }
    }

    public function editMaintenanceHistory(Request $request)
    {
        // echo "<pre>"; print_r($request->all());  echo "</pre>";
        $id = $request->maintenancehistoryEditId;
        $maintenanceEvent = MaintenanceEvents::find($request['editVehicleMaintenanceEventType']);
        $vehicle = Vehicle::with('type')->withTrashed()->where('id',$request['editVehicleId'])->first();
        $vehicleMaintenancehistoryEdit = VehicleMaintenanceHistory::findOrFail($id);
        $oldVehicleMaintenancehistoryStatus = $vehicleMaintenancehistoryEdit->event_status;

        $vehicleMaintenancehistoryEdit->vehicle_id = $request['editVehicleId'];
        if(isset($request['editVehicleMaintenancePlannedDate']) && $request['editVehicleMaintenancePlannedDate'] != null){
            $vehicleMaintenancehistoryEdit->event_plan_date = $request['editVehicleMaintenancePlannedDate'];
        }
        //$vehicleMaintenancehistoryEdit->event_type = $request['editVehicleMaintenanceEventType'];
        $vehicleMaintenancehistoryEdit->event_type_id = $request['editVehicleMaintenanceEventType'];
        $vehicleMaintenancehistoryEdit->event_date = $request['editVehicleMaintenanceEventDate'];

        if ($maintenanceEvent->slug == 'mot') {
            $vehicleMaintenancehistoryEdit->mot_type = $request['editVehicleMaintenanceMotType'];
            $vehicleMaintenancehistoryEdit->mot_outcome = $request['editVehicleMaintenanceMotOutcome'];
        } else {
            $vehicleMaintenancehistoryEdit->mot_type = null;
            $vehicleMaintenancehistoryEdit->mot_outcome = null;
        }

        $vehicleMaintenancehistoryEdit->comment = $request['editVehicleMaintenanceComment'];
        $oldStatus = $vehicleMaintenancehistoryEdit->event_status;
        $vehicleMaintenancehistoryEdit->event_status = $request['editVehicleMaintenanceStatus'];

        $vehicleMaintenancehistoryEdit->is_safety_inspection_in_accordance_with_dvsa = $request['editVehicleMaintenanceAcknowledgment'] == 'yes' ? 1 : ($request['editVehicleMaintenanceAcknowledgment'] == 'no' ? 0 : null);

        $vehicleMaintenancehistoryEdit->odomerter_reading = $request['editVehicleMaintenanceOdometerReading'] != NULL ? $request['editVehicleMaintenanceOdometerReading'] : null;

        if ($maintenanceEvent->slug == 'next_service_inspection_distance') {// || $maintenanceEvent->slug == 'preventative_maintenance_inspection'

            // $vehicleMaintenancehistoryEdit->event_planned_distance = $request['editVehicleMaintenanceOdometerReading'] != NULL ? $request['editVehicleMaintenanceOdometerReading'] : null;
            $vehicleMaintenancehistoryEdit->event_planned_distance = $request['eventPlannedDistance'] != NULL ? $request['eventPlannedDistance'] : null;

            $common = new Common();
            if ($vehicleMaintenancehistoryEdit->event_planned_distance != null && $oldStatus == 'Incomplete' && $vehicleMaintenancehistoryEdit->event_status == 'Incomplete') {

                $vehicle->next_service_inspection_distance = $vehicleMaintenancehistoryEdit->event_planned_distance;
                $vehicle->save();

            } else if ($vehicleMaintenancehistoryEdit->event_planned_distance != null && $oldStatus == 'Incomplete' && $vehicleMaintenancehistoryEdit->event_status == 'Complete') {
                $vehicleProfile = $vehicle->type;
                $newServiceDistance = $common->getNextServiceInspectionDistance($vehicleMaintenancehistoryEdit->event_planned_distance,$vehicleProfile->service_inspection_interval);
                if ($vehicle->next_service_inspection_distance < $newServiceDistance) {
                    $vehicle->next_service_inspection_distance = $newServiceDistance;
                    $vehicle->save();
                }
            } else if ($vehicleMaintenancehistoryEdit->event_planned_distance != null && $oldStatus == 'Complete' && $vehicleMaintenancehistoryEdit->event_status == 'Incomplete') {
                $vehicleProfile = $vehicle->type;
                $lastCompletedVehicleMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id',$vehicle->id)
                    ->where('event_type_id',$maintenanceEvent->id)
                    ->where('event_status','Complete')
                    ->where('id','!=',$vehicleMaintenancehistoryEdit->id)
                    ->whereNotNull('event_planned_distance')
                    ->orderBy('event_planned_distance','DESC')
                    ->first();

                $newServiceDistanceVehicle = $common->getNextServiceInspectionDistance($vehicle->last_odometer_reading,$vehicleProfile->service_inspection_interval);

                if ($lastCompletedVehicleMaintenanceHistory) {
                    $newServiceDistanceByLastHistory = $common->getNextServiceInspectionDistance($lastCompletedVehicleMaintenanceHistory->event_planned_distance,$vehicleProfile->service_inspection_interval);

                    if ($newServiceDistanceByLastHistory > $newServiceDistanceVehicle) {
                        $vehicle->next_service_inspection_distance = $newServiceDistanceByLastHistory;
                        $vehicle->save();
                    } else {
                        $vehicle->next_service_inspection_distance = $newServiceDistanceVehicle;
                        $vehicle->save();
                    }

                } else {
                    $vehicle->next_service_inspection_distance = $newServiceDistanceVehicle;
                    $vehicle->save();
                }
            }
        }

        $vehicleMaintenancehistoryEdit->updated_by = Auth::id();
        $vehicleMaintenancehistoryEdit->save();

        $condition = ['vehicle_id' => $vehicleMaintenancehistoryEdit->vehicle_id, 'event_type_id' => $vehicleMaintenancehistoryEdit->event_type_id];

        $isPmi = 0;
        if ($vehicleMaintenancehistoryEdit->eventType->slug == 'preventative_maintenance_inspection' && $request['is_update_pmi_schedule_edit'] != 'N/A') {
            $isPmi = $request['is_update_pmi_schedule_edit'];
        }
        
        $isMotRescheduled = 0;
        if($vehicleMaintenancehistoryEdit->eventType->slug == 'mot') {
            $isMotRescheduled = $request['is_mot_rescheduled'];
        } 

        // if($vehicleMaintenancehistoryEdit->event_status == 'Incomplete' || $oldVehicleMaintenancehistoryStatus == 'Complete') {
        if($oldVehicleMaintenancehistoryStatus == 'Complete') {
            return 'true';
        }

        return $this->updateServiceNextDate($vehicleMaintenancehistoryEdit,$isPmi,$isMotRescheduled,$request);
    }

    public function updateServiceNextDate($vehicleMaintenanceEntry,$isPmi = 0,$isMotRescheduled =0,$request)
    {
        if ($vehicleMaintenanceEntry->eventType->is_standard_event == 0) {
            return;
        }
        $vehicle = Vehicle::find($vehicleMaintenanceEntry->vehicle_id);
        $maintenanceEvent = $vehicleMaintenanceEntry->eventType;
        $eventType = $maintenanceEvent->slug;

        if($eventType == 'next_service_inspection_distance') {
            if($vehicleMaintenanceEntry->event_status == 'Complete') {
                $user = User::where('first_name','System')->first();
                $vehicleHistory = new VehicleMaintenanceHistory();
                $vehicleHistory->vehicle_id = $vehicleMaintenanceEntry->vehicle_id;
                $vehicleHistory->event_type_id = $vehicleMaintenanceEntry->event_type_id;
                $vehicleHistory->event_planned_distance = $vehicle->next_service_inspection_distance;
                // $vehicleHistory->odomerter_reading = $vehicle->next_service_inspection_distance;
                $vehicleHistory->event_status = 'Incomplete';
                $vehicleHistory->created_by = $user->id;
                $vehicleHistory->updated_by = $user->id;
                \Log::info($vehicleHistory);
                $vehicleHistory->save();
            }
            return $vehicle;
        }

        $serviceMapping = collect(config('config-variables.service_column_mapping'));

        $getColumn = $serviceMapping[$eventType]['get_column'];
        $setColumn = $serviceMapping[$eventType]['set_column'];
        $vehicleType = $vehicle->type;

        if (isset($getColumn) && trim($getColumn) != '') {
            $interval = $vehicleType->$getColumn;
        } else {
            $interval = $serviceMapping[$eventType]['interval'];
        }
        \Log::info('interval: '.$interval);

        if($vehicleMaintenanceEntry->event_status == 'Incomplete') {
            if($eventType == 'preventative_maintenance_inspection') {
                if(!is_null($interval) && $interval != "") {
                    $interval = \DateInterval::createFromDateString($interval);
                    $pmiVehicleNxtServiceDt = Carbon::parse($request['editVehicleMaintenancePlannedDate']);

                    if($pmiVehicleNxtServiceDt->lt(Carbon::today())) {
                        do {
                            $pmiVehicleNxtServiceDt = $pmiVehicleNxtServiceDt->add($interval);
                        }
                        while ($pmiVehicleNxtServiceDt->lt(Carbon::today()));
                    }

                    $vehicle->next_pmi_date = $pmiVehicleNxtServiceDt->format('d M Y');
                }
            } else {
                $vehicle->$setColumn = Carbon::parse($request['editVehicleMaintenancePlannedDate'])->format('d M Y');
            }
            $vehicle->save();
            return 'true';
        }

        if(isset($serviceMapping[$eventType]) && $eventType != null){
            \Log::info('in if cond');

            if(!is_null($interval) && $interval != "") {
                \Log::info('interval exists so in if cond');
                $interval = \DateInterval::createFromDateString($interval);

                // if($vehicleMaintenanceEntry->eventType->slug == 'preventative_maintenance_inspection' && $isPmi == 0) {
                //     if($request['editVehicleMaintenanceEventDate'] == $request['editVehicleMaintenancePlannedDate']) {
                //         $serviceDt = Carbon::parse($request['editVehicleMaintenanceEventDate']);
                //         $nxtServiceDt = $serviceDt->add($interval);
                //     } else {
                //         $nxtServiceDt = Carbon::parse($request['selectedPmiDate']);
                //     }

                // } else if($vehicleMaintenanceEntry->eventType->slug == 'mot' && $isMotRescheduled == 0) {

                //     if($request['editVehicleMaintenanceEventDate'] == $request['editVehicleMaintenancePlannedDate']) {
                //         $serviceDt = Carbon::parse($request['editVehicleMaintenanceEventDate']);
                //         $nxtServiceDt = $serviceDt->add($interval);
                //     } else {
                //         $nxtServiceDt = Carbon::parse($request['selectedMotDate']);
                //     }

                // } else {

                //     $serviceDt = Carbon::parse($vehicleMaintenanceEntry->event_date);
                //     $nxtServiceDt = $serviceDt->add($interval);

                //     // do {
                //     //     $nxtServiceDt = $serviceDt->add($interval);
                //     // }
                //     // while ($nxtServiceDt->lt(Carbon::today()));

                // }

                if($eventType == 'mot' && $isMotRescheduled == 0) {
                    if($request['editVehicleMaintenanceEventDate'] == $request['editVehicleMaintenancePlannedDate']) {
                        $serviceDt = Carbon::parse($vehicleMaintenanceEntry->event_plan_date);
                        $nxtServiceDt = $serviceDt->add($interval);
                    } else {
                        $nxtServiceDt = Carbon::parse($request['selectedMotDate']);
                    }
                } else if($eventType == 'preventative_maintenance_inspection' && $isPmi == 0) {
                    if($request['editVehicleMaintenanceEventDate'] == $request['editVehicleMaintenancePlannedDate']) {
                        $serviceDt = Carbon::parse($vehicleMaintenanceEntry->event_plan_date);
                        $nxtServiceDt = $serviceDt->add($interval);
                    } else {
                        $nxtServiceDt = Carbon::parse($request['selectedPmiDate']);
                    }
                } else {
                    $serviceDt = Carbon::parse($vehicleMaintenanceEntry->event_date);
                    $nxtServiceDt = $serviceDt->add($interval);
                }

                \Log::info('nxtServiceDt: '.$nxtServiceDt);

                $vehicleNxtServiceDt = '';
                if($eventType == 'mot' || $eventType == 'preventative_maintenance_inspection') {
                    if($request['editVehicleMaintenanceEventDate'] == $request['editVehicleMaintenancePlannedDate']) {
                        $vehicleServiceDt = Carbon::parse($vehicleMaintenanceEntry->event_date);
                    } else {
                        if($eventType == 'preventative_maintenance_inspection') {
                            $vehicleServiceDt = $vehicleNxtServiceDt = Carbon::parse($request['selectedPmiDate']);
                        } else {
                            $vehicleNxtServiceDt = Carbon::parse($request['selectedMotDate']);
                        }
                    }
                } else {
                    $vehicleServiceDt = Carbon::parse($vehicleMaintenanceEntry->event_date);
                }

                if( $vehicleNxtServiceDt == '' || ($eventType == 'preventative_maintenance_inspection' && $vehicleNxtServiceDt->lt(Carbon::now())) ) {
                    do {
                        $vehicleNxtServiceDt = $vehicleServiceDt->add($interval);
                    }
                    while ($vehicleNxtServiceDt->lt(Carbon::today()));
                }

                $vehicle->$setColumn = $vehicleNxtServiceDt->format('d M Y');
                $vehicle->save();

                \Log::info('event_status: '.$vehicleMaintenanceEntry->event_status);

                //create new next month maintenance entry
                // need to confirm/check for next_service_inspection_distance slug/event
                if($vehicleMaintenanceEntry->event_status == 'Complete') {
                    \Log::info('creating entrye');
                    $user = User::where('first_name','System')->first();
                    $vehicleHistory = new VehicleMaintenanceHistory();
                    $vehicleHistory->vehicle_id = $vehicleMaintenanceEntry->vehicle_id;
                    $vehicleHistory->event_type_id = $vehicleMaintenanceEntry->event_type_id;
                    $vehicleHistory->event_plan_date = $nxtServiceDt->format('d M Y');
                    $vehicleHistory->event_status = 'Incomplete';
                    $vehicleHistory->created_by = $user->id;
                    $vehicleHistory->updated_by = $user->id;
                    \Log::info($vehicleHistory);
                    $vehicleHistory->save();
                }

                return $vehicle;
            }
        }
    }

    public function showMaintenanceHistory(Request $request) {
        $id = $request->id;
        $maintenanceEvent = VehicleMaintenanceHistory::with(['eventType','vehicle.type'])->find($id);
        $maintenanceEventTypes = config('config-variables.maintenanceEventTypes');
        $maintenanceEvent->event_type_text = config('config-variables.maintenanceEventTypes')[$maintenanceEvent->event_type];
        $isDVSAConfigurationTabEnabled = $this->vehicleService->isDVSAConfigurationTabEnabled();

        return view('_partials.vehicles.show_maintainance_history', compact('maintenanceEvent', 'isDVSAConfigurationTabEnabled'))->render();
    }

    public function getMaintenanceHistory(Request $request) {
        $id = $request->id;
        $vehicleMaintenancehistory = VehicleMaintenanceHistory::with(['eventType','vehicle.type'])->find($id);
        
        $vehicleMaintenancehistory->event_type_text = ucwords(str_replace('_', ' ', $vehicleMaintenancehistory->event_type));

        //$maintenanceEventTypes = config('config-variables.maintenanceEventTypes');
        $vehicle = Vehicle::with('type')->withTrashed()->find($vehicleMaintenancehistory->vehicle_id);

        $hiddenSlug = 'next_service_inspection';

        if ($vehicle->type->service_interval_type == 'Time') {
            $hiddenSlug = 'next_service_inspection_distance';
        }
        if($vehicleMaintenancehistory->eventType->is_standard_event==1){ //if its standard then include both
            $includedEventGroup=array(1);
        }else{
            $includedEventGroup=array(0); //only non-standard means custom
        }
        $maintenanceEventTypes = MaintenanceEvents::whereIn('is_standard_event',$includedEventGroup)->where('slug','!=',$hiddenSlug)->orderBy('name')->get();

        $maintenanceHistoryStatus = config('config-variables.maintenance_history_status');

        $isDVSAConfigurationTabEnabled = $this->vehicleService->isDVSAConfigurationTabEnabled();

        return view('_partials.vehicles.edit_maintainance_history', compact('vehicleMaintenancehistory', 'maintenanceEventTypes', 'vehicle','maintenanceHistoryStatus', 'isDVSAConfigurationTabEnabled'))->render();
    }

    public function deleteMaintenanceHistory(Request $request) {
        $id = $request->maintenancehistoryDeletId;
        $vehicleMaintenancehistoryDelete = VehicleMaintenanceHistory::with('eventType')->find($id);

       // dd($vehicleMaintenancehistoryDelete);

        //dd($vehicleMaintenancehistoryDelete);
        $event = $vehicleMaintenancehistoryDelete->eventType->slug;
        $eventId = $vehicleMaintenancehistoryDelete->eventType->id;
        $isStandardEvent = $vehicleMaintenancehistoryDelete->eventType->is_standard_event;
        $vehicle = $vehicleMaintenancehistoryDelete->vehicle;
        $vehicleType = $vehicleMaintenancehistoryDelete->vehicle->type;

        if($vehicleMaintenancehistoryDelete->event_status == 'Incomplete') {
            $totalIncompleteEntries = VehicleMaintenanceHistory::where('event_status', 'Incomplete')
                                                                ->where('event_type_id', $vehicleMaintenancehistoryDelete->event_type_id)
                                                                ->where('vehicle_id', $vehicleMaintenancehistoryDelete->vehicle_id)
                                                                ->count();
            if($totalIncompleteEntries == 1) {
                return 0;
            }
        }

        $vehicleMaintenancehistoryDelete->delete();

        // if ($isStandardEvent == 1) {
        //     $serviceMapping = collect(config('config-variables.service_column_mapping'));

        //     if ($event != 'next_service_inspection_distance') {
        //         $getColumn = $serviceMapping[$event]['get_column'];
        //         $setColumn = $serviceMapping[$event]['set_column'];

        //         $interval = NULL;
        //         if (isset($getColumn) && trim($getColumn) != '') {
        //             $interval = $vehicleType->$getColumn;
        //         } else {
        //             $interval = $serviceMapping[$event]['interval'];
        //         }
        //     }
        //     $condition = ['vehicle_id' => $vehicle->id, 'event_type_id' => $eventId];
        //     $oldHistoryRecord = VehicleMaintenanceHistory::where($condition)
        //         ->orderBy('event_date', 'desc')
        //         ->first();
        //     if ($oldHistoryRecord) {

        //         if ($event == 'next_service_inspection_distance') {
        //             $vehicleProfile = $vehicle->type;
        //             $common = new Common();
        //             $nextServiceDistance = $common->getNextServiceInspectionDistance($vehicle->last_odometer_reading,$vehicleProfile->service_inspection_interval);
        //             $vehicle->next_service_inspection_distance = $nextServiceDistance;
        //         } else {
        //             $prevServiceDt = $oldHistoryRecord->event_date;
        //             $prevServiceDt = Carbon::parse($prevServiceDt);

        //             $interval = \DateInterval::createFromDateString($interval);
        //             $prevServiceDt = $prevServiceDt->add($interval);

        //             $vehicle->$setColumn = $prevServiceDt->format('d M Y');
        //         }

        //     } else {

        //         if ($event == 'next_service_inspection_distance') {
        //             $vehicleProfile = $vehicle->type;
        //             $common = new Common();
        //             $nextServiceDistance = $common->getNextServiceInspectionDistance($vehicle->last_odometer_reading,$vehicleProfile->service_inspection_interval);
        //             $vehicle->next_service_inspection_distance = $nextServiceDistance;
        //         } else if ($event != 'preventative_maintenance_inspection') {
        //             $vehicle->$setColumn = NULL;
        //         } else {
        //             if (isset($vehicle->type) && $vehicle->type != null) {

        //                 $vehicleProfile = $vehicle->type;
        //                 $pmiInterval = $vehicleProfile->pmi_interval;
        //                 $firstPmiDate = $vehicle->first_pmi_date;
        //                 $currentDate = Carbon::now()->format('d M Y');

        //                 if($pmiInterval && $firstPmiDate) {
        //                     while (strtotime($firstPmiDate) < strtotime($currentDate)) {
        //                         $firstPmiDate = date ("d M Y", strtotime($pmiInterval, strtotime($firstPmiDate)));
        //                     }
        //                 }
        //                 $vehicle->next_pmi_date = $firstPmiDate;
        //                 //$vehicle->save();
        //             }
        //         }
        //     }
        //     $vehicle->save();
        // }

        return 1;
    }

    public function maintenanceHistoryList(Request $request) {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->addYear()->startOfMonth();

        $endYear = Carbon::now()->addYear()->format('Y');
        $endMonth = Carbon::now()->addYear()->format('n')-1;

        $maintenanceEvents = MaintenanceEvents::whereIn('is_standard_event', [1,2])->get()->keyBy('slug');
        $pmiEventId = $maintenanceEvents['preventative_maintenance_inspection']->id;

        $eventsList = config('config-variables.eventSlugWithVehicleFields');
        $et_keys = array_keys($eventsList);
        $et_values = array_values($eventsList);
        $vehicle = Vehicle::find($request->id);
        $maintenanceList = [];
        $i = 0;
        foreach ($eventsList as $value => $event  ) {
            $eventName = isset($maintenanceEvents[$value]) ? $maintenanceEvents[$value]->name : '' ;

            if ($value == 'next_service_inspection_distance') {
                $distanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
                $distanceEvents = VehicleMaintenanceHistory::where('event_type_id',$distanceEvent->id)
                    ->where('vehicle_id',$vehicle->id)
                    ->where('event_status', 'Incomplete')
                    ->where('event_plan_date','>=',$start)
                    ->where('event_plan_date','<=',$end)
                    ->get();

                if (count($distanceEvents) > 0) {
                    foreach ($distanceEvents as $event) {
                        $dt = Carbon::parse($event->event_plan_date)->format('F Y');
                        $eventDt = Carbon::parse($event->event_plan_date)->format('Y-m-d');
                        $eventDtFormat = Carbon::parse($event->event_plan_date)->format('d M Y');
                        $maintenanceList[$dt][$i]['value'] = $distanceEvent->name . ' (' . $eventDtFormat . ')';
                        $maintenanceList[$dt][$i]['date'] = $eventDt;
                        $maintenanceList[$dt][$i]['event'] = $distanceEvent->name;
                        $i++;
                    }
                }
            } else if(isset($vehicle->$event)) {
                if($event == 'next_pmi_date') {
                    $serviceInterval = $vehicle->type->pmi_interval;
                    if($vehicle->next_pmi_date == '' || $vehicle->next_pmi_date == NULL || $serviceInterval == '' || $serviceInterval == 'none' || $serviceInterval == NULL ) {
                        continue;
                    }
                    $event = 'first_pmi_date';
                    $interval = \DateInterval::createFromDateString($serviceInterval);
                    $nextPmiDate = Carbon::parse($vehicle->next_pmi_date);
                    // $eventDate = Carbon::parse($vehicle->$event);
                    $eventDate = $nextPmiDate->sub($interval);
                    $year = $eventDate->format('Y');
                    $month = $eventDate->format('n');
                    $isUpdatedNextPmi = 0;
                    while($end->diffInDays($eventDate, false) < 0){
                        if ($isUpdatedNextPmi == 0 && $eventDate->gte($nextPmiDate)) {
                            $eventDate = $nextPmiDate;
                            $isUpdatedNextPmi = 1;
                        }
                        $evDate = clone $eventDate;
                        $dt = $evDate->format('F Y');
                        $nxtServiceDt = $evDate->format('d M Y');

                        $checkEventIsCompleted = $vehicle->maintenanceHistories()->where('event_type_id', $pmiEventId)
                                                                            ->where('event_status', 'Incomplete')
                                                                            ->orderBy('event_plan_date', 'desc')
                                                                            ->first(['event_plan_date']);

                        if(!isset($checkEventIsCompleted) || $evDate->gte(Carbon::parse($checkEventIsCompleted->event_plan_date))) {
                            //If conditions points to #6550
                            if(isset($maintenanceList[$dt])) {
                                $maintenanceList[$dt] = array_values($maintenanceList[$dt]);
                                if (array_search($eventName, array_column($maintenanceList[$dt], 'event')) !== FALSE) {
                                    $eventKey = array_search($eventName, array_column($maintenanceList[$dt], 'event'));
                                    unset($maintenanceList[$dt][$eventKey]);
                                    $maintenanceList[$dt] = array_values($maintenanceList[$dt]);
                                }
                            }
                            $maintenanceList[$dt][$i]['value'] = $eventName . ' (' . $nxtServiceDt . ')';
                            $maintenanceList[$dt][$i]['date'] = $evDate->format('Y-m-d');
                            $maintenanceList[$dt][$i]['event'] = $eventName;
                        }
                        $eventDate = $eventDate->add($interval);
                        $year = $evDate->format('Y');
                        $i++;
                    }
                } else {
                    $dt = Carbon::parse($vehicle->$event)->format('F Y');
                    $eventDt = Carbon::parse($vehicle->$event)->format('Y-m-d');
                    $eventDtFormat = Carbon::parse($vehicle->$event)->format('d M Y');
                    $maintenanceList[$dt][$i]['value'] = $eventName . ' (' . $eventDtFormat . ')';
                    $maintenanceList[$dt][$i]['date'] = $eventDt;
                    $maintenanceList[$dt][$i]['event'] = $eventName;
                    $i++;
                }
            }
        }

        foreach ($maintenanceList as $key => $value) {
            usort($value, function($a, $b) {
                if(isset($a['date']) && isset($b['date'])){
                    return strtotime($a['date']) - strtotime($b['date']);
                }
                return true;
            });
            $maintenanceList[$key] = $value;
        }
        return view('_partials.vehicles.12month_schedule', compact('start', 'end', 'maintenanceList'))->render();
    }

    public function storeComment(StoreVehiclePlanningHistoryRequest $request)
    {
        $vehiclePlanningComment = new VehiclePlanningComment();
        $data = $request->all();
        $user = Auth::user();
        $vehiclePlanningComment->user_id = $user->id;
        $vehiclePlanningComment->vehicle_id = $request->vehicle_id;
        $vehiclePlanningComment->comment = $request->comments;
        $vehiclePlanningComment->comment_datetime = Carbon::now();

        if($vehiclePlanningComment->save()){
            if (!empty($request->file())) {
                $fileName = $request->file('attachment')->getClientOriginalName();
                $customFileName = preg_replace('/\s+/', '_', $fileName);
                if(!empty($request->file_input_name)) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $customFileName = $request->file_input_name . "." . $ext;
                }
                $vehiclePlanningMedia = VehiclePlanningComment::findOrFail($vehiclePlanningComment->id);
                $fileToSave= $request->file('attachment')->getRealPath();
                $vehiclePlanningMedia->addMedia($fileToSave)
                                    ->setFileName($customFileName)
                                    ->withCustomProperties(['mime-type' => $request->file('attachment')->getMimeType()])
                                    ->toCollectionOnDisk('vehicle_planning_comment', 'S3_uploads');
            }

            flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        return redirect('vehicles/'.$request->vehicle_id.'#planning');
    }

    public function downloadPlanningMedia($id) {
        $vehicle = VehiclePlanningComment::findOrFail($id);
        $media = $vehicle->getMedia();
        return redirect(getPresignedUrl($media[0]));
        //return redirect($media[0]->getUrl());
    }

    public function updateComment(Request $request) {
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $comment = VehiclePlanningComment::find($id);
        $comment->comment = $value;
        $comment->save();
    }

    public function destroyComment($id) {
        $vehiclePlanningComment = VehiclePlanningComment::where('id', $id)->first();
        if(VehiclePlanningComment::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('vehicles/'.$vehiclePlanningComment->vehicle_id.'#planning');
    }

    public function saveVehicleListingFields(Request $request){
        $vehicle = Vehicle::where('id',$request->vehicleId)->withTrashed()->first();

        $vehicleListingRequestFieldName = $request->field;
        $vehicleListingRequestJson = $request->json;
        if(isset($vehicleListingRequestFieldName) && $vehicleListingRequestFieldName != ''){
            $vehicleListingFieldCost = $vehicleListingRequestJson;
            $vehicle->$vehicleListingRequestFieldName = $vehicleListingFieldCost;
            $vehicle->save();
        }
        return $vehicleListingRequestFieldName;
    }
    public function editMonthlyInsuranceCost(Request $request)
    {
        $vehicle = Vehicle::where("id",$request['vehicleId'])->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isInsuranceCostOverride = $vehicle->is_insurance_cost_override;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request['vehicleId'])->orderBy('id','DESC')->first();
        $monthlyInsuranceJson = $request['monthlyInsuranceField'];
        $monthlyInsuranceCostField = json_decode($monthlyInsuranceJson, true);
        $monthlyInsuranceArray = [];
        if($request['monthlyInsuranceField']){
            foreach ($monthlyInsuranceCostField as $key => $editMonthlyInsuranceCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editMonthlyInsuranceCost['cost_value']);
                $finalArray['cost_from_date'] = $editMonthlyInsuranceCost['cost_from_date'];
                $finalArray['cost_to_date'] = $editMonthlyInsuranceCost['cost_to_date'];
                $finalArray['cost_continuous'] = $editMonthlyInsuranceCost['cost_continuous'];

                $monthlyInsuranceArray[] = $finalArray;
            }
        }
        $monthlyInsuranceCostField = $monthlyInsuranceArray;
        $vehicle->insurance_cost = json_encode($monthlyInsuranceCostField);
        $vehicle->save();

        $currentCost = 0;
        $currentDate = '';
        $insuranceFieldCurrentDateValue = '';

        $commonHelper = new Common();
        $insuranceFieldCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->insurance_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,(int)$isInsuranceCostOverride);
        //$insuranceFieldCurrentData = $this->calcMonthlyCurrentData($vehicle->insurance_cost,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,'N/A');
        $currentCost = $insuranceFieldCurrentData['currentCost'];
        $currentDate = $insuranceFieldCurrentData['currentDate'];
        // $insuranceFieldCurrentDateValue = $insuranceFieldCurrentData['currentDateValue'];

        $vehicleDepreciation = json_decode($vehicle->insurance_cost, true);

        if(isset($vehicleDepreciation)){
            foreach ($vehicleDepreciation as $fleetCost) {

                $currentDate = Carbon::now()->startOfDay();

                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $insuranceFieldCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $insuranceValueDisplay = '';
        if($vehicle->insurance_cost != ''){
            $insuranceValueDisplay = json_decode($vehicle->insurance_cost,true);
        }
        return view('_partials.vehicles.monthly_insurance_cost_history')
            ->with('insuranceValueDisplay',$insuranceValueDisplay)
            ->with('insuranceFieldCurrentDate',$currentDate)
            ->with('insuranceFieldCurrentCost',$currentCost)
            ->with('insuranceFieldCurrentDateValue',$insuranceFieldCurrentDateValue)
            ->with('vehicle',$vehicle);
    }

    public function editMonthlyInsuranceCostOverride(Request $request) {
        $vehicle = Vehicle::where('id',$request->vehicleId)->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request->vehicleId)->orderBy('id','DESC')->first();
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isInsuranceCostOverride = $request->is_insurance_cost_override;
        $vehicle->is_insurance_cost_override = $isInsuranceCostOverride;
        $vehicle->save();

        if($isInsuranceCostOverride == 0) {
            $vehicle->insurance_cost = null;
            $vehicle->save();
        }

        $monthlyInsuranceData = '';
        // $monthlyInsurance = Settings::where('key', 'fleet_cost_area_detail')->first();
        // $monthlyInsuranceJson = $monthlyInsurance->value;
        $monthlyInsuranceJson = $vehicle->type->annual_insurance_cost;
        $monthlyInsuranceData = json_decode($monthlyInsuranceJson, true);

        if ($vehicle->is_insurance_cost_override == 1) {
            $finalCost = [];
            foreach ($monthlyInsuranceData as $row) {
                if ($row['cost_to_date'] != '' && \Carbon\Carbon::parse($row['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet))) {
                    // Do nothing
                } else {
                    $row['cost_from_date'] = \Carbon\Carbon::parse($row['cost_from_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet)) ? \Carbon\Carbon::parse($vehicle->dt_added_to_fleet)->format('d M Y') : $row['cost_from_date'];
                    array_push($finalCost, $row);
                }
            }
        } else {
            $finalCost = $monthlyInsuranceData;
        }

        if($vehicle->is_insurance_cost_override != 1) {
            $insuranceValue = json_encode($finalCost);
        } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost == ''){
            $insuranceValue = json_encode($finalCost);
        } else {
            $insuranceValue = json_encode($finalCost);
        }

        $insuranceFieldCurrentCost = 0;
        $insuranceFieldCurrentDate = '';
        if(isset($insuranceValue)){
            $commonHelper = new Common();
            $insuranceCurrentData = $commonHelper->getFleetCostValueForDate($insuranceValue,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,(int)$isInsuranceCostOverride);
            //$insuranceCurrentData = $this->calcMonthlyCurrentData($insuranceValue,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,(int)$isInsuranceCostOverride);
            $insuranceFieldCurrentCost = $insuranceCurrentData['currentCost'];
            $insuranceFieldCurrentDate = $insuranceCurrentData['currentDate'];
        }

        $insuranceFieldCurrentDateValue = '';
        $vehicleDepreciation = json_decode($insuranceValue, true);
        if(isset($vehicleDepreciation)){
            foreach ($vehicleDepreciation as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $insuranceFieldCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        if($isInsuranceCostOverride == 1) {
            $vehicle->insurance_cost = $insuranceValue;
            $vehicle->save();
        }

        //dd($insuranceValue);
        /*return view('_partials.vehicles.monthly_insurance_cost_history')
            ->with('insuranceValueDisplay',$vehicleDepreciation)
            ->with('insuranceFieldCurrentDate',$insuranceFieldCurrentDate)
            ->with('insuranceFieldCurrentCost',$insuranceFieldCurrentCost)
            ->with('insuranceFieldCurrentDateValue',$insuranceFieldCurrentDateValue);*/
        //dd($isInsuranceCostOverride,$vehicle->is_insurance_cost_override);
        $data = [
            'cost' => $insuranceFieldCurrentCost,
            'html' => \Illuminate\Support\Facades\View::make('_partials.vehicles.monthly_insurance_cost_history',[
                'insuranceValueDisplay' => $vehicleDepreciation,
                'insuranceFieldCurrentDate' => $insuranceFieldCurrentDate,
                'insuranceFieldCurrentCost' => $insuranceFieldCurrentCost,
                'insuranceFieldCurrentDateValue' => $insuranceFieldCurrentDateValue,
                'vehicle' => $vehicle
            ])->render(),
            'html_edit' => \Illuminate\Support\Facades\View::make('_partials/vehicles/edit_monthly_insurance_cost',['insuranceValueDisplay' => json_decode($insuranceValue,true),'vehicle' => $vehicle])->render(),
        ];

        return $data;
        return $insuranceFieldCurrentCost;
    }

    public function editMonthlyTelematicsCost(Request $request){
        $vehicle = Vehicle::where("id",$request['vehicleId'])->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request['vehicleId'])->orderBy('id','DESC')->first();
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isTelematicsCostOverride = $vehicle->is_telematics_cost_override;
        $monthlyTelematicsJson = $request['monthlyTelematicsField'];
        $monthlyTelematicsCostField = json_decode($monthlyTelematicsJson, true);
        $monthlyTelematicsArray = [];
        if($request['monthlyTelematicsField']){
            foreach ($monthlyTelematicsCostField as $key => $editMonthlyTelematicsCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editMonthlyTelematicsCost['cost_value']);
                $finalArray['cost_from_date'] = $editMonthlyTelematicsCost['cost_from_date'];
                $finalArray['cost_to_date'] = $editMonthlyTelematicsCost['cost_to_date'];
                $finalArray['cost_continuous'] = $editMonthlyTelematicsCost['cost_continuous'];
                $monthlyTelematicsArray[] = $finalArray;
            }
        }

        $monthlyTelematicsCostField = $monthlyTelematicsArray;
        $vehicle->telematics_cost = json_encode($monthlyTelematicsCostField);
        $vehicle->save();

        $currentCost = 0;
        $currentDate = '';
        $telematicsFieldCurrentDateValue = '';
        // $vehicle->telematics_cost = $request['monthlyTelematicsField'];
        $commonHelper = new Common();
        $telematicsFieldCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->telematics_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isTelematicsCostOverride);
        //$telematicsFieldCurrentData = $this->calcMonthlyCurrentData($vehicle->telematics_cost,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A',$isTelematicsCostOverride);
        $currentCost = $telematicsFieldCurrentData['currentCost'];
        $currentDate = $telematicsFieldCurrentData['currentDate'];
        // $telematicsFieldCurrentDateValue = $telematicsFieldCurrentData['currentDateValue'];

        $vehicleTelematicsValue = json_decode($vehicle->telematics_cost, true);
        if(isset($vehicleTelematicsValue)){
            foreach ($vehicleTelematicsValue as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $telematicsFieldCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        $telematicsValueDisplay = '';
        if($vehicle->telematics_cost != ''){
            $telematicsValueDisplay = json_decode($vehicle->telematics_cost,true);
        }
        return view('_partials.vehicles.monthly_telematics_cost_history')
            ->with('telematicsValueDisplay',$telematicsValueDisplay)
            ->with('telematicsFieldCurrentDate',$currentDate)
            ->with('telematicsFieldCurrentCost',$currentCost)
            ->with('telematicsFieldCurrentDateValue',$telematicsFieldCurrentDateValue)
            ->with('vehicle',$vehicle);
    }

    public function editMonthlyTelematicsCostOverride(Request $request) {
        $vehicle = Vehicle::where('id',$request->vehicleId)->withTrashed()->first();
        $vehicleId = $vehicle->id;
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request->vehicleId)->orderBy('id','DESC')->first();
        $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
        $isTelematicsCostOverride = $request->is_telematics_cost_override;
        $vehicle->is_telematics_cost_override = $isTelematicsCostOverride;
        if($request['is_telematics_cost_override'] == 1) {
            $vehicle->is_telematics_enabled = $request['is_telematics_enabled'];
        }
        $vehicle->save();

        if($isTelematicsCostOverride == 0) {
            $vehicle->telematics_cost = null;
            $vehicle->save();
        }

        $monthlyInsuranceData = '';
        $monthlyInsurance = Settings::where('key', 'fleet_cost_area_detail')->first();
        $monthlyInsuranceJson = $monthlyInsurance->value;
        $monthlyInsuranceData = json_decode($monthlyInsuranceJson, true);

        $telematicsValue = '';
        $telematicsValueDisplay = '';
        if ($vehicle->is_telematics_cost_override == 1) {
            $finalCost = [];
            foreach ($monthlyInsuranceData['telematics_insurance_cost'] as $row) {
                if ($row['cost_to_date'] != '' && \Carbon\Carbon::parse($row['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet))) {
                    // Do nothing
                } else {
                    $row['cost_from_date'] = \Carbon\Carbon::parse($row['cost_from_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet)) ? \Carbon\Carbon::parse($vehicle->dt_added_to_fleet)->format('d M Y') : $row['cost_from_date'];
                    array_push($finalCost, $row);
                }
            }
        } else {
            $finalCost = $monthlyInsuranceData['telematics_insurance_cost'];
        }
        if($vehicle->is_telematics_cost_override != 1) {
            $telematicsValue = json_encode($finalCost);
        } else if($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost != ''){
            $telematicsValue = $vehicle->telematics_cost;
        } else {
            $telematicsValue = json_encode($finalCost);
        }

        if($isTelematicsCostOverride == 1) {
            $vehicle->telematics_cost = $telematicsValue;
            $vehicle->save();
        }


        $telematicsFieldCurrentCost = 0;
        $telematicsFieldCurrentDate = '';
        $telematicsFieldCurrentDateValue = '';
        if(isset($telematicsValue)){
            //$telematicsCurrentData = $this->calcMonthlyCurrentData($telematicsValue,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A',(int)$isTelematicsCostOverride);
            $commonHelper = new Common();
            $telematicsCurrentData = $commonHelper->getFleetCostValueForDate($telematicsValue,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory,$vehicleDtAddedToFleet,(int)$isTelematicsCostOverride);
            $telematicsFieldCurrentCost = $telematicsCurrentData['currentCost'];
            $telematicsFieldCurrentDate = $telematicsCurrentData['currentDate'];
            $telematicsFieldCurrentDateValue = $telematicsCurrentData['currentDateValue'];
        }

        $data = [
            'cost' => $telematicsFieldCurrentCost,
            'html' => \Illuminate\Support\Facades\View::make('_partials.vehicles.monthly_telematics_cost_history',[
                'telematicsValueDisplay' => json_decode($telematicsValue,true),
                'telematicsFieldCurrentDate' =>$telematicsFieldCurrentDate,
                'telematicsFieldCurrentCost' =>$telematicsFieldCurrentCost,
                'telematicsFieldCurrentDateValue' =>$telematicsFieldCurrentDateValue,
                'vehicle' => $vehicle
            ]
            )->render(),
            'html_edit' => \Illuminate\Support\Facades\View::make('_partials/vehicles/edit_monthly_telematics',['telematicsValueDisplay' => json_decode($telematicsValue,true),'vehicle' => $vehicle])->render(),
        ];

        return $data;
        return $telematicsFieldCurrentCost;
    }

    public function viewVehicleCostSummaryCurrentMonth(Request $request)
    {
        return $this->viewVehicleCostSummaryCurrentMonthCalc($request->field);
    }

    private function viewVehicleCostSummaryCurrentMonthCalc($manualCostJson,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet='N/A',$isInsuranceCostOverride='N/A',$isTelematicsCostOverride=null,$type = "",$isVehicleTax = 0){
        $commonHelper = new Common();
        return $commonHelper->calcMonthlyCurrentData($manualCostJson,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,null,$type,$isVehicleTax);
    }

    public function viewVehicleCostSummaryBasedOnPeriod(Request $request)
    {
        return $this->viewVehicleCostSummaryBasedOnPeriodCalc($request->field);
    }

    private function viewVehicleCostSummaryBasedOnPeriodCalc($valueCostJson,$selectedDateValue,$vehicleId,$vehicleArchiveHistory){
        $commonHelper = new Common();
        return $commonHelper->calcCurrentMonthBasedOnPeriod($valueCostJson,$selectedDateValue,$vehicleId,$vehicleArchiveHistory);
    }

    /**
     * Update the lease cost of a vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editDepreciationCost(Request $request)
    {
        $field = $request['monthlyDepreciationField'];
        $vehicle = Vehicle::where("id",$request['vehicleId'])->withTrashed()->first();
        $vehicleId = $vehicle->id;

        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$request['vehicleId'])->orderBy('id','DESC')->first();
        $deprectionCostField = json_decode($field, true);

        $deprectionCostArray = [];
        if($request['monthlyDepreciationField']){
            foreach ($deprectionCostField as $key => $deprectionCost) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $deprectionCost['cost_value']);
                $finalArray['cost_from_date'] = $deprectionCost['cost_from_date'];
                $finalArray['cost_to_date'] = $deprectionCost['cost_to_date'];
                $finalArray['cost_continuous'] = $deprectionCost['cost_continuous'];
                $deprectionCostArray[] = $finalArray;
            }
        }
        $deprectionCostField = $deprectionCostArray;
        $vehicle->monthly_depreciation_cost = json_encode($deprectionCostField);
        $vehicle->save();

        $depreciationCost = 0;
        $depreciationCurrentDate = '';
        $depreciationCurrentDateValue = '';
        $commonHelper = new Common();
        $depreciationCurrentData = $commonHelper->getFleetCostValueForDate($vehicle->monthly_depreciation_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
        //$depreciationCurrentData = $this->calcMonthlyCurrentData($vehicle->monthly_depreciation_cost,$vehicleId,$vehicleArchiveHistory);
        $depreciationCost = $depreciationCurrentData['currentCost'];
        $depreciationCurrentDate = $depreciationCurrentData['currentDate'];
        // $depreciationCurrentDateValue = $depreciationCurrentData['currentDateValue'];

        $depreciationHistoryDate = '';
        $vehicleDepreciation = json_decode($vehicle->monthly_depreciation_cost, true);
        if(isset($vehicleDepreciation)){
            foreach ($vehicleDepreciation as $fleetCost) {
                $currentDate = Carbon::now()->startOfDay();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate->gte($annualInsuranceFromDate) && $currentDate->lte($annualInsuranceToDate)){
                    $depreciationCurrentDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        return view('_partials.vehicles.depreciation_cost_history')
            ->with('deperectionCurrentCost',$depreciationCost)
            ->with('depreciationCurrentDate',$depreciationCurrentDate)
            ->with('vehicle',$vehicle)
            ->with('depreciationCurrentDateValue',$depreciationCurrentDateValue);
    }

    public function getAssignmentHistory(Request $request)
    {
        $id = $request->id;
        $vehicleAssignmentHistory = VehicleAssignment::find($id);
        // $data = $this->vehicleService->getData();
        $data = $this->vehicleService->getDataDivRegLoc();
        asort($data['vehicleDivisions']);

        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleRegions'] as $regionId => $regions) {
                asort($regions);
                $data['vehicleRegions'][$regionId] = $regions;
            }
        } else {
            asort($data['vehicleRegions']);
        }

        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            foreach($data['vehicleBaseLocations'] as $locationId => $locations) {
                asort($locations);
                $data['vehicleBaseLocations'][$locationId] = $locations;
            }
        } else {
            asort($data['vehicleBaseLocations']);
        }

        $divisionName = VehicleDivisions::where('id','=',$vehicleAssignmentHistory->vehicle_division_id)->first();
        $vehicleDivisionValue = $divisionName['name'];

        $vehicleRegionName = VehicleRegions::where('id','=',$vehicleAssignmentHistory->vehicle_region_id)->first();
        $vehicleRegionNameValue = $vehicleRegionName['name'];

        $vehicleLocationName = VehicleLocations::where('id','=',$vehicleAssignmentHistory->vehicle_location_id)->first();
        $vehicleLocationNameValue = $vehicleLocationName['name'];

        $vehicleDivisions = $data['vehicleDivisions'];
        $vehicleRegions = $data['vehicleRegions'];
        $vehicleLocation = $data['vehicleBaseLocations'];

        $vehicle = Vehicle::withTrashed()->find($vehicleAssignmentHistory['vehicle_id']);
        $vehicleRegistration = $vehicle['registration'];

        return view('_partials.vehicles.edit_assignment', compact('vehicleAssignmentHistory','vehicleDivisions','vehicleDivisionValue','vehicleRegionNameValue','vehicleRegions','vehicleLocationNameValue','vehicleLocation','vehicleRegistration','vehicle'))->render();
    }

    public function editAssignmentUpdateHistory(Request $request)
    {
        $editAssignmentRequestFromDate = Carbon::parse($request->editAssignmentFromDate);
        $editAssignmentRequestToDate = Carbon::parse($request->editAssignmentToDate);
        $divisionName = VehicleDivisions::where('id','=',$request['editAssignmentDivision'])->first();
        $vehicleRegionName = VehicleRegions::where('id','=',$request['editAssignmentRegion'])->first();
        $vehicleLocationName = VehicleLocations::where('id','=',$request['editAssignmentLocation'])->first();
        $id = $request->assignmentHistoryEditId;

        /*$overlapping = VehicleAssignment::where('vehicle_id', $request['editVehicleId'])->where('id','!=',$id)->get();

        if($overlapping->count() > 0) {
            $overlappingMaxDate = VehicleAssignment::where('vehicle_id', $request['editVehicleId'])->where('id','!=',$id)->selectRaw('MAX(to_date) as max_date')->first();

            $maxDate =  Carbon::parse($overlappingMaxDate->max_date);
            foreach ($overlapping as $key => $value) {
                $valueStartDate = Carbon::parse($value->from_date);
                $valueEndDate = Carbon::parse($value->to_date);

                if($editAssignmentRequestFromDate->gte($editAssignmentRequestToDate)){

                } else if($valueStartDate == $editAssignmentRequestFromDate || $valueStartDate == $editAssignmentRequestToDate){
                    return response()->json([
                            'status' => false
                        ]);
                }

                if($valueStartDate == $editAssignmentRequestFromDate || $valueEndDate == $editAssignmentRequestToDate) {
                } else {
                    if($valueStartDate->gt($editAssignmentRequestFromDate) && $valueEndDate->lt($editAssignmentRequestFromDate)){
                        return response()->json([
                                'status' => false
                            ]);

                    }else if($valueStartDate->gte($editAssignmentRequestToDate) && $valueEndDate->lte($editAssignmentRequestToDate)) {
                        return response()->json([
                                'status' => false
                            ]);

                    }else if($editAssignmentRequestFromDate->gt($valueStartDate) && $editAssignmentRequestFromDate->lt($valueEndDate)){
                        return response()->json([
                                'status' => false
                            ]);

                    } else if ($editAssignmentRequestToDate->gt($valueStartDate) && $editAssignmentRequestToDate->lt($valueEndDate)){
                        return response()->json([
                                'status' => false
                            ]);

                    } else if($editAssignmentRequestFromDate->lte($valueStartDate) && $editAssignmentRequestToDate->gte($valueEndDate)){
                        return response()->json([
                                'status' => false
                            ]);
                    }
                }
            }
        }*/

        $vehicleAssignmentEdit = VehicleAssignment::findOrFail($id);
        $vehicleAssignmentEdit->vehicle_id = $request['editVehicleId'];
        $vehicleAssignmentEdit->vehicle_division_id = $divisionName['id'];
        $vehicleAssignmentEdit->vehicle_region_id = $vehicleRegionName['id'];
        $vehicleAssignmentEdit->vehicle_location_id = $vehicleLocationName['id'];
        //$vehicleAssignmentEdit->from_date = $request['editAssignmentFromDate'];
        if($request['editAssignmentToDate'] != $vehicleAssignmentEdit->to_date) {
            $vehicleAssignmentEdit->to_date = $request['editAssignmentToDate'];
            $fetchNextRecord = VehicleAssignment::where('id', '>', $id)->where('vehicle_id', $request['editVehicleId'])->first();
            if(isset($fetchNextRecord)) {
                $fetchNextRecord->from_date = Carbon::parse($request['editAssignmentToDate'])->addDays(1)->format('d M Y');
                $fetchNextRecord->save();
            }
        }
        $vehicleAssignmentEdit->save();
        return response()->json([
            'status' => true
        ]);
    }

    public function addAssignmentUpdateHistory(Request $request)
    {
        $vehicle = Vehicle::find($request->vehicle_id);
        if($request->addAssignmentDivision != $vehicle->vehicle_division_id || $request->addAssignmentRegion != $vehicle->vehicle_region_id || $request->addAssignmentLocation != $vehicle->vehicle_location_id) {
            $checkAssignment = VehicleAssignment::where('vehicle_id',$vehicle->id)->orderBy('id', 'DESC')->first();
            if ($checkAssignment) {
                $vehicleValue = $checkAssignment;
                $toDate = Carbon::now()->subDays('1');
                $fromDate = Carbon::parse($vehicleValue->from_date);

                if ($toDate->lt($fromDate)) {
                    $toDate = $fromDate;
                }
                $vehicleValue->to_date = $toDate->format('d M Y');
                $vehicleValue->save();
            } else {
                $vehicleAssignment = new VehicleAssignment();
                $vehicleAssignment->vehicle_id = $vehicle->id;
                $vehicleAssignment->vehicle_division_id = $vehicle->vehicle_division_id;
                if(isset($vehicle->vehicle_location_id)) {
                    $vehicleAssignment->vehicle_location_id = $vehicle->vehicle_location_id;
                }
                $vehicleAssignment->vehicle_region_id = $vehicle->vehicle_region_id;
                $vehicleAssignment->from_date = Carbon::parse($vehicle->dt_added_to_fleet)->format('d M Y');
                $toDate = Carbon::now()->subDays('1');
                $fromDate = Carbon::parse($vehicleAssignment->from_date);

                if ($toDate->lt($fromDate)) {
                    $toDate = $fromDate;
                }
                $vehicleAssignment->to_date = $toDate->format('d M Y');
                $vehicleAssignment->save();
            }

            $vehicleAssignmentLast = new VehicleAssignment();
            $vehicleAssignmentLast->vehicle_id = $vehicle->id;
            $vehicleAssignmentLast->vehicle_division_id = $request->addAssignmentDivision;
            if($request->addAssignmentLocation) {
                $vehicleAssignmentLast->vehicle_location_id = $request->addAssignmentLocation;
            }
            $vehicleAssignmentLast->vehicle_region_id = $request->addAssignmentRegion;
            $vehicleAssignmentLast->from_date = Carbon::now()->format('d M Y');
            $vehicleAssignmentLast->to_date = null;
            $vehicleAssignmentLast->save();
        }
        $vehicle->vehicle_division_id = $request->addAssignmentDivision;
        if($request->addAssignmentLocation) {
            $vehicle->vehicle_location_id = $request->addAssignmentLocation;
        }
        $vehicle->vehicle_region_id = $request->addAssignmentRegion;
        $vehicle->save();
        return response()->json([
            'status' => true
        ]);
    }

    public function deleteAssignmentHistory(Request $request)
    {
        $id = $request->assignmentDeletId;
        $vehicleAssignmenthistoryDelete = VehicleAssignment::find($id);
        $vehicleAssignmenthistoryDelete->delete();
    }

    public function getAllVehicleRepairLocations() {
        $vehicleRepairLocations = VehicleRepairLocations::with('vehicles')->orderBy('name', 'asc')->get()->toArray();
        $vehicleRepairLocationsList = ['' => ''] + $vehicleRepairLocations;
        $returnJsonString = array();
        foreach ($vehicleRepairLocations as $key => $value) {
            array_push($returnJsonString, ['id'=>$value['id'], 'name'=>$value['name'], 'vehicle_repair'=>$value['vehicles']]);
        }
        return $returnJsonString;
    }

    public function viewAllLocations(Request $request) {
        $data = $request->all();
        $redirect = $data['redirect'];
        if($redirect == 'vehicles') {
          return $this->getAllVehicleRepairLocations();
        }
    }

    public function locationDelete(Request $request){
        $data = $request->all();
        $id = $data['id'];
        $redirect = $data['redirect'];
        if(VehicleRepairLocations::where('id', $id)->forcedelete()) {
            if($redirect == 'vehicles') {
              return $this->getAllVehicleRepairLocations();
            }
        }
    }

    public function updateLocationName(Request $request){
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $locationName = VehicleRepairLocations::find($id);
        $locationName->name = $value;
        if($locationName->save()) {
          if($field == 'vehicles') {
            return $this->getAllVehicleRepairLocations();
          }
        }
    }

    public function updateDateForArchivedVehicleStatuses(Request $request)
    {        
        $vehicle = Vehicle::withTrashed()->find($request->pk);
        $vehicle->archived_date = $request->value;
        $vehicle->save();

        $eventDateTime = Carbon::parse($request->value)->startOfDay()->format('Y-m-d H:i:s');
        $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id', $request->pk)
                                                        ->where('event', 'Archived')
                                                        ->get()
                                                        ->last();

        if ($vehicleArchiveHistory) {
            $vehicleArchiveHistory->event_date_time = $eventDateTime;
            $vehicleArchiveHistory->save();
        }

        return response()->json([
            'vehicle' => $vehicle
        ]);
    }

    public function checkDateAddedToFleet(Request $request) 
    {
        if ($request->id == "undefined") {
            return "true";
        }
        $vehicle = Vehicle::withTrashed()->find($request->id);

        if ($vehicle && $vehicle->archived_date != null) {
            $archivedDate = Carbon::parse($vehicle->archived_date);
            $dateAddedToFleet = Carbon::parse($request->dt_added_to_fleet);

            $isDateAddedToFleetBigger = $dateAddedToFleet->gt($archivedDate);
            if ($isDateAddedToFleetBigger) {
                return "false";
            }
        }
        return "true";
    }

    public function addEvent(Request $request) {
        $maintenanceEvent = MaintenanceEvents::where('name',$request->name)->first();

        if ($maintenanceEvent) {
            return [
                'status' => false,
                'msg' => 'Event name already exists.'
            ];
        } else {
            $maintenanceEvent = new MaintenanceEvents();
            $maintenanceEvent->name = $request->name;
            $maintenanceEvent->slug= str_slug($request->name,'_');
            $maintenanceEvent->save();

            $dataAll = $this->getAllMaitenanceEvent($request,$maintenanceEvent->id);
            $data = $this->getAddedMaitenanceEvent($request,$maintenanceEvent->id);
            return [
                'status' => true,
                'data' => $maintenanceEvent,
                'options' =>$data['options'],
                'tBody' =>$data['tBody'],
                'optionsAll' =>$dataAll['options'],
            ];
        }
    }

    public function getAddedMaitenanceEvent(Request $request,$eventId=0) {

        $vehicle = Vehicle::with('type')->withTrashed()->find($request->vehicle_id);

        $hiddenSlug = 'next_service_inspection';
        if ($vehicle->type->service_interval_type == 'Time') {
            $hiddenSlug = 'next_service_inspection_distance';
        }

        $allEvents = MaintenanceEvents::with('maintenanceHistory')
                                        //->whereIn('is_standard_event',[0,1])
                                        ->where('is_standard_event', 0)
                                        ->where('slug','!=',$hiddenSlug)
                                        ->orderBy('name')
                                        ->get();

        $tBody = '';
        $options = '<option></option>';

        foreach ($allEvents as $event) {
            if ($event->id == $eventId) {
                $options .= '<option value="'.$event->id.'" data-slug="'.$event->slug.'" selected>'.$event->name.'</option>';
            } else {
                $options .= '<option value="'.$event->id.'" data-slug="'.$event->slug.'">'.$event->name.'</option>';
            }

            if ($event->is_standard_event == 0) {
                $tBody .= '<tr id="'.$event->id.'">
                    <td><span class="editable-wrapper">
                        <a href="#" class="maintenance_event_name editable editable-click" data-type="text" data-pk="'.$event->id.'" data-value="'.$event->name.'" >'.$event->name.'</a>
                    </span>
                </td>';

                $isDisabled = ($event->maintenanceHistory->count() > 0) ? 'disabled' : '';
                $tBody .='<td class="text-center" >
                    <a href = "#" class="btn btn-xs grey-gallery edit-timesheet tras_btn maintenanceDltBtn '.$isDisabled.'" data-id="'.$event->id.'">
                    <i class="jv-icon jv-dustbin icon-big" ></i >
                    </a >
                </td >';
            } else {
                $tBody .= '<tr id="'.$event->id.'">
                    <td><span class="editable-wrapper">
                        <a href="#">'.$event->name.'</a>
                    </span>
                </td>';
                $tBody .='<td>&nbsp;</td>';
            }

            $tBody .='</tr>';
        }

        return [
            'options' => $options,
            'tBody' => $tBody
        ];

    }
    public function getAllMaitenanceEvent(Request $request,$eventId=0) {

        $vehicle = Vehicle::with('type')->withTrashed()->find($request->vehicle_id);

        $hiddenSlug = 'next_service_inspection';
        if ($vehicle->type->service_interval_type == 'Time') {
            $hiddenSlug = 'next_service_inspection_distance';
        }

        $allEvents = MaintenanceEvents::with('maintenanceHistory')
                                        ->whereIn('is_standard_event',[0,1])
                                        // ->where('is_standard_event', 0)
                                        ->where('slug','!=',$hiddenSlug)
                                        ->orderBy('name')
                                        ->get();

        $tBody = '';
        $options = '<option></option>';

        foreach ($allEvents as $event) {
            if ($event->id == $eventId) {
                $options .= '<option value="'.$event->id.'" data-slug="'.$event->slug.'" selected>'.$event->name.'</option>';
            } else {
                $options .= '<option value="'.$event->id.'" data-slug="'.$event->slug.'">'.$event->name.'</option>';
            }

            if ($event->is_standard_event == 0) {
                $tBody .= '<tr id="'.$event->id.'">
                    <td><span class="editable-wrapper">
                        <a href="#" class="maintenance_event_name editable editable-click" data-type="text" data-pk="'.$event->id.'" data-value="'.$event->name.'" >'.$event->name.'</a>
                    </span>
                </td>';

                $isDisabled = ($event->maintenanceHistory->count() > 0) ? 'disabled' : '';
                $tBody .='<td class="text-center" >
                    <a href = "#" class="btn btn-xs grey-gallery edit-timesheet tras_btn maintenanceDltBtn '.$isDisabled.'" data-id="'.$event->id.'">
                    <i class="jv-icon jv-dustbin icon-big" ></i >
                    </a >
                </td >';
            } else {
                $tBody .= '<tr id="'.$event->id.'">
                    <td><span class="editable-wrapper">
                        <a href="#">'.$event->name.'</a>
                    </span>
                </td>';
                $tBody .='<td>&nbsp;</td>';
            }

            $tBody .='</tr>';
        }

        return [
            'options' => $options,
            'tBody' => $tBody
        ];

    }

    public function updateEventName(Request $request) {
        $maintenanceEvent = MaintenanceEvents::where('id',$request->pk)->first();

        if($maintenanceEvent) {
            $maintenanceEvent->name = $request->value;
            $maintenanceEvent->save();

            return [
                'status' => true,
                'dataAll' =>$this->getAllMaitenanceEvent($request,$request->eventId),
                'data' =>$this->getAddedMaitenanceEvent($request,$request->eventId)
            ];
        } else {
            return [
                'status' => false,
                'msg' => 'Something went wrong! Please try again.'
            ];
        }
    }

    public function deleteEvent(Request $request) {
        $maintenanceEvent = MaintenanceEvents::with('maintenanceHistory')
                                            ->where('id',$request->id)
                                            ->first();

        if (isset($maintenanceEvent) && $maintenanceEvent->maintenanceHistory->count() == 0) {
            $maintenanceEvent->delete();
            return [
                'status' => true,
                'dataAll' =>$this->getAllMaitenanceEvent($request,$request->eventId),
                'data' =>$this->getAddedMaitenanceEvent($request,$request->eventId)
            ];
        } else {
            return [
                'status' => false,
                'msg' => 'Something went wrong! Please try again.'
            ];
        }
    }

    public function getPlanningTable($id) {
        $vehicle = Vehicle::with('type', 'creator', 'updater', 'location', 'repair_location','division','region',
            'nominatedDriver')->withTrashed()->findOrFail($id);
        $currentDate = Carbon::now();

        $maintenanceEvents = MaintenanceEvents::where('is_standard_event',1)->get();

        $vehicleMaintenancehistory = VehicleMaintenanceHistory::with('eventType')->where('vehicle_id',$id)
            ->whereIn('event_type_id',$maintenanceEvents->pluck('id')->toArray())
            ->orderBy('event_date','DESC')->get();

        $vehicleMaintenancehistory = $vehicleMaintenancehistory->groupBy('event_type_id');

        $maintenanceHistory = [];

        foreach ($maintenanceEvents as $event) {

            if (isset($vehicleMaintenancehistory[$event->id]))
                $maintenanceHistory[$event->slug] = $vehicleMaintenancehistory[$event->id]->first();
        }

        $maintenanceHistory = (object)$maintenanceHistory;


        $vehicleMaintenancehistory = $vehicleMaintenancehistory->groupBy('event_type_id');

        if ($vehicle->type->service_interval_type == 'Distance') {
            $distanceEvent = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();
            $vehicleNextDistanceEvent = VehicleMaintenanceHistory::with('eventType')->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Incomplete')
                ->where('event_planned_distance', $vehicle->next_service_inspection_distance)
                ->orderBy('event_plan_date', 'DESC')->orderBy('event_date', 'DESC')->first();

            $vehicleCompletedNextDistanceEvent = VehicleMaintenanceHistory::with('eventType')
                ->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Complete')
                // ->orderBy('event_plan_date', 'DESC')
                ->orderBy('event_date', 'DESC')
                ->orderBy('id', 'desc')
                ->first();

            $isDistanceBanIcon = false;

            $lastServiceDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);

            $vehicleCompletedNextDistanceEventCheckIcon = VehicleMaintenanceHistory::with('eventType')
                ->where('vehicle_id', $id)
                ->where('event_type_id', $distanceEvent->id)
                ->where('event_status', 'Incomplete')
                ->where('event_planned_distance', $lastServiceDistance)
                // ->orderBy('event_plan_date', 'DESC')
                ->orderBy('event_date', 'DESC')
                ->orderBy('id', 'desc')
                ->first();

            if ($vehicleCompletedNextDistanceEventCheckIcon) {
                $isDistanceBanIcon = true;
            }

        } else {
            $vehicleNextDistanceEvent = null;
            $vehicleCompletedNextDistanceEvent = null;
            $isDistanceBanIcon = false;
        }

        $isFirstPmiEventComplete = false;

        $pmiEventId = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();
        $firstPMIEvent = VehicleMaintenanceHistory::where('event_type_id',$pmiEventId->id)
            ->where('event_plan_date',Carbon::parse($vehicle->first_pmi_date)->format('Y-m-d'))
            ->where('vehicle_id',$vehicle->id)
            ->where('event_status','Complete')
            ->first();

        if ($firstPMIEvent) {
            $isFirstPmiEventComplete = true;
        }

        return view('_partials.vehicles.show_planning_table',compact(
            'vehicle',
            'currentDate',
            'vehicleMaintenancehistory',
            'maintenanceHistory',
            'vehicleCompletedNextDistanceEvent',
            'vehicleNextDistanceEvent',
            'isDistanceBanIcon',
            'vehicleCompletedNextDistanceEventCheckIcon',
            'isFirstPmiEventComplete'
        ));
    }

    public function planningDataYearly($year) {
        $dates =  $this->vehicleService ->getYearDatesArray($year);
    }

    public function getVehicleDocsList($id)
    {
        $vehicle = Vehicle::find($id);
        $docList = $this->getVehicleDocs($vehicle);
        return $docList;
    }

    public function getVehicleDocs($vehicle)
    {
        $files = $vehicle->getMedia();
        $maintenanceHistory = $vehicle->maintenanceHistories;
        $fileCollection = collect([]);
        $fileCollection = $fileCollection->merge($files);
        foreach($maintenanceHistory as $history) {
            $fileCollection = $fileCollection->merge($history->getMedia());
        }

        $docList = [];
        $i=0;
        foreach ($fileCollection->sortByDesc('created_at') as $key => $value) {
            $docList[$i]['id'] =  $value->id;
            if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                $docList[$i]['text'] = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            else {
                $docList[$i]['text'] = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
            }
            $i++;
        }

        return $docList;
    }

    public function fetchVehicleByRegistrationNo($registration)
    {
        return Vehicle::where('registration', $registration)->first();
    }
}
