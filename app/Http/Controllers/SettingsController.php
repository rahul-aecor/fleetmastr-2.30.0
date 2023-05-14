<?php

namespace App\Http\Controllers;

use Auth;
use View;
use Storage;
use JavaScript;
use Carbon\Carbon;
use App\Models\Settings;
use App\Models\P11dReport;
use Illuminate\Http\Request;
use App\Custom\Helper\Common;
use App\Services\SettingsService;
use App\Custom\Helper\P11dReportHelper;
use App\Models\VehicleUsageHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\StoreConfigurationRequest;

class SettingsController extends Controller
{
    /**
     * The settings service.
     * @var [type]
     */
    protected $service;
    public $title= 'Settings';

    public function __construct(SettingsService $service)
    {
        $this->service = $service;
        View::share ( 'title', $this->title );
    }
    public function index()
    {
        //echo "<pre>"; print_r(Setting::all());  echo "</pre>"; exit;
        $p11dReportHelper = new P11dReportHelper();
        $fuelBenefitData = Settings::whereIN('key',['cash_equivalent','fuel_benefit_noncommercial','fuel_benefit_commercial','android_version','ios_version'])->get()->toArray();
        $fuelBenefitArray = [];
        foreach ($fuelBenefitData as $key => $value) {
            $fuelBenefitArray[$value['key']] = $value['value'];
        }

        $taxYearsAdded = [];
        $hmrcco2data = Settings::where('key', 'like', 'hmrc_co2_%') ->orderBy('key', 'desc')->get();
        $hmrcco2array = [];
        foreach ($hmrcco2data as $hmrc_obj) {
            $key_parts = explode('_', $hmrc_obj->key);
            $hmrcjson = $hmrc_obj->value;
            $hmrcdata = json_decode($hmrcjson);
            array_push($taxYearsAdded, $hmrcdata->year);
            array_push($hmrcco2array, $hmrcdata);
        }
        $showP11dFinalse = 'false';
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);
        $maxFinalisedYear = P11dReport::select('tax_year')->orderBy('tax_year','desc')->first();
        $maxFinalisedYearParts = [];
        if ($maxFinalisedYear != null) {
            $maxFinalisedYear = $maxFinalisedYear->tax_year;
            $maxFinalisedYearParts = explode('-', $maxFinalisedYear);
        }
        $prevTaxYearParts = [$currTaxYearParts[0]-1, $currTaxYearParts[1]-1];
        $prevTaxYear = implode('-', $prevTaxYearParts);

        if ($prevTaxYear != $maxFinalisedYear && Carbon::parse($currTaxYearParts[0].'-04-06')->lt(Carbon::now()) && Carbon::parse($currTaxYearParts[1].'-04-05')->gt(Carbon::now())) {
            $showP11dFinalse = 'true';
        }
        
        //logic to populate HMRCCO2 tax year select list
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
        $taxYearList = [""=>"Select"] + $taxYearList;
        
        $taxYearsFinalised = P11dReport::select('tax_year')->get()->pluck('tax_year')->toArray();

        $accidentInsurance = Settings::where('key', 'accident_insurance_detail')->first();

        $accidentInsuranceMedia = [];
        $accidentInsuranceData = [];
        if(!empty($accidentInsurance)) {
            $accidentInsuranceMedia = $accidentInsurance->getMedia('insurance_certificate_attachment')->first();
            $accidentInsuranceJson = $accidentInsurance->value;
            $accidentInsuranceData = json_decode($accidentInsuranceJson, true);
        }

        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostJson = $fleetCost->value;
        $fleetCostData = json_decode($fleetCostJson, true);


        $insuranceCurrentCost = '';
        $insuranceCurrentDate = '';
        if(isset($fleetCostData['annual_insurance_cost'])){
            foreach ($fleetCostData['annual_insurance_cost'] as $fleetCost) {
                $currentDate = Carbon::now()->format('Y-m-d');
                $annualInsuranceFromDate = Carbon::parse($fleetCost['cost_from_date'])->format('Y-m-d');
                $annualInsuranceToDate = Carbon::parse($fleetCost['cost_to_date'])->format('Y-m-d');
                if($currentDate >= $annualInsuranceFromDate && $currentDate <= $annualInsuranceToDate){
                    $insuranceCurrentCost = $fleetCost['cost_value'];
                    $insuranceCurrentDate = $fleetCost['cost_from_date'];
                }
            }
        }

