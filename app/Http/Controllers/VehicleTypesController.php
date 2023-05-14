<?php

namespace App\Http\Controllers;

use Input;
use App\Http\Requests;
use App\Models\VehicleType;
use App\Models\ColumnManagements;
use App\Models\Vehicle;
use App\Models\Settings;
use App\Models\DefectMaster;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Custom\Helper\Common;
use App\Custom\Helper\P11dReportHelper;
use App\Models\DefectMasterVehicleTypes;
use App\Repositories\VehicleTypesRepository;
use App\Custom\Facades\GridEncoder;
use View;
use JavaScript;
use Carbon\Carbon;
use App\Jobs\CheckProfileServiceInterval;
use App\Jobs\CheckProfileServiceIntercalForDistance;
use App\Models\VehicleArchiveHistory;


class VehicleTypesController extends Controller
{
    public $title= 'Vehicle Profile';

    public function __construct() {
        View::share ( 'title', $this->title );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = [
            'groupOp' => 'AND',
            'rules' => [['field' => 'vehicle_types.deleted_at', 'op' => 'eq', 'data' => NULL]],
        ];
        if ($request->has('show')) {
            $filters = $this->getVehicleFilters($request->get('show'));
        }

        $vehicleCategoryList = ['' => '','hgv' => 'HGV', 'non-hgv' => 'Non-HGV'];
        $vehicleSubCategoriesNonHGV = config('config-variables.vehicleSubCategoriesNonHGV');
        // Odometer setting
        $vehicleTypeOdometerSetting = config('config-variables.vehicle_type_odometer_setting');

        $fuelTypeList = config('config-variables.fuelTypeList');
        $engineTypeList = config('config-variables.engineTypeList');
        $oilGrade = $this->getOilGradeData();
        $vehicleProfileType = VehicleType::select('vehicle_type as id', 'vehicle_type as text')
                             ->withTrashed()->get(); 

        $vehicleTypeProfilesAll = VehicleType::withTrashed()->select('vehicle_type as id', 
                                                           'vehicle_type as text')->get();
        $vehicleTypeProfiles = VehicleType::select('vehicle_type as id', 
                                                        'vehicle_type as text')->get(); 


        $column_management = ColumnManagements::where('user_id',$request->user()->id)
        ->where('section','vehicleProfile')
        ->select('data')
        ->first();

        $p11dReportHelper = new P11dReportHelper();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);

        $currentYearValue = substr($currTaxYearParts[1], 2);
        $currentYearFormat = $currTaxYearParts[0] . '-' . $currentYearValue;

        JavaScript::put([
            'filters' => $filters,
            'vehicleProfileType' => $vehicleProfileType,
            'vehicleTypeProfiles' => $vehicleTypeProfiles,
            'vehicleTypeProfilesAll' => $vehicleTypeProfilesAll,
            'column_management' => $column_management,
            'vehicleSubCategoriesNonHGV' => $vehicleSubCategoriesNonHGV,
            'vehicleTypeOdometerSetting' => $vehicleTypeOdometerSetting,
            'currentYearFormat' => $currentYearFormat,
        ]);