        $telematicsCurrentCost = '';
        $telematicsCurrentDate = '';
        if(isset($fleetCostData['telematics_insurance_cost'])){
            foreach ($fleetCostData['telematics_insurance_cost'] as $fleetCost) {
                $currentDate = Carbon::now()->format('Y-m-d');
                $telematicsCostFromDate = Carbon::parse($fleetCost['cost_from_date'])->format('Y-m-d');
                $telematicsCostToDate = Carbon::parse($fleetCost['cost_to_date'])->format('Y-m-d');

                if($currentDate >= $telematicsCostFromDate && $currentDate <= $telematicsCostToDate){
                    $telematicsCurrentCost = $fleetCost['cost_value'];
                    $telematicsCurrentDate = $fleetCost['cost_from_date'];
                }
            }
        }

        JavaScript::put([
            'accidentInsuranceMedia' => $accidentInsuranceMedia,
        ]);

        $isConfigurationTabEnabled = 0;
        if (auth()->user()->checkRole('App version handling')) {
            $isConfigurationTabEnabled = 1;
        }

        $isDVSAConfigurationTabEnabled = 0;
        $dvsaSetting = Settings::where('key', 'is_dvsa_enabled')->first();
        if ($dvsaSetting && $dvsaSetting->value == 1) {
            $isDVSAConfigurationTabEnabled = 1;
        }

        $isFleetcostTabEnabled = 0;
        $fleetCostSetting = Settings::where('key', 'is_fleetcost_enabled')->first();
        if ($fleetCostSetting && $fleetCostSetting->value == 1) {
            $isFleetcostTabEnabled = 1;
        }

        $selectedTab = isset($_COOKIE['settings_ref_tab']) ? str_replace("#", "", $_COOKIE['settings_ref_tab']) : 'display_setting';

        return view('settings.index')
            ->with('selectedTab', $selectedTab)
            ->with('fuelBenefitData',$fuelBenefitArray)
            ->with('showP11dFinalse',$showP11dFinalse)
            ->with('hmrcco2data',$hmrcco2array)
            ->with('taxYearList',$taxYearList)
            ->with('taxYearsFinalised',$taxYearsFinalised)
            ->with('taxYearsAdded',$taxYearsAdded)
            ->with('taxyear',$p11dReportHelper->calcTaxYear())
            ->with('evaluationYear',$prevTaxYear)
            ->with('accidentInsuranceMedia', $accidentInsuranceMedia)
            ->with('accidentInsuranceData', $accidentInsuranceData)
            ->with('fleetCostData', $fleetCostData)
            ->with('insuranceCurrentCost', $insuranceCurrentCost)
            ->with('telematicsCurrentCost', $telematicsCurrentCost)
            ->with('insuranceCurrentDate', $insuranceCurrentDate)
            ->with('telematicsCurrentDate', $telematicsCurrentDate)
            ->with('isConfigurationTabEnabled', $isConfigurationTabEnabled)
            ->with('isDVSAConfigurationTabEnabled', $isDVSAConfigurationTabEnabled)
            ->with('isFleetcostTabEnabled', $isFleetcostTabEnabled);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function previewColor(Request $request, $color)
    {
        if(! auth()->user()->isSuperAdmin()) {
            return response()->json([
                'msg' => 'Unauthorized',
            ], 401);
        }
        // Prepare the SASS compiler
    	$scss = new \Leafo\ScssPhp\Compiler;
    	$scss->addImportPath(base_path('resources/assets/sass'));
    	$css = $scss->compile('
		  	@import "partials/variables.scss";
		  	$primary-colour: #' . $color . ';
		  	$primary_close_img: url(../img/remove-icon-blue-small.svg);
			@import "partials/common.scss";
		');

        // Returned the compiled CSS string
    	return response()->json([
    		'css' => $css,
    	]);
    }

    public function uploadLogo(Request $request)
    {
        if(! auth()->user()->isSuperAdmin()) {
            return response()->json([
                'msg' => 'Unauthorized',
            ], 401);
        }

        return $this->service->uploadLogo($request);
    }

    public function fuel_store(Request $request)
    {
        setting([
            'cash_equivalent' => $request->get('cash_equivalent'),
            'fuel_benefit_noncommercial' => $request->get('fuel_benefit_noncommercial'),
            'fuel_benefit_commercial' => $request->get('fuel_benefit_commercial')
        ])->save();
        
        flash()->success(config('config-variables.flashMessages.dataSaved'));
        
        return redirect()->back();
    }

    public function hmrcadd($year)
    {
        ///code to add starts here
        $last_co2_setting = Settings::where('key', 'like', 'hmrc_co2_%')->orderBy('key','desc')->first();
        $new_co2_setting = $last_co2_setting->replicate();
        $user = \Auth::user();
        $hmrcdata = json_decode($new_co2_setting->value);
        $hmrcdata->year = $year;
        $hmrcdata->edited_by = $user->first_name.' '.$user->last_name;
        $hmrcdata->edited_at = date_format(Carbon::now(),"Y-m-d H:i:s");
        $new_co2_setting->key = 'hmrc_co2_'.implode('_', explode('-', $year));
        $new_co2_setting->value = json_encode($hmrcdata);
        $new_co2_setting->save();
        ///code to add ends here

        ///following is code to display reloaded page
        $hmrcco2data = Settings::where('key', 'like', 'hmrc_co2_%') ->orderBy('key', 'desc')->get();
        $hmrcco2array = [];
        foreach ($hmrcco2data as $hmrc_obj) {
            $key_parts = explode('_', $hmrc_obj->key);
            $hmrcjson = $hmrc_obj->value;
            $hmrcdata = json_decode($hmrcjson);
            array_push($hmrcco2array, $hmrcdata);
        }
        $p11dReportHelper = new P11dReportHelper();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);
        
        /////
        $taxYearsFinalised = P11dReport::select('tax_year')->get()->pluck('tax_year')->toArray();

        return view('_partials.settings.hmrcCo2Index')
            ->with('hmrcco2data',$hmrcco2array)
            ->with('taxYearsFinalised',$taxYearsFinalised)
            ->with('taxyear',$p11dReportHelper->calcTaxYear());
        
    }

    public function hmrcedit($year)
    {
        $year = implode('_', explode('-', $year));
        //print_r('hmrc_co2_'.$year);exit;
        $hmrcco2data = Settings::where('key', '=', 'hmrc_co2_'.$year)->get();
        $hmrcco2array = [];
        foreach ($hmrcco2data as $hmrc_obj) {
            $key_parts = explode('_', $hmrc_obj->key);
            $hmrcjson = $hmrc_obj->value;
            $hmrcdata = json_decode($hmrcjson);
            array_push($hmrcco2array, $hmrcdata);
        }
        //print_r($hmrcco2array);exit;
        return view('_partials.settings.hmrcedit')
            ->with('hmrcco2data',$hmrcco2array);
        
    }

    public function hmrcdetail($year)
    {
        $year = implode('_', explode('-', $year));
        $hmrcco2data = Settings::where('key', '=', 'hmrc_co2_'.$year)->get();
        
        $hmrcco2array = [];
        foreach ($hmrcco2data as $hmrc_obj) {
            $key_parts = explode('_', $hmrc_obj->key);
            $hmrcjson = $hmrc_obj->value;
            $hmrcdata = json_decode($hmrcjson);
            array_push($hmrcco2array, $hmrcdata);
        }

        return view('_partials.settings.hmrcdetail')
            ->with('hmrcco2data',$hmrcco2array);
    }

    public function hmrcupdate(Request $request, $year)
    {
        $year = implode('_', explode('-', $year));        
        $hmrcco2data = Settings::where('key', '=', 'hmrc_co2_'.$year)->first();
        $co2_values_count = $request['co2_values_count'];
        $co2value = [];
        for ($index = 0; $index < $co2_values_count; $index++) {
           $co2 = [];
           $co2['co2_emission'] = $request['co2_emission_'.$index];
           $co2['co2_per_electric_petrol'] = $request['co2_per_electric_petrol_'.$index];
           $co2['co2_per_diesel'] = $request['co2_per_diesel_'.$index];
           //$co2['comments'] = '';
           array_push($co2value, json_encode($co2));
        } 
        $user = \Auth::user();
        $hmrcdata = json_decode($hmrcco2data->value);
        $hmrcdata->co2_values = $co2value;
        $hmrcdata->comments = $request['comments'];
        $hmrcdata->edited_by = $user->first_name.' '.$user->last_name;
        $hmrcdata->edited_at = date_format(Carbon::now(),"Y-m-d H:i:s");
        $hmrcco2data->value = json_encode($hmrcdata);

        if ($hmrcco2data->save()) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('settings');
    }