        return view('vehicleTypes.index')
        ->with('searchEmail', 'test@abc.com')
        ->with('vehicleCategoryList', $vehicleCategoryList)
        ->with('vehicleSubCategoriesNonHGV', $vehicleSubCategoriesNonHGV)
        ->with('fuelTypeList', $fuelTypeList)
        ->with('engineTypeList', $engineTypeList)
        ->with('oilGrade', $oilGrade);
    }

    public function anyData()
    {
       return GridEncoder::encodeRequestedData(new VehicleTypesRepository(), Input::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $vehicleCategoryList = ['' => '','hgv' => 'HGV', 'non-hgv' => 'Non-HGV'];
        $vehicleSubCategoriesNonHGV = config('config-variables.vehicleSubCategoriesNonHGV');
        // $fuelTypeList = ['' => 'Select','Diesel' => 'Diesel', 'Unleaded petrol' => 'Unleaded petrol'];

        if(config('branding.name') == "skanska" || config('branding.name') == "mgroupservices") {
            $fuelTypeList = ['' => '', 'Generic' => 'Generic'];
            $engineTypeList = ['' => '', 'Generic' => 'Generic'];
        } else {
            $fuelTypeList = config('config-variables.fuelTypeList');
            $engineTypeList = config('config-variables.engineTypeList');
        }
        $oilGrade = $this->getOilGradeData();

        // sorting engine type list alphabetically
        array_shift($engineTypeList);
        asort($engineTypeList);
        $engineTypeList = [""=>""] + $engineTypeList;

        if(config('branding.name') == "icl" || config('branding.name') == "ferns") {
            $fuelTypeList = array_merge($fuelTypeList, ['NA' => 'NA']);
            $engineTypeList = array_merge($engineTypeList, ['NA' => 'NA']);
        }

        $vehicleProfileType = VehicleType::select('vehicle_type as id', 'vehicle_type as text')
                             ->withTrashed()->get(); 
        $usageTypeList = config('config-variables.usageType');

        $associatedMediaList = array();
        $associatedMediaList['Front View'] = (object)['for'=>'frontview'];
        $associatedMediaList['Back View'] = (object)['for'=>'backview'];
        $associatedMediaList['Left View'] = (object)['for'=>'leftview'];
        $associatedMediaList['Right View'] = (object)['for'=>'rightview'];

        //$defectMasterList = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->get()->toArray();

        $onlyOnDefectsPage = explode(',',env('SKIP_DEFECT'));
        $removeDefects = explode(',',env('REMOVE_DEFECT'));
        $trailerDefects = explode(',', env('TRAILER_QUESTIONS_ORDER'));
        $defectMasterQuery = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereNotIn('order',$onlyOnDefectsPage);
        if (env('REMOVE_DEFECT')) {
            $defectMasterQuery->whereNotIn('order', $removeDefects);
        }
        if(env('TRAILER_QUESTIONS_ORDER')) {
            $defectMasterQuery->whereNotIn('order', $trailerDefects);
        }

        $defectMasterList = $defectMasterQuery->get()->toArray();

        $defectMasterDefectsOnlyList = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereIn('order',$onlyOnDefectsPage)->whereNotIn('order', $removeDefects)->get()->toArray();

        $engineSizeMandatoryFlag = config('config-variables.engineSizeMandatoryFlag');

        $p11dReportHelper = new P11dReportHelper();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);

        $currentYearValue = substr($currTaxYearParts[1], 2);
        $currentYearFormat = $currTaxYearParts[0] . '-' . $currentYearValue;
        
        $taxYearList = [];
        $nextTaxYearParts = [$currTaxYearParts[0]+1, $currTaxYearParts[1]+1];
        $nextTaxYear = implode('-', $nextTaxYearParts);
        $taxYearListStart = '2017-2018';
        $taxYearListEnd = $nextTaxYear;
        array_push($taxYearList, $taxYearListStart);
        $taxYearListStartParts = explode('-', $taxYearListStart);
        $taxYearListItemParts = [$taxYearListStartParts[0]+1, $taxYearListStartParts[1]+1];
        $taxYearListItem = implode('-', $taxYearListItemParts);
        array_push($taxYearList, $taxYearListItem);
        while (!in_array($taxYearListEnd, $taxYearList)) {
            $taxYearListParts = explode('-', $taxYearListItem);
            $taxYearListItemParts = [$taxYearListParts[0]+1, $taxYearListParts[1]+1];
            $taxYearListItem = implode('-', $taxYearListItemParts);
            array_push($taxYearList, $taxYearListItem);
        }

        rsort($taxYearList);
        // $taxYearList = [""=>""] + $taxYearList;

        JavaScript::put([
            'vehicleProfileType' => $vehicleProfileType,
            'brandName' => env('BRAND_NAME'),
            'currentYearFormat' => $currentYearFormat,
            'serviceInspectionTime' => config('config-variables.serviceInspection')
        ]);
        $fromPage = "add";

        return view('vehicleTypes.create')
            ->with('usageTypeList', $usageTypeList)
            ->with('vehicleCategoryList', $vehicleCategoryList)
            ->with('vehicleSubCategoriesNonHGV', $vehicleSubCategoriesNonHGV)
            ->with('fuelTypeList', $fuelTypeList)
            ->with('engineTypeList', $engineTypeList)
            ->with('engineSizeMandatoryFlag', $engineSizeMandatoryFlag)
            ->with('oilGrade', $oilGrade)
            ->with('defectMasterList', $defectMasterList)
            ->with('defectMasterDefectsOnlyList', $defectMasterDefectsOnlyList)
            ->with('medialist', $associatedMediaList)
            ->with('taxYearList', $taxYearList)
            ->with('currentYearFormat',$currentYearFormat)
            ->with('fromPage',$fromPage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /*public function addVehicleTax(Request $request){
        $dataToSave = $request->all();
        $tax_year_to_add = $dataToSave['tax_year_to_add'];
        $tax_val = $dataToSave['tax_val'];
        //print_r($request->all());exit;
    }*/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->invertor_service_interval == 'none'){
            $request->merge([
                'invertor_service_interval' => null,
            ]);
        }

        if($request->compressor_service_interval == 'none'){
            $request->merge([
                'compressor_service_interval' => null,
            ]);
        }

        if(!is_null($request->vehicle_tax) && $request->vehicle_tax != ''){
            $tempVehicleTax = array_filter(json_decode($request->vehicle_tax));
            foreach ($tempVehicleTax as $key => $value) {
                $tempVehicleTax[$key]->id = $key+1;
            }
            $request->merge(['vehicle_tax' => json_encode($tempVehicleTax)]);
        }

        if($request->pto_service_interval == 'none'){
            $request->merge([
                'pto_service_interval' => null,
            ]);
        }

        if($request->adr_test_date == ''){
            $request->merge([
                'adr_test_date' => null,
            ]);
        }

        if($request->vehicle_subcategory == 'none'){
            $request->merge([
                'vehicle_subcategory' => null,
            ]);
        }

        $dataToSave = $request->all();
        $dataToSave['vehicle_tax'] = '';
        if(isset($request['saveMonthlyCostFlag']) && $request['saveMonthlyCostFlag'] == 1 && !empty($request['monthly_vehicle_tax'])){
            $monthlyVehicleTax = [];
            $editMonthlyVehicleTaxCost = json_decode($request['monthly_vehicle_tax'],true);
            foreach ($editMonthlyVehicleTaxCost as $key => $vehicleTax) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $vehicleTax['cost_value']);
                $finalArray['cost_from_date'] = $vehicleTax['cost_from_date'];
                $finalArray['cost_to_date'] = $vehicleTax['cost_to_date'];    
                $finalArray['cost_continuous'] = isset($vehicleTax['cost_continuous']) && $vehicleTax['cost_continuous'] == 'true' ? 'true' : 'false';
                $finalArray['json_type'] = 'monthlyVehicleTax';
                array_push($monthlyVehicleTax, $finalArray);
                //$maintenanceCostArray[] = $finalArray;
            }
            $dataToSave['vehicle_tax'] = json_encode($monthlyVehicleTax);
        }

        $dataToSave['annual_insurance_cost'] = '';
        if(isset($request['saveInsuranceCostFlag']) && $request['saveInsuranceCostFlag'] == 1 && !empty($request['monthly_vehicle_insurance'])){
            $monthlyInsuranceTax = [];
            $editMonthlyVehicleInsuranceCost = json_decode($request['monthly_vehicle_insurance'],true);
            foreach ($editMonthlyVehicleInsuranceCost as $key => $vehicleTax) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $vehicleTax['cost_value']);
                $finalArray['cost_from_date'] = $vehicleTax['cost_from_date'];
                $finalArray['cost_to_date'] = $vehicleTax['cost_to_date'];    
                $finalArray['cost_continuous'] = isset($vehicleTax['cost_continuous']) && $vehicleTax['cost_continuous'] == 'true' ? 'true' : 'false';
                $finalArray['json_type'] = 'monthlyVehicleInsurance';
                array_push($monthlyInsuranceTax, $finalArray);
            }
            $dataToSave['annual_insurance_cost'] = json_encode($monthlyInsuranceTax);
        }
        
        if($dataToSave['vehicle_tax'] == ''){
            $dataToSave['vehicle_tax'] = null;
        }

        if($dataToSave['annual_insurance_cost'] == ''){
            $dataToSave['annual_insurance_cost'] = null;
        }
        
        if($dataToSave['vehicle_category'] != "non-hgv"){
            $dataToSave['vehicle_subcategory'] = "";
        }
        if ($dataToSave['engine_size'] == '') {
            $dataToSave['engine_size'] = null;
        }

        if($dataToSave['length'] == '') {
            $dataToSave['length'] = null;
        }

        if($dataToSave['width'] == '') {
            $dataToSave['width'] = null;
        }

        if($dataToSave['height'] == '') {
            $dataToSave['height'] = null;
        }

        $this->validate($request, [
            'vehicle_type' => 'required|unique:vehicle_types',
        ]);
        unset($dataToSave['_token']);
        $vehicleType = VehicleType::create($dataToSave);

        if($vehicleType){
                if (!empty($request->file())) {
                    if ($request->file('frontview') != null) {
                        $fileName = $request->file('frontview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        $fileToSave = $request->file('frontview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('frontview')->getMimeType()])
                                            ->toCollectionOnDisk('frontview', 'S3_uploads');
                    }
                    if ($request->file('backview') != null) {
                        $fileName = $request->file('backview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        $fileToSave= $request->file('backview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('backview')->getMimeType()])
                                            ->toCollectionOnDisk('backview', 'S3_uploads');
                    }
                    if ($request->file('leftview') != null) {
                        $fileName = $request->file('leftview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        $fileToSave= $request->file('leftview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('leftview')->getMimeType()])
                                            ->toCollectionOnDisk('leftview', 'S3_uploads');
                    }
                    if ($request->file('rightview') != null) {
                        $fileName = $request->file('rightview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        $fileToSave= $request->file('rightview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('rightview')->getMimeType()])
                                            ->toCollectionOnDisk('rightview', 'S3_uploads');
                    }
                }

                //code to add vehicle type defects
                if ($dataToSave['defects']) {
                    $vehicleTypeDefectList = implode(',', $dataToSave['defects']);
                    //$defectMasterVehicleTypes = DefectMasterVehicleTypes::where('vehicle_type_id',$vehicleType->id)->first();
                    //print_r($defectMasterVehicleTypes);exit;
                    //$defectMasterVehicleTypes->defect_list = $vehicleTypeDefectList;
                    //$defectMasterVehicleTypes->save();
                    \DB::table('defect_master_vehicle_types')->insert(
                        [
                            'vehicle_type_id' => $vehicleType->id,
                            'vehicle_type_name' => $vehicleType->vehicle_type,
                            'defect_list' => $vehicleTypeDefectList
                        ]
                    );
                }
                /*
                $defectList = \DB::table('defect_master')->select(\DB::raw('GROUP_CONCAT(DISTINCT(`order`)) AS defect_list'))->first();
                \DB::table('defect_master_vehicle_types')->insert(
                    [
                        'vehicle_type_id' => $vehicleType->id,
                        'vehicle_type_name' => $vehicleType->vehicle_type,
                        'defect_list' => $defectList->defect_list
                    ]
                );*/
                $this->updateSurveyJson();

            flash()->success(config('config-variables.flashMessages.dataSaved'));

        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('profiles');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$title = 'Vehicle Defects > Details';
        $vehicleType = VehicleType::withTrashed()->findOrFail($id);
        $vehicleCategoryList = ['' => '','hgv' => 'HGV', 'non-hgv' => 'Non-HGV'];
        $vehicleSubCategoriesNonHGV = config('config-variables.vehicleSubCategoriesNonHGV');
        // $fuelTypeList = ['' => 'Select','Diesel' => 'Diesel', 'Unleaded petrol' => 'Unleaded petrol'];
        $fuelTypeList = config('config-variables.fuelTypeList');
        $engineTypeList = config('config-variables.engineTypeList');
        $oilGrade = $this->getOilGradeData();
        // Odometer setting
        $vehicleTypeOdometerSetting = config('config-variables.vehicle_type_odometer_setting');
        $associatedMediaList = $vehicleType->getMediaList();
        $vehicleProfileType = VehicleType::select('vehicle_type as id', 'vehicle_type as text')
                             ->withTrashed()->get();

        $vehicleDefects = DefectMasterVehicleTypes::where('vehicle_type_id',$id)->get()->toArray();
        $vehicleDefectsArray = [];
        if(count($vehicleDefects) > 0) {
            $vehicleDefectsArray = explode(',', $vehicleDefects[0]['defect_list']);
        }
        
        $onlyOnDefectsPage = explode(',',env('SKIP_DEFECT'));
        $removeDefects = explode(',',env('REMOVE_DEFECT'));
        $trailerDefects = explode(',', env('TRAILER_QUESTIONS_ORDER'));
        $defectMasterQuery = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereNotIn('order',$onlyOnDefectsPage);
        if (env('REMOVE_DEFECT') != "") {
            $defectMasterQuery->whereNotIn('order', $removeDefects);
        }
        if(env('TRAILER_QUESTIONS_ORDER')) {
            $defectMasterQuery->whereNotIn('order', $trailerDefects);
        }
        $defectMasterList = $defectMasterQuery->get()->toArray();
        /*$removeDefects = explode(',',env('REMOVE_DEFECT'));
        $defectMasterList = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereNotIn('order',$onlyOnDefectsPage)->whereNotIn('order',$removeDefects)->get()->toArray();*/
        $defectMasterDefectsOnlyList = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereIn('order',$onlyOnDefectsPage)->whereNotIn('order',$removeDefects)->get()->toArray();

        $vehiclesRecord = Vehicle::where('vehicle_type_id',$id)->first();
        if($vehiclesRecord != null) {
            $vehicleDtAddedToFleet = $vehiclesRecord->dt_added_to_fleet;
        } else {
            $vehicleDtAddedToFleet = null;
        }

        $vehicleId = 0;
        $vehicleArchiveHistory = 0;
        if(!empty($vehiclesRecord->id)){
            $vehicleId = $vehiclesRecord->id;
            $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('event_date_time','DESC')->first();
        }

        JavaScript::put([
            'vehicleProfileType' => $vehicleProfileType
        ]);

        $formated_month = Carbon::now()->format("M Y");
        $currentTaxYearValue = 0;

        if(!is_null($vehicleType->vehicle_tax) && $vehicleType->vehicle_tax != ''){
            $vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleType->vehicle_tax,$vehicleId,$vehicleArchiveHistory);
            $currentTaxYearValue = $vehicleTaxCurrentData['currentCost'];
            // $currentDate = $vehicleTaxCurrentData['currentDate'];
        }
        /*if(!empty($vehicleTaxArray)) {
            foreach ($vehicleTaxArray as $key => $value) {
                if($currentYearFormat == $value->tax_year_to_add) {
                    $currentTaxYearValue = number_format($value->tax_val,2) ." (current)";
                } 
            }
        }*/

        $currentInsuranceValue = 0;

        if(!is_null($vehicleType->annual_insurance_cost) && $vehicleType->annual_insurance_cost != ''){
            $vehicleInsuranceData = $this->calcMonthlyCurrentData($vehicleType->annual_insurance_cost,$vehicleId,$vehicleArchiveHistory);
            $currentInsuranceValue = $vehicleInsuranceData['currentCost'];
            // $currentDate = $vehicleTaxCurrentData['currentDate'];
        }

        return view('vehicleTypes.show')
            ->with('medialist',$associatedMediaList)
            ->with('vehicleType',$vehicleType)
            ->with('vehicleCategoryList', $vehicleCategoryList)
            ->with('vehicleSubCategoriesNonHGV', $vehicleSubCategoriesNonHGV)
            ->with('fuelTypeList', $fuelTypeList)
            ->with('vehicleDefectsArray', $vehicleDefectsArray)
            ->with('defectMasterList', $defectMasterList)
            ->with('defectMasterDefectsOnlyList', $defectMasterDefectsOnlyList)
            ->with('engineTypeList', $engineTypeList)
            ->with('oilGrade', $oilGrade)
            ->with('currentTaxYearValue', $currentTaxYearValue)
            ->with('currentInsuranceValue', $currentInsuranceValue)
            ->with('vehicleTypeOdometerSetting', $vehicleTypeOdometerSetting);
        //return view('defects.show', compact('defect', 'comments', 'images', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vehicleType = VehicleType::withTrashed()->findOrFail($id); 

        $vehicleCategoryList = ['' => '','hgv' => 'HGV', 'non-hgv' => 'Non-HGV'];
        $vehicleSubCategoriesNonHGV = config('config-variables.vehicleSubCategoriesNonHGV');

        if(config('branding.name') == "skanska" || config('branding.name') == "mgroupservices") {
            $fuelTypeList = ['' => '', 'Generic' => 'Generic'];
            $engineTypeList = ['' => '', 'Generic' => 'Generic'];
        } else {
            $fuelTypeList = config('config-variables.fuelTypeList');
            $engineTypeList = config('config-variables.engineTypeList');
        }
        $oilGrade = $this->getOilGradeData();
        if(config('branding.name') == "icl" || config('branding.name') == "ferns") {
            $fuelTypeList = array_merge($fuelTypeList, ['NA' => 'NA']);
            $engineTypeList = array_merge($engineTypeList, ['NA' => 'NA']);
        }
        
        if(config('branding.name') != "skanska" && config('branding.name') != "mgroupservices") {
            if ($vehicleType->fuel_type == 'Diesel') {
                $engineTypeList = ['' => '', 'Euro V diesel' => 'Euro V diesel', 'Euro VI diesel (Adblue)' => 'Euro VI diesel (Adblue)'];
            }
            if ($vehicleType->fuel_type == 'EV') {
                $engineTypeList = ['' => '', 'EV' => 'EV'];
            }
            if ($vehicleType->fuel_type == 'Hybrid/Diesel') {
                $engineTypeList = ['' => '', 'Hybrid diesel/EV' => 'Hybrid diesel/EV'];
            }
            if ($vehicleType->fuel_type == 'Hybrid/Petrol') {
                $engineTypeList = ['' => '', 'Hybrid petrol/EV' => 'Hybrid petrol/EV'];
            }
            if ($vehicleType->fuel_type == 'Hybrid/Petrol PHEV') {
                $engineTypeList = ['' => '', 'PHEV petrol/EV' => 'PHEV petrol/EV'];
            }
            if ($vehicleType->fuel_type == 'Unleaded petrol') {
                $engineTypeList = ['' => '', 'Petrol' => 'Petrol'];
            }
            if ($vehicleType->fuel_type == 'NA') {
                $engineTypeList = ['' => '', 'NA' => 'NA'];
            }
        }
        $profileStatus = config('config-variables.profile_status');
        $associatedMediaList = $vehicleType->getMediaList();

        $vehicleStatus = Vehicle::where('vehicle_type_id',$id)->whereNotIn('status',['Archive','Archived - De-commissioned','Archived - Written off'])->count();
        $usageTypeList = config('config-variables.usageType');

        $vehicleDefects = DefectMasterVehicleTypes::where('vehicle_type_id',$id)->get()->toArray();
        $vehicleDefectsArray = [];
        if(count($vehicleDefects) > 0) {
            $vehicleDefectsArray = explode(',', $vehicleDefects[0]['defect_list']);
        }
        $onlyOnDefectsPage = explode(',',env('SKIP_DEFECT'));
        $removeDefects = explode(',',env('REMOVE_DEFECT'));
        $trailerDefects = explode(',', env('TRAILER_QUESTIONS_ORDER'));
        $defectMasterQuery = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereNotIn('order',$onlyOnDefectsPage);
        if (env('REMOVE_DEFECT') != "") {
            $defectMasterQuery->whereNotIn('order', $removeDefects);
        }
        if(env('TRAILER_QUESTIONS_ORDER')) {
            $defectMasterQuery->whereNotIn('order', $trailerDefects);
        }
        $defectMasterList = $defectMasterQuery->get()->toArray();

        $defectMasterDefectsOnlyList = DefectMaster::groupBy('order')->orderBy('order')->select(['order','page_title'])->whereIn('order',$onlyOnDefectsPage)->whereNotIn('order',$removeDefects)->get()->toArray();

        $vehiclesRecord = Vehicle::where('vehicle_type_id',$id)->first();
        if($vehiclesRecord != null) {
            $vehicleDtAddedToFleet = $vehiclesRecord->dt_added_to_fleet;
        } else {
            $vehicleDtAddedToFleet = null;
        }

        $formated_month = Carbon::now()->format("M Y");
        $vehicleId = 0;
        $vehicleArchiveHistory = null;
        if(!empty($vehiclesRecord)){
            $vehicleId = $vehiclesRecord->id;
            $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('event_date_time','DESC')->first();
        }
        $p11dReportHelper = new P11dReportHelper();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);

        $currentYearValue = substr($currTaxYearParts[1], 2);
        $currentYearFormat = $currTaxYearParts[0] . '-' . $currentYearValue;

        $currentMonthVehicleTaxCost = 0;
        $currentMonthVehicleTaxDate = '';
        $currentMonthVehicleTaxDateValue = '';

        $currentMonthVehicleInsuranceCost = 0;
        $currentMonthVehicleInsuranceDate = '';
        $currentMonthVehicleInsuranceDateValue = '';
        
        if ($vehicleType->vehicle_tax != null) {
            $commonHelper = new Common();
            $vehicleTaxCurrentData = $commonHelper->getFleetCostValueForDate($vehicleType->vehicle_tax,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
            $currentMonthVehicleTaxCost = $vehicleTaxCurrentData['currentCost'];
            $currentMonthVehicleTaxDate = $vehicleTaxCurrentData['currentDate'];
        }

        $monthlyVehicleTax = json_decode($vehicleType->vehicle_tax, true);
        if(isset($monthlyVehicleTax)){
            foreach ($monthlyVehicleTax as $fleetCost) {
                $currentDate = Carbon::now();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $currentMonthVehicleTaxDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        if ($vehicleType->annual_insurance_cost != null) {
            $commonHelper = new Common();
            $vehicleInsuranceCurrentData = $commonHelper->getFleetCostValueForDate($vehicleType->annual_insurance_cost,Carbon::now()->format('Y-m-d'),$vehicleArchiveHistory);
            $currentMonthVehicleInsuranceCost = $vehicleInsuranceCurrentData['currentCost'];
            $currentMonthVehicleInsuranceDate = $vehicleInsuranceCurrentData['currentDate'];
        }

        $monthlyVehicleInsurance = json_decode($vehicleType->annual_insurance_cost, true);
        if(isset($monthlyVehicleInsurance) && is_array($monthlyVehicleInsurance)){
            foreach ($monthlyVehicleInsurance as $fleetCost) {
                $currentDate = Carbon::now();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $currentMonthVehicleInsuranceDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        JavaScript::put([
            'status' => $vehicleStatus,
            'brandName' => env('BRAND_NAME'),
            'currentYearFormat' => $currentYearFormat,
            'vehicleTypeId' => $id,
            'fromPage' => 'edit',
            'vehicleType' => $vehicleType,
            'serviceInspectionTime' => config('config-variables.serviceInspection')
        ]);

        $engineSizeMandatoryFlag = config('config-variables.engineSizeMandatoryFlag');
        $fromPage = "edit";

        return view('vehicleTypes.edit')
            ->with('usageTypeList',$usageTypeList)
            ->with('medialist',$associatedMediaList)
            ->with('vehicleType',$vehicleType)
            ->with('vehicleCategoryList', $vehicleCategoryList)
            ->with('vehicleSubCategoriesNonHGV', $vehicleSubCategoriesNonHGV)
            ->with('fuelTypeList', $fuelTypeList)
            ->with('engineTypeList', $engineTypeList)
            ->with('engineSizeMandatoryFlag', $engineSizeMandatoryFlag)
            ->with('oilGrade', $oilGrade)
            ->with('vehicleDefectsArray', $vehicleDefectsArray)
            ->with('defectMasterList', $defectMasterList)
            ->with('defectMasterDefectsOnlyList', $defectMasterDefectsOnlyList)
            ->with('profileStatus',$profileStatus)
            ->with('currentMonthVehicleTaxCost',$currentMonthVehicleTaxCost)
            ->with('currentYearFormat', $currentYearFormat)
            ->with('fromPage',$fromPage)
            ->with('vahicleTaxCurrentDate',$currentMonthVehicleTaxDate)
            ->with('currentMonthVehicleTaxDateValue',$currentMonthVehicleTaxDateValue)
            ->with('currentMonthVehicleInsuranceCost',$currentMonthVehicleInsuranceCost)
            ->with('vahicleInsuranceCurrentDate',$currentMonthVehicleInsuranceDate)
            ->with('currentMonthVehicleInsuranceDateValue',$currentMonthVehicleInsuranceDateValue);
    }

    /**
     * Update the vehicle tax cost of a vehicle type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editVehicleTax(Request $request) 
    {
        $vehicle_type_id = $request['vehicle_type_id'];
        $field = $request['field'];
        $vehicleType = VehicleType::findOrFail($vehicle_type_id);
        $editVehicleTaxCostField = json_decode($field, true);
        $vehicleType->vehicle_tax = $field;

        $vehiclesRecord = Vehicle::where('vehicle_type_id',$vehicle_type_id)->first();
        if($vehiclesRecord != null) {
            $vehicleDtAddedToFleet = $vehiclesRecord->dt_added_to_fleet;
        } else {
            $vehicleDtAddedToFleet = null;

        }

        $vehicleId = 0;
        $vehicleArchiveHistory = 0;
        if(!empty($vehiclesRecord->id)){
            $vehicleId = $vehiclesRecord->id;
            $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('event_date_time','DESC')->first();
        }

        $editVehicleTaxArray = [];
        if($request['field']){
            foreach ($editVehicleTaxCostField as $key => $editVehicleTax) {  
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editVehicleTax['cost_value']);
                $finalArray['cost_from_date'] = $editVehicleTax['cost_from_date'];
                $finalArray['cost_to_date'] = $editVehicleTax['cost_to_date'];
                $finalArray['cost_continuous'] = $editVehicleTax['cost_continuous'];
                $finalArray['json_type'] = 'monthlyVehicleTax';
                $editVehicleTaxArray[] = $finalArray;
            }
        }
        $formated_month = Carbon::now()->format("M Y");
        $editVehicleTaxCostField = $editVehicleTaxArray;
        $vehicleType->vehicle_tax = json_encode($editVehicleTaxCostField);
        $vehicleType->save();

        $currentCost = 0;
        $currentDate = '';
        $currentMonthVehicleTaxDateValue = '';

        //$vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleType->vehicle_tax,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet);
        $vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleType->vehicle_tax,null,null,null);
        $currentCost = $vehicleTaxCurrentData['currentCost'];
        $currentDate = $vehicleTaxCurrentData['currentDate'];
        // $currentMonthVehicleTaxDateValue = $vehicleTaxCurrentData['currentDateValue'];

        $monthlyVehicleTax = json_decode($vehicleType->vehicle_tax, true);
        if(isset($monthlyVehicleTax)){
            foreach ($monthlyVehicleTax as $fleetCost) {
                $currentDate = Carbon::now();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $currentMonthVehicleTaxDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        return view('_partials.vehicle_types.vehicle_tax_history')
            ->with('currentMonthVehicleTaxCost',$currentCost)
            ->with('vehicleType',$vehicleType)
            ->with('vahicleTaxCurrentDate',$currentDate)
            ->with('currentMonthVehicleTaxDateValue',$currentMonthVehicleTaxDateValue);
    }
    private function calcMonthlyCurrentData($costs_json,$vehicleId=null,$vehicleArchiveHistory=null,$vehicleDtAddedToFleet=null){
        $commonHelper = new Common();
        $formated_month = Carbon::now()->format("M Y");
       
      
        // $vehicleId = 0;
        // $vehicleArchiveHistory = 0;
        return $commonHelper->getFleetCostValueForDate($costs_json,Carbon::now()->format('Y-m-d'));

        dd($commonHelper->calcMonthlyCurrentData($costs_json, $formated_month,
            $vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,null,null,$isBasedIncurrentDate=true));
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
        $oldProfile = VehicleType::withTrashed()->find($id);

        if($request->adr_test_date == ''){
            $request->merge([
                'adr_test_date' => null,
            ]);
        }

        if(!is_null($request->vehicle_tax) && $request->vehicle_tax != '' && is_object(json_decode($request->vehicle_tax))){
            $tempVehicleTax = array_filter(json_decode($request->vehicle_tax));
            foreach ($tempVehicleTax as $key => $value) {
                $tempVehicleTax[$key]->id = $key+1;
            }
            $request->merge(['vehicle_tax' => json_encode($tempVehicleTax)]);
        }

        $dataToUpdate = $request->all();

        if ($dataToUpdate['engine_size'] == '') {
            $dataToUpdate['engine_size'] = null;
        }

        if($dataToUpdate['length'] == '') {
            $dataToUpdate['length'] = null;
        }

        if($dataToUpdate['width'] == '') {
            $dataToUpdate['width'] = null;
        }

        if($dataToUpdate['height'] == '') {
            $dataToUpdate['height'] = null;
        }

        //code to add vehicle type defects
        if (isset($dataToUpdate['defects']) && $dataToUpdate['defects']) {
            $vehicleTypeDefectList = implode(',', $dataToUpdate['defects']);
            $defectMasterVehicleTypes = DefectMasterVehicleTypes::where('vehicle_type_id',$id)->first();
            if(!$defectMasterVehicleTypes) {
                \DB::table('defect_master_vehicle_types')->insert(
                    [
                        'vehicle_type_id' => $id,
                        'vehicle_type_name' => $oldProfile->vehicle_type,
                        'defect_list' => $vehicleTypeDefectList
                    ]
                );
            } else {
                $defectMasterVehicleTypes->defect_list = $vehicleTypeDefectList;
                $defectMasterVehicleTypes->save();
            }
        }
        unset($dataToUpdate['defects']);

        if($dataToUpdate['vehicle_category'] != "non-hgv"){
            $dataToUpdate['vehicle_subcategory'] = "";
        }

        if (isset($dataToUpdate['rightview_media_id'])) {
            $rightview_media_id = $dataToUpdate['rightview_media_id'];
            unset($dataToUpdate['rightview_media_id']);
            $rightview_del = $dataToUpdate['rightview_del'];
            unset($dataToUpdate['rightview_del']);
        }
        if (isset($dataToUpdate['rightview'])) {
            $rightviewData = $dataToUpdate['rightview'];
            unset($dataToUpdate['rightview']);
        }

        if (isset($dataToUpdate['leftview_media_id'])) {
            $leftview_media_id = $dataToUpdate['leftview_media_id'];
            unset($dataToUpdate['leftview_media_id']);
            $leftview_del = $dataToUpdate['leftview_del'];
            unset($dataToUpdate['leftview_del']);
        }
        if (isset($dataToUpdate['leftview'])) {
            $leftviewData = $dataToUpdate['leftview'];
            unset($dataToUpdate['leftview']);
        }

        if (isset($dataToUpdate['frontview_media_id'])) { 
            $frontview_media_id = $dataToUpdate['frontview_media_id'];
            unset($dataToUpdate['frontview_media_id']);
            $frontview_del = $dataToUpdate['frontview_del'];
            unset($dataToUpdate['frontview_del']);
        }
        if (isset($dataToUpdate['frontview'])) { 
            $frontviewData = $dataToUpdate['frontview'];
            unset($dataToUpdate['frontview']);
        }

        if (isset($dataToUpdate['backview_media_id'])) {
            $backview_media_id = $dataToUpdate['backview_media_id'];
            unset($dataToUpdate['backview_media_id']);
            $backview_del = $dataToUpdate['backview_del'];
            unset($dataToUpdate['backview_del']);
        }
        if (isset($dataToUpdate['backview'])) {
            $backviewData = $dataToUpdate['backview'];
            unset($dataToUpdate['backview']);
        }
        unset($dataToUpdate['_token']);
        unset($dataToUpdate['_method']);

        if (isset($dataToUpdate['vehicle_type_id'])) {
            $vehicleTypeId = $dataToUpdate['vehicle_type_id'];
            unset($dataToUpdate['vehicle_type_id']);
        }

        if (isset($dataToUpdate['fromPage'])) {
            $fromPage = $dataToUpdate['fromPage'];
            unset($dataToUpdate['fromPage']);
        }

        if (isset($dataToUpdate['vehicle_tax_cost'])) {
            $vehicleTax = $dataToUpdate['vehicle_tax_cost'];
            unset($dataToUpdate['vehicle_tax_cost']);
        }

        if (isset($dataToUpdate['vehicle_insurance_cost'])) {
            $vehicleInsuranceCost = $dataToUpdate['vehicle_insurance_cost'];
            unset($dataToUpdate['vehicle_insurance_cost']);
        }
        if (VehicleType::withTrashed()->where('id', $id)->update($dataToUpdate)) {
            $vehicleType = VehicleType::with('vehicles')->withTrashed()->findOrFail($id);

            if($dataToUpdate['profile_status'] == 'Active') { 
                $vehicleType->deleted_at = null; 
                $vehicleType->save();
            }

            if($dataToUpdate['profile_status'] == 'Archived') {
                $vehicleType->delete();
            }
            
            // Code to remove Image.
            if(isset($frontview_media_id) && isset($frontview_del)  && $frontview_del==2){
                $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                $vehicleTypeMedia->deleteMedia($frontview_media_id);
            }
            if(isset($backview_media_id) && isset($backview_del)  && $backview_del==2){
                $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                $vehicleTypeMedia->deleteMedia($backview_media_id);
            }
            if(isset($leftview_media_id) && isset($leftview_del)  && $leftview_del==2){
                $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                $vehicleTypeMedia->deleteMedia($leftview_media_id);
            }
            if(isset($rightview_media_id) && isset($rightview_del)  && $rightview_del==2){
                $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                $vehicleTypeMedia->deleteMedia($rightview_media_id);
            }

            if (!empty($request->file())) {
                    if ($request->file('frontview') != null) {
                        $fileName = $request->file('frontview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        if(isset($frontview_media_id)){
                            $vehicleTypeMedia->deleteMedia($frontview_media_id);
                        }
                        $fileToSave= $request->file('frontview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('frontview')->getMimeType()])
                                            ->toCollectionOnDisk('frontview', 'S3_uploads');
                    }
                    if ($request->file('backview') != null) {
                        $fileName = $request->file('backview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        if(isset($backview_media_id)){
                            $vehicleTypeMedia->deleteMedia($backview_media_id);
                        }
                        $fileToSave= $request->file('backview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('backview')->getMimeType()])
                                            ->toCollectionOnDisk('backview', 'S3_uploads');
                    }
                    if ($request->file('leftview') != null) {
                        $fileName = $request->file('leftview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        if(isset($leftview_media_id)){
                            $vehicleTypeMedia->deleteMedia($leftview_media_id);
                        }
                        $fileToSave= $request->file('leftview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('leftview')->getMimeType()])
                                            ->toCollectionOnDisk('leftview', 'S3_uploads');
                    }
                    if ($request->file('rightview') != null) {
                        $fileName = $request->file('rightview')->getClientOriginalName();
                        $customFileName = preg_replace('/\s+/', '_', $fileName);
                        $vehicleTypeMedia = VehicleType::findOrFail($vehicleType->id);
                        if(isset($rightview_media_id)){
                            $vehicleTypeMedia->deleteMedia($rightview_media_id);
                        }
                        $fileToSave= $request->file('rightview')->getRealPath();
                        $vehicleTypeMedia->addMedia($fileToSave)
                                            ->setFileName($customFileName)
                                            ->withCustomProperties(['mime-type' => $request->file('rightview')->getMimeType()])
                                            ->toCollectionOnDisk('rightview', 'S3_uploads');
                    }
            }

            if ($dataToUpdate['service_interval_type'] == 'Distance') {
                $vehicleType->vehicles()->update(['dt_next_service_inspection' => NULL]);
            } else {
                $vehicleType->vehicles()->update(['next_service_inspection_distance' => NULL]);
            }

            $this->updateSurveyJson();
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        if($request->service_interval_type == 'Time' && $oldProfile->service_inspection_interval != $request->service_inspection_interval) {
            dispatch(new CheckProfileServiceInterval('next_service_inspection', $oldProfile->id));
        }

        if($request->service_interval_type == 'Distance' && $oldProfile->service_inspection_interval != $request->service_inspection_interval) {
            dispatch(new CheckProfileServiceIntercalForDistance('next_service_inspection_distance', $oldProfile->id));
        }        

        if($oldProfile->pto_service_interval != $request->pto_service_interval) {
            dispatch(new CheckProfileServiceInterval('pto_service_inspection', $oldProfile->id));
        }

        if($oldProfile->pmi_interval != $request->pmi_interval) {
            dispatch(new CheckProfileServiceInterval('preventative_maintenance_inspection', $oldProfile->id));
        }

        if($oldProfile->invertor_service_interval != $request->invertor_service_interval) {
            dispatch(new CheckProfileServiceInterval('invertor_inspection', $oldProfile->id));
        }

        if($oldProfile->compressor_service_interval != $request->compressor_service_interval) {
            dispatch(new CheckProfileServiceInterval('compressor_inspection', $oldProfile->id));
        }

        if($oldProfile->loler_test_interval != $request->loler_test_interval) {
            dispatch(new CheckProfileServiceInterval('loler_test', $oldProfile->id));
        }

        if($oldProfile->tank_test_interval != $request->tank_test_interval) {
            dispatch(new CheckProfileServiceInterval('tank_test', $oldProfile->id));
        }

        $pmiInterval = $dataToUpdate['pmi_interval'];

        if ($pmiInterval) {
            //NextPmiDate Interval Update
            $vehicleData = Vehicle::where('vehicle_type_id', $id)->get();

            foreach ($vehicleData as $key => $vehicle) {
                $firstPmiDate = $vehicle->first_pmi_date;
                $currentDate = Carbon::now()->format('d M Y');

                if ($vehicle->first_pmi_date && $vehicle->first_pmi_date != '') {
                    while (strtotime($firstPmiDate) < strtotime($currentDate)) {
                        $firstPmiDate = date("d M Y", strtotime($pmiInterval, strtotime($firstPmiDate)));
                    }
                }
                $vehicle->next_pmi_date = $firstPmiDate;
                $vehicle->save();
            }
        }

        return redirect()->to('profiles/'.$id);
    }

    protected function getVehicleFilters($show)
    {
        $filters = [
            'groupOp' => 'OR',
            'rules' => [],
        ];
        if ($show === 'Active') {
            array_push($filters['rules'], ['field' => 'vehicle_types.profile_status', 'op' => 'eq', 'data' => 'Active']);
        }
        
        return $filters;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(VehicleType::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('profiles');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    /*protected function validator(array $data)
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
            'password' => 'required|confirmed|min:6',
        ]);
    }*/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkUniqueType(Request $request)
    {
        $data = $request->all();

        if ($data['vehicle_type'] !== null && !empty($data['vehicle_type'])) {
            $vehicleType = $data['vehicle_type'];
            $vehicle = VehicleType::where('vehicle_type', $vehicleType)->where('id',$data[''])->get();
            if (!$vehicle->isEmpty()) {
                return "false";
            }            
        }
        return "true";  
    }

    /*public function calcSettingsHmrc(Request $request)
    {
        $data = $request->all();
        $fuel_type = $data['fuel_type'];
        $co2 = $data['co2'];

        $hmrcco2data = Settings::where('key', 'like', 'hmrc_co2_%')->orderBy('key', 'desc')->first();

        $hmrcdata = json_decode($hmrcco2data->value);
        $selectCo2Type = 'co2_per_electric_petrol';
        if($fuel_type == 'Diesel' || $fuel_type == 'Hybrid/Diesel'){
            $selectCo2Type = 'co2_per_diesel';
        }

        foreach ($hmrcdata->co2_values as $co2_obj) {
            //$key_parts = explode('_', $hmrc_obj->key);
            $co2_array = json_decode($co2_obj);
            //print_r($co2_obj);exit;
            if (strpos($co2_array->co2_emission, '-') !== false) {
                $co2Emisionrange = explode('-', $co2_array->co2_emission);
                if($co2 >= trim($co2Emisionrange[0]) && $co2 <= trim($co2Emisionrange[1])){
                    return $co2_array->$selectCo2Type;
                }
            }
            else{
                //this case comes when instead of range value is 'Misc' or 'etc' or something like that
                return $co2_array[$selectCo2Type];

            }
            

            
        }
        return "0.00";  
    }*/

    private function updateSurveyJson()
    {
        // Start : Code to update Survey Json & Survey Json Version
        $survey_json_version = \DB::table('survey_json_version')->first();
        \DB::table('survey_json_version')->update(['version' => $survey_json_version->version + 1]);

        $exitCode = \Artisan::call('db:seed', [
            '--class' => 'SurveyMasterTableSeeder',
            '--no-interaction' => true,
            '--force' => true
        ]);
        // End : Code to update Survey Json & Survey Json Version
    }

    public function saveAnnualVehicleTaxListingField(Request $request)
    {
        $vehicleType = VehicleType::where('id',$request->vehicleTypeId)->first();

        $vehicleListingRequestFieldName = $request->field;
        $vehicleListingRequestJson = $request->json;
        if(isset($vehicleListingRequestFieldName) && $vehicleListingRequestFieldName != ''){
            $vehicleListingFieldCost = $vehicleListingRequestJson;
            $vehicleType->$vehicleListingRequestFieldName = $vehicleListingFieldCost;
            $vehicleType->save();
        }
        return $vehicleListingRequestFieldName;
    }

    public function getOilGradeData()
    {
        if(config('branding.name') == "skanska" || config('branding.name') == "mgroupservices") {
            $oilGrade = ['' => '', 'Generic' => 'Generic'];
        } else if(config('branding.name') == "servicemetals") {
            // $oilGrade = VehicleType::select('oil_grade')->distinct()->withTrashed()->get()->pluck('oil_grade', 'oil_grade')->toArray();
            // $oilGrade = ['' => ''] + $oilGrade;
            $oilGrade = ['' => '', '5/30 fully synthetic' => '5/30 fully synthetic', '10/40 fully synthetic low ash' => '10/40 fully synthetic low ash'];
        } else {
            $oilGrade = ['' => '','0W 30' => '0W 30', '5W 30' => '5W 30'];
        }

        return $oilGrade;
    }

    /**
     * Update the vehicle tax cost of a vehicle type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editVehicleInsurance(Request $request) 
    {
        $vehicle_type_id = $request['vehicle_type_id'];
        $field = $request['field'];
        $vehicleType = VehicleType::findOrFail($vehicle_type_id);
        $editVehicleTaxCostField = json_decode($field, true);
        $vehicleType->annual_insurance_cost = $field;

        $vehiclesRecord = Vehicle::where('vehicle_type_id',$vehicle_type_id)->first();
        if($vehiclesRecord != null) {
            $vehicleDtAddedToFleet = $vehiclesRecord->dt_added_to_fleet;
        } else {
            $vehicleDtAddedToFleet = null;

        }

        $vehicleId = 0;
        $vehicleArchiveHistory = 0;
        if(!empty($vehiclesRecord->id)){
            $vehicleId = $vehiclesRecord->id;
            $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('event_date_time','DESC')->first();
        }

        $editVehicleTaxArray = [];
        if($request['field']){
            foreach ($editVehicleTaxCostField as $key => $editVehicleTax) {  
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editVehicleTax['cost_value']);
                $finalArray['cost_from_date'] = $editVehicleTax['cost_from_date'];
                $finalArray['cost_to_date'] = $editVehicleTax['cost_to_date'];
                $finalArray['cost_continuous'] = $editVehicleTax['cost_continuous'];
                $finalArray['json_type'] = 'monthlyVehicleTax';
                $editVehicleTaxArray[] = $finalArray;
            }
        }
        $formated_month = Carbon::now()->format("M Y");
        $editVehicleTaxCostField = $editVehicleTaxArray;
        $vehicleType->annual_insurance_cost = json_encode($editVehicleTaxCostField);
        $vehicleType->save();

        $currentCost = 0;
        $currentDate = '';
        $currentMonthVehicleInsuranceDateValue = '';

        $vehicleTaxCurrentData = $this->calcMonthlyCurrentData($vehicleType->annual_insurance_cost,null,null,null);
        $currentCost = $vehicleTaxCurrentData['currentCost'];
        $currentDate = $vehicleTaxCurrentData['currentDate'];
        $monthlyVehicleTax = json_decode($vehicleType->annual_insurance_cost, true);

        if(isset($monthlyVehicleTax)){
            foreach ($monthlyVehicleTax as $fleetCost) {
                $currentDate = Carbon::now();
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date']);
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date']);
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $currentMonthVehicleInsuranceDateValue = $fleetCost['cost_from_date'];
                }
            }
        }

        return view('_partials.vehicle_types.vehicle_insurance_history')
            ->with('currentMonthVehicleInsuranceCost',$currentCost)
            ->with('vehicleType',$vehicleType)
            ->with('vahicleTaxCurrentDate',$currentDate)
            ->with('currentMonthVehicleInsuranceDateValue',$currentMonthVehicleInsuranceDateValue);
    }

    public function updatedVehicleInsuranceData(Request $request, $id)
    {
        $insuranceValueDisplay = '';
        $vehicleType = VehicleType::findOrFail($id);
        if(isset($request->page) && $request->page == 'vehicles') {
            $template = '_partials.vehicles.vehicle_insurance_details';
            $insuranceValue = $vehicleType->annual_insurance_cost;
            $insuranceValueDisplay = json_decode($insuranceValue, true);
            $finalInsuranceValueDisplay = [];
            if($insuranceValueDisplay){
                foreach ($insuranceValueDisplay as $row) {
                    //TODO: we need to rewrite code for below if condition
                    if ($row['cost_to_date'] != '') {
                        // Do nothing
                    } else {
                        array_push($finalInsuranceValueDisplay, $row);
                    }
                }
            }

            $insuranceValueDisplay = $finalInsuranceValueDisplay;
        } else {
            $template = '_partials.vehicle_types.vehicle_insurance_details';
        }
        return view($template)
            ->with('vehicleType',$vehicleType)
            ->with('insuranceValueDisplay',$insuranceValueDisplay)
            ->with('fromPage', 'edit');
    }
}