    public function hmrcexportexcel($year)
    {
        $displayYear = $year;
        $year = implode('_', explode('-', $year));
        $hmrcco2data = Settings::where('key', '=', 'hmrc_co2_'.$year)->get();
        
        $hmrcco2array = [];
        foreach ($hmrcco2data as $hmrc_obj) {
            $key_parts = explode('_', $hmrc_obj->key);
            $hmrcjson = $hmrc_obj->value;
            $hmrcdata = json_decode($hmrcjson);
            array_push($hmrcco2array, $hmrcdata);
        }

        $excelFileDetail=array(
            'title' => "HMRC CO2"
        );

        $sheetArray=[];

        $sheet=[];
        $sheet['autofilter'] = 'no';
        $sheet['labelArray'] = [
            'CO2 Emissions in g/km', 'Appropriate Percentage (Electric & Petrol Vehicles)', 'Appropriate Percentage (Diesel Vehicles)'
        ];
        
        $sheet['dataArray'] = [];
        $sheet['columnsToAlign'] = ['A'=>'center', 'B'=>'center', 'C'=>'center'];
        $data=[
            'A' => '',
            'B' => $displayYear,
            'C' => $displayYear,            
        ];
        array_push($sheet['dataArray'], $data);
        
        foreach($hmrcco2array[0]->co2_values as $key => $val){
            $value = json_decode($val); 
            $data=[
                    'co2_emission' => $value->co2_emission,
                    'co2_per_electric_petrol' => $value->co2_per_electric_petrol.'%',
                    'co2_per_diesel' => $value->co2_per_diesel.'%',                    
                ];
            array_push($sheet['dataArray'], $data);
        }
                
        $sheet['otherParams'] = [
            'sheetName' => "HMRC CO2 %"
        ];
        $sheet['columnFormat'] = [];
        //$sheet['columnFormat'] = ['A'=>'\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT','B'=>'\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT','C'=>'\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT'];
        $numberOfColumns = sizeof($sheet['labelArray']);
        $sheet['charts'] = [];
        $chartcounter = 1;
        $sheet['summaryRow'] = [];
        array_push($sheetArray, $sheet);
        $commonHelper = new Common();
        $exportFile= $commonHelper->downloadDesktopExcel($excelFileDetail, $sheetArray, 'xlsx', 'yes');
    } 

    public function store(Request $request)
    {
        // Validate the request
        $this->validate($request, [
            'primary_colour' => 'required',
        ]);

        $requiresCompilation = ($request->get('primary_colour') !== setting('primary_colour'));

        // Update the settings in table.
        $defect_email_notification = 0;
        if ($request->has('defect_email_notification')) {
            $defect_email_notification = 1;
        }
        setting([
            'primary_colour' => $request->get('primary_colour'),
            'logo' => $request->get('image'),
            'defect_email_notification' => $defect_email_notification,
        ])->save();
        
        // Generate the branding css file with the new colour.
        if($requiresCompilation) {
            $this->service->writeBrandingStylesForColour($request->get('primary_colour'));
        }
        
        flash()->success(config('config-variables.flashMessages.dataSaved'));
        
        return redirect()->back();
    }

    public function storeReportFinalize(Request $request)
    {
        $p11dReportHelper = new P11dReportHelper();
        $reportYear = $request->get('evaluationYear');

        if ($request->get('finalize_report_flag') == 'true') {
            $currTaxYear = $p11dReportHelper->calcTaxYear();
            $currTaxYearParts = explode('-', $currTaxYear);
            $prevTaxYearParts = [$currTaxYearParts[0]-1, $currTaxYearParts[1]-1];
            $prevTaxYear = implode('-', $prevTaxYearParts);

            $p11dreport = new P11dReport();
            $p11dreport->freezed_date = Carbon::now();
            $p11dreport->tax_year = $prevTaxYear;

            $reportfile = $p11dReportHelper->generateReport($prevTaxYear, 'no');
            $file_name_temp = "P11D_Benefits_in_Kind_".$prevTaxYear;
            $filename=strtolower(str_replace(" ","-",$file_name_temp));
            Storage::disk('S3_uploads')->put('p11dReports/'.$filename.'.xlsx', file_get_contents($reportfile));
            unlink($reportfile);
            $url = config('filesystems.disks.S3_uploads.domain') . '/p11dReports/'.$filename.'.xlsx';

            $p11dreport->url = $url;
            $p11dreport->save();

        }
    }
    public function storeNotification(Request $request)
    {
        $defect_email_notification = 0;
        if ($request->get('defect_email_notification') == 'true') {
            $defect_email_notification = 1;
        }

        setting([
            'defect_email_notification' => $defect_email_notification,
        ])->save();
    }

    /*public function generateReport($reportYear){
        $lableArray = [
                    'Full Name',
                    'Type of Vehicle',
                    'Type of Fuel used',
                    'Vehicle Index',
                    'Make',
                    'Model',
                    'Date first registered',
                    'CO2',
                    'C02 %',
                    'Engine Size (Cubic Capacity )',
                    'Date vehicle was available from',
                    'Date vehicle was no longer available',
                    'Vehicle List Price (Non-commercial) / Benefit charge (Commercial)',
                    'FULL Benefit / Cash Eqiv',
                    'No. Days in tax year',
                    'Prorat\'d BIK Based on no. of days (£)',
                    'Private use days in tax year',
                    'Fuel card used during tax year',
                    'Fuel Benefit Charge (£)',
                    'Prorat\'d Fuel Benefit Based on no. of days (£)',
                ];
                
                $dataArray = [];
                //$taxYearRange = explode('-', $this->calcTaxYear());
                $taxYearRange = explode('-', $reportYear);
                $taxYearStartDate = Carbon::parse('06-04-'.$taxYearRange[0]);//date($taxYearRange[0].'-04-06');
                $taxYearEndDate = Carbon::parse('05-04-'.$taxYearRange[1]);//date($taxYearRange[1].'-04-05');
                $vehicle_usage_history1 = VehicleUsageHistory::with('vehicle_history')->with('user')->with('vehicle_history.type')
                                 ->whereBetween('from_date', [$taxYearStartDate, $taxYearEndDate])
                                 ->orderBy('id','DESC')->get();
                $vehicle_usage_history2 = VehicleUsageHistory::with('vehicle_history')->with('user')
                                         ->whereBetween('to_date', [$taxYearStartDate, $taxYearEndDate])
                                         ->orderBy('id','DESC')->get();
                $vehicle_usage_history = $vehicle_usage_history1->merge($vehicle_usage_history2);
                //$vehicle_usage_history = $vehicle_usage_history->unique('vehicle_id');
                foreach ($vehicle_usage_history as $key => $value) {
                    $fuelBenefitCash = 0;
                    $fuelBenefitCharge = 0;
                    $usageStart = Carbon::parse($taxYearStartDate)->gt(Carbon::parse($value->from_date))? $taxYearStartDate : Carbon::parse($value->from_date);
                    $usageEnd = $value->to_date == null ? Carbon::now() : (Carbon::parse($taxYearEndDate)->gt(Carbon::parse($value->to_date))? Carbon::parse($value->to_date) : $taxYearEndDate) ;
                    $vehicleUsage = $value->vehicle_history->usage_type ? $value->vehicle_history->usage_type : ($value->vehicle_history->type->usage_type ? $value->vehicle_history->type->usage_type : '');
                    //$vehicleUsedDays = $usageEnd->diffForHumans($usageStart);
                    $vehicleUsedDays = $usageEnd->diff($usageStart)->days;
                    if ($vehicleUsage == 'Commercial') {
                        $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                        $fuelBenefitCash = $fuel_benefit_commercial->value;
                    }
                    else{
                        $fuelBenefitCash = $value->vehicle_history->P11D_list_price * $value->vehicle_history->type->hmrc_co2;
                    }
                    if ($vehicleUsage == 'Commercial') {
                        $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                        $fuelBenefitCharge = $fuel_benefit_commercial->value;

                    }
                    else{
                        $fuel_benefit_noncommercial = Settings::where('key','fuel_benefit_noncommercial')->first();
                        $fuelBenefitCharge = $fuel_benefit_noncommercial->value;

                    }
                    $data = [

                        $value->user->first_name.' '.$value->user->last_name,
                        $value->vehicle_history->type->vehicle_type,
                        $value->vehicle_history->type->fuel_type,
                        $value->vehicle_history->registration,
                        $value->vehicle_history->type->manufacturer,
                        $value->vehicle_history->type->model,
                        $value->vehicle_history->dt_registration,
                        $value->vehicle_history->CO2 ? $value->vehicle_history->CO2 : ($value->vehicle_history->type->CO2 ? $value->vehicle_history->type->CO2 : 0),
                        $value->vehicle_history->type->hmrc_co2 ? $value->vehicle_history->type->hmrc_co2 : 0,
                        $value->vehicle_history->type->engine_size,
                        Carbon::parse($usageStart)->format('d M Y'),
                        Carbon::parse($usageEnd)->format('d M Y'),
                        $value->vehicle_history->P11D_list_price,
                        $fuelBenefitCash,
                        $vehicleUsedDays,
                        number_format((float)(($fuelBenefitCash/365)*$vehicleUsedDays), 2, '.', ''),
                        $value->vehicle_history->calcPrivateUseDays(),
                        $value->user->fuel_card_issued == 1 ? 'Yes' : 'No',
                        $fuelBenefitCharge,
                        number_format((float)(($fuelBenefitCharge/365)*$vehicleUsedDays), 2, '.', ''),
                    ];
                    array_push($dataArray, $data);
                }

                $excelFileDetail=array(
                    'title' => "P11D Benefits in Kind ".$reportYear,
                    );

                $sheetArray=array();
                $sheet=array();
                $sheet['otherParams'] = [
                    'sheetName' => "P11D Report"
                ];
                $sheet['labelArray'] = $lableArray;
                $sheet['dataArray'] = $dataArray;
                $sheet['columnFormat'] = [];
                $sheet['charts'] = [];
                $sheet['summaryRow'] = [];
                array_push($sheetArray, $sheet);
                $commonHelper = new Common();
                $reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','no');
                return $reportFile;
    }*/

    public function storeAccidentInsuranceDetail(Request $request)
    {
        $accidentInsurance = Settings::where('key', 'accident_insurance_detail')->first();

        if(!empty($request->file())) {
            $fileName = $request->file('insurance_certificate_attachment')->getClientOriginalName();
            if(!empty($request->insurance_file_input_name)) {
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $customFileName = $request->insurance_file_input_name . "." . $ext;
            }
            $customFileName = preg_replace("/[^a-z0-9\_\-\.]/i", '-', $customFileName);
            $customFileName = trim(preg_replace('/-+/', '-', $customFileName), '-');
            $fileToSave= $request->file('insurance_certificate_attachment')->getRealPath();

            $accidentInsurance->clearMediaCollection('insurance_certificate_attachment');
            $accidentInsurance->addMedia($fileToSave)
                                ->setFileName($customFileName)
                                ->withCustomProperties(['mime-type' => $request->file('insurance_certificate_attachment')->getMimeType()])
                                ->toCollectionOnDisk('insurance_certificate_attachment', 'S3_uploads');
        }

        if(empty($request->file()) && $request['is_certificate_deleted']) {
            $accidentInsurance->clearMediaCollection('insurance_certificate_attachment');
        }

        $media = $accidentInsurance->getMedia('insurance_certificate_attachment')->first();
        $mediaS3Url = isset($media) ? $media->getUrl() : "";

        $accidentInsuranceDetailArray = [
            'insurance_company' => isset($request['insurance_company']) ? $request['insurance_company'] : null,
            'telephone_number' => isset($request['telephone_number']) ? $request['telephone_number'] : null,
            'policy_number' => isset($request['policy_number']) ? $request['policy_number'] : null,
            'policy_name' => isset($request['policy_name']) ? $request['policy_name'] : null,
            'insurance_certificate_attachment' => $mediaS3Url,
        ];

        $accidentInsurance->value = json_encode($accidentInsuranceDetailArray);
        $accidentInsurance->save();

        flash()->success(config('config-variables.flashMessages.dataSaved'));
        
        return redirect()->back();
    }

    //Global Fleet cost 
    public function storeFleetCostDetail(Request $request) 
    {    
        $fleetCostDetail = $request['vor_opportunity_cost'];
        $fleetCostData = Settings::where('key', 'fleet_cost_area_detail')->first();

        $fleetCostDataArray = json_decode($fleetCostData->value, true);
        $fleetCostDataArray['vor_opportunity_cost_per_day'] = str_replace(',', '', $fleetCostDetail);
        $fleetCostData->value = json_encode($fleetCostDataArray);
        // $fleetCostData->save();
        if ($fleetCostData->save()) {
             flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect()->back();
    }

    public function editAnnualInsuranceCost(Request $request) {
        $editAnnualInsuranceCost = $request['annualInsurancerepeater'];
        $annualInsuranceArray = [];
        $insuranceData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $insuranceArray = json_decode($insuranceData->value, true);

        if($request['annualInsurancerepeater']){
            foreach ($editAnnualInsuranceCost as $key => $editAnnualInsurance) {
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editAnnualInsurance['edit_annual_insurance_cost']);
                $finalArray['cost_from_date'] = $editAnnualInsurance['edit_annual_insurance_from_date'];
                $finalArray['cost_to_date'] = $editAnnualInsurance['edit_annual_insurance_to_date'];
                $finalArray['cost_continuous'] = isset($editAnnualInsurance['edit_insurance_cost_continuous']) ? 'true' : 'false';
                $annualInsuranceArray[] = $finalArray;
            }
        }

        $insuranceArray['annual_insurance_cost'] = $annualInsuranceArray;
        $insuranceData->value = json_encode($insuranceArray);
        $insuranceData->save();
        return redirect()->back();
    }

    public function editAnnualTelematicsCost(Request $request) {
        $editTelematicsInsuranceCost = $request['telematicsInsurancerepeater'];
        $telematicsInsuranceArray = [];
        $insuranceData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $insuranceArray = json_decode($insuranceData->value, true);
        if($request['telematicsInsurancerepeater']){
            foreach ($editTelematicsInsuranceCost as $key => $editTelematicsInsurance) {  
                $finalArray = [];
                $finalArray['cost_value'] = str_replace(',', '', $editTelematicsInsurance['edit_telematics_insurance_cost']);
                $finalArray['cost_from_date'] = $editTelematicsInsurance['edit_telamatics_from_date'];
                $finalArray['cost_to_date'] = $editTelematicsInsurance['edit_telamatics_to_date'];
                $finalArray['cost_continuous'] = isset($editTelematicsInsurance['edit_telematics_cost_continuous']) ? 'true' : 'false';
                $telematicsInsuranceArray[] = $finalArray;
            }
        }
        $insuranceArray['telematics_insurance_cost'] = $telematicsInsuranceArray;
        $insuranceData->value = json_encode($insuranceArray);
        $insuranceData->save();
        return redirect()->back();
    }

    public function variableCostPerMonth(Request $request)
    {
        $forecastVariableCost = $request['month'];
        foreach ($forecastVariableCost as $key => $variableCost) {
            $forecastVariableCostValue = str_replace(",","", $variableCost);
            $forecastVariableCost[$key] = (isset($forecastVariableCostValue) && $forecastVariableCostValue != '') ? $forecastVariableCostValue : "0";
        }

        $variableCostData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $variableCostArray = json_decode($variableCostData->value, true);

        $variableCostArray['forecast_cost_per_month'] = $forecastVariableCost;
        $variableCostData->value = json_encode($variableCostArray);
        $variableCostData->save();
        return redirect()->back();
    }

    public function fixedCostPerMonth(Request $request)
    {
        $forecastFixedCost = $request['month'];
        foreach ($forecastFixedCost as $key => $fixCostValue) {
            $forecastFixedCostValue = str_replace(",","", $fixCostValue);
            $forecastFixedCost[$key] = (isset($forecastFixedCostValue) && $forecastFixedCostValue != '') ? $forecastFixedCostValue : "0";
        }

        $forecastFixedCostData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fixedCostArray = json_decode($forecastFixedCostData->value, true);

        $fixedCostArray['forecast_fixed_cost_per_month'] = $forecastFixedCost;
        $forecastFixedCostData->value = json_encode($fixedCostArray);
        $forecastFixedCostData->save();
        return redirect()->back();
    }

    public function fleetMilesPerMonth(Request $request)
    {
        $fleetMiles = $request['month'];
        foreach ($fleetMiles as $key => $milesPerMonth) {
            $forecastMilesValue = str_replace(",","", $milesPerMonth);
            $fleetMiles[$key] = (isset($forecastMilesValue) && $forecastMilesValue != '') ? $forecastMilesValue : "0";
        }

        $fleetMilesData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetMilesArray = json_decode($fleetMilesData->value, true);

        $fleetMilesArray['fleet_miles_per_month'] = $fleetMiles;
        $fleetMilesData->value = json_encode($fleetMilesArray);
        $fleetMilesData->save();
        return redirect()->back();
    }

    public function fleetDamageCostPerMonth(Request $request)
    {
        $fleetDamage = $request['month'];
        foreach ($fleetDamage as $key => $damageCostValue) {
            $forecastDamageCostValue = str_replace(",","", $damageCostValue);
            $fleetDamage[$key] = (isset($forecastDamageCostValue) && $forecastDamageCostValue != '') ? $forecastDamageCostValue : "0";
        }

        $fleetDamageData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetDamageForecastArray = json_decode($fleetDamageData->value, true);

        $fleetDamageForecastArray['fleet_damage_cost_per_month'] = $fleetDamage;
        $fleetDamageData->value = json_encode($fleetDamageForecastArray);
        $fleetDamageData->save();
        return redirect()->back();
    }

    public function storeSiteConfiguration(StoreConfigurationRequest $request)
    {
        if ($request->get('is_configuration_tab_enabled') == 1) {
            $existingTrailerCheck = setting('is_trailer_feature_enabled');
            $updatedTrailerCheck = $request->get('is_trailer_feature_enabled') == 'on' ? 1 : 0;
            setting([
                'android_version' => $request->get('android_version'),
                'ios_version' => $request->get('ios_version'),
                'android_update_prompt_message' => $request->get('android_update_prompt_message'),
                'ios_update_prompt_message' => $request->get('ios_update_prompt_message'),
                'show_resolve_defect' => $request->get('show_resolve_defect') == 'on' ? 1 : 0,
                'is_incident_reports_enabled' => $request->get('is_incident_reports_enabled') == 'on' ? 1 : 0,
                'is_trailer_feature_enabled' => $updatedTrailerCheck,
                'is_offline_in_android' => $request->get('is_offline_in_android') == 'on' ? 1 : 0,
                'is_offline_in_ios' => $request->get('is_offline_in_ios') == 'on' ? 1 : 0,
                'is_telematics_enabled' => $request->get('is_telematics_enabled') == 'on' ? 1 : 0,
                'is_alertcentre_enabled' => $request->get('is_alertcentre_enabled') == 'on' ? 1 : 0,
                'is_fleetcost_enabled' => $request->get('is_fleetcost_enabled') == 'on' ? 1 : 0,
                'is_android_testfairy_feedback_enabled' => $request->get('is_android_testfairy_feedback_enabled') == 'on' ? 1 : 0,
                'is_ios_testfairy_feedback_enabled' => $request->get('is_ios_testfairy_feedback_enabled') == 'on' ? 1 : 0,
                'is_android_testfairy_video_capture_enabled' => $request->get('is_android_testfairy_video_capture_enabled') == 'on' ? 1 : 0,
                'is_ios_testfairy_video_capture_enabled' => $request->get('is_ios_testfairy_video_capture_enabled') == 'on' ? 1 : 0,
                'is_dvsa_enabled' => $request->get('is_dvsa_enabled') == 'on' ? 1 : 0
            ])->save();
            if($existingTrailerCheck != $updatedTrailerCheck) {
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
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        }

        return redirect()->back();
    }
    
    public function saveManualCostAdjustmentListing(Request $request)
    {   
        $fleetCostAdjustment = (!empty($request['manual_cost_adjustment'])) ? array_filter(json_decode($request['manual_cost_adjustment'], true)) : [];
        $fleetCostData = Settings::where('key', 'fleet_cost_area_detail')->first();

        $fleetCostDataArray = json_decode($fleetCostData->value, true);
        $fleetCostDataArray['manual_cost_adjustment'] = $fleetCostAdjustment;
        $fleetCostData->value = json_encode($fleetCostDataArray);
        $fleetCostData->save();
        return $fleetCostData;
    }

    public function storeMaintenanceReminderNotification(Request $request)
    {
        $maintenance_reminder_notification = 0;
        if ($request->get('maintenance_reminder_notification') == 'true') {
            $maintenance_reminder_notification = 1;
        }

        setting([
            'maintenance_reminder_notification' => $maintenance_reminder_notification,
        ])->save();
    }

    public function storeDVSAConfiguration(Request $request)
    {
        $isDVSAConfigurationEnabled = Settings::where('key', 'is_dvsa_enabled')->first();
        if ($isDVSAConfigurationEnabled && $isDVSAConfigurationEnabled->value == 1) {
            setting([
                'dvsa_joining_period' => $request->get('dvsa_joining_period'),
                'dvsa_joining_year' => $request->get('dvsa_joining_year'),
                'dvsa_commencement_date' => Carbon::parse($request->get('dvsa_commencement_date'))->format('d M Y'),
                'dvsa_operator_id' => $request->get('dvsa_operator_id'),
            ])->save();

            flash()->success(config('config-variables.flashMessages.dataSaved'));
            return redirect()->back();
        }
    }    
}

