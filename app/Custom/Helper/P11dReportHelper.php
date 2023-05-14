<?php
namespace App\Custom\Helper;

use Mail;
use Exception;
use Carbon\Carbon as Carbon;
use App\Models\P11dReport;
use App\Models\Settings;
use App\Models\User;
use App\Custom\Helper\Common;
use App\Models\VehicleUsageHistory;
use App\Models\PrivateUseLogs;

class P11dReportHelper {
    
    function calcTaxYear(){
        //$today = Carbon::today();
        $currentyear = date_format(Carbon::now(),"Y");
        $newTaxYearDate = Carbon::parse('06-04-'.$currentyear);//new tax year
        if(Carbon::parse($newTaxYearDate)->gt(Carbon::now())){
            return ($currentyear-1).'-'.$currentyear;
        }
        else{
            return $currentyear.'-'.($currentyear+1);
        }
    }
    function calcTaxPrevYear(){
        //$today = Carbon::today();
        $currentyear = date_format(Carbon::now(),"Y");
        $newTaxYearDate = Carbon::parse('06-04-'.$currentyear);//new tax year
        if(Carbon::parse($newTaxYearDate)->gt(Carbon::now())){
            return ($currentyear-2).'-'.($currentyear-1);
        }
        else{
            return ($currentyear-1).'-'.($currentyear);
        }
    }

    function calcPrivateUseDays($user_id, $vehicle_id){
        //$today = Carbon::today();
        //$today = Carbon::today();
        $privateUseLogs = PrivateUseLogs::where(['user_id'=>$user_id, 'vehicle_id'=>$vehicle_id, 'tax_year'=>$this->calcTaxYear()])->get();
        $totalPrivateDays = 0 ;
        foreach ($privateUseLogs as $key => $privateUse) {
            $sdate = $edate = Carbon::now();
            if ($privateUse->start_date != null) {
                $sdate = Carbon::parse($privateUse->start_date);
            }
            if ($privateUse->end_date != null) {
                $edate = Carbon::parse($privateUse->end_date);
            }
            $totalPrivateDays = $totalPrivateDays + $sdate->diffInDays($edate)+1;
        }
        return $totalPrivateDays;
    }
    function calcVehiclePrivateUseDays($vehicle_id){
        //$today = Carbon::today();
        //$today = Carbon::today();
        $privateUseLogs = PrivateUseLogs::where(['vehicle_id'=>$vehicle_id, 'tax_year'=>$this->calcTaxYear()])->get();
        $totalPrivateDays = 0 ;
        foreach ($privateUseLogs as $key => $privateUse) {
            $sdate = $edate = Carbon::now();
            if ($privateUse->start_date != null) {
                $sdate = Carbon::parse($privateUse->start_date);
            }
            if ($privateUse->end_date != null) {
                $edate = Carbon::parse($privateUse->end_date);
            }
            $totalPrivateDays = $totalPrivateDays + $sdate->diffInDays($edate)+1;
        }
        return $totalPrivateDays;
    }
    function calcUserPrivateUseDays($user_id){
        //$today = Carbon::today();
        //$today = Carbon::today();
        $privateUseLogs = PrivateUseLogs::where(['user_id'=>$user_id, 'tax_year'=>$this->calcTaxYear()])->get();
        $totalPrivateDays = 0 ;
        foreach ($privateUseLogs as $key => $privateUse) {
            $sdate = $edate = Carbon::now();
            if ($privateUse->start_date != null) {
                $sdate = Carbon::parse($privateUse->start_date);
            }
            if ($privateUse->end_date != null) {
                $edate = Carbon::parse($privateUse->end_date);
            }
            $totalPrivateDays = $totalPrivateDays + $sdate->diffInDays($edate)+1;
        }
        return $totalPrivateDays;
    }
    function calcUserPrevYearPrivateUseDays($user_id){
        //$today = Carbon::today();
        //$today = Carbon::today();
        $privateUseLogs = PrivateUseLogs::where(['user_id'=>$user_id, 'tax_year'=>$this->calcTaxPrevYear()])->get();
        $totalPrivateDays = 0 ;
        foreach ($privateUseLogs as $key => $privateUse) {
            $sdate = $edate = Carbon::now();
            if ($privateUse->start_date != null) {
                $sdate = Carbon::parse($privateUse->start_date);
            }
            if ($privateUse->end_date != null) {
                $edate = Carbon::parse($privateUse->end_date);
            }
            $totalPrivateDays = $totalPrivateDays + $sdate->diffInDays($edate)+1;
        }
        return $totalPrivateDays;
    }
    function calcHmrcCo2Percentage($fuel_type, $co2, $hmrc_setting_key)
    {
        //$hmrcco2data = Settings::where('key', 'like', 'hmrc_co2_%')->orderBy('key', 'desc')->first();
        $hmrcco2data = Settings::where(['key'=>$hmrc_setting_key])->first();

        if ($hmrcco2data == null) {
            return "0.00";
        }
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
    }
    function generateReport($reportYear, $downloadFlag='yes'){
        $lableArray = [
                    'Full name',
                    'Usage',
                    'Type of fuel used',
                    'Vehicle index',
                    'Make',
                    'Model',
                    'Date first registered',
                    'CO2',
                    'C02 %',
                    'Engine size (cubic capacity )',
                    'Date vehicle was available from',
                    'Date vehicle was no longer available',
                    'Vehicle list price (non-commercial) / Benefit charge (commercial)',
                    'Full benefit cash equivalent',
                    'No. days in tax year',
                    'Prorat\'d BIK based on no. of days (£)',
                    'Private use days in tax year',
                    'Fuel card used during tax year',
                    'Fuel card for personal use (BIK)',
                    'Fuel benefit charge (£)',
                    'Prorat\'d fuel benefit based on no. of days (£)',
                ];
                
        $dataArray = [];
        //$taxYearRange = explode('-', $this->calcTaxYear());
        $taxYearRange = explode('-', $reportYear);
        $taxYearStartDate = Carbon::parse('06-04-'.$taxYearRange[0]);//date($taxYearRange[0].'-04-06');
        $taxYearEndDate = Carbon::parse('05-04-'.$taxYearRange[1]);//date($taxYearRange[1].'-04-05');
        $vehicle_usage_history1 = VehicleUsageHistory::with('vehicle_history')->with('vehicle_history.type')
                         ->whereBetween('from_date', [$taxYearStartDate, $taxYearEndDate])
                         ->orderBy('id','DESC')->get();
        $vehicle_usage_history2 = VehicleUsageHistory::with('vehicle_history')
                                 ->whereBetween('to_date', [$taxYearStartDate, $taxYearEndDate])
                                 ->orderBy('id','DESC')->get();
        $vehicle_usage_history3 = VehicleUsageHistory::with('vehicle_history')
                                ->where('to_date',null)
                                ->where('from_date','<=',$taxYearEndDate)
                                ->orderBy('id','DESC')->get();
        $vehicle_usage_history = $vehicle_usage_history1->merge($vehicle_usage_history2);
        $vehicle_usage_history = $vehicle_usage_history->merge($vehicle_usage_history3);
        //$vehicle_usage_history = $vehicle_usage_history->unique('vehicle_id');
        $users = User::withDisabled()->get();
        foreach ($vehicle_usage_history as $key => $value) {
            $listVehicleFlag = 'false';
            if ($value->vehicle_history->deleted_at != null) {
                $archived_date = $value->vehicle_history->deleted_at;
                /*if(Carbon::parse($taxYearStartDate)->lt(Carbon::parse($archived_date)) && Carbon::parse($taxYearEndDate)->gt(Carbon::parse($archived_date))){
                    $listVehicleFlag = 'true';
                }*/
                if(Carbon::parse($archived_date)->gt(Carbon::parse($taxYearStartDate))){
                    $listVehicleFlag = 'true';
                }
            }
            if ($value->vehicle_history->deleted_at == null || $listVehicleFlag == 'true') {
                $user = $users->where('id', $value->user_id)->first();
                $fuelBenefitCash = 0;
                $fuelBenefitCharge = 0;
                $usageStart = Carbon::parse($taxYearStartDate)->gt(Carbon::parse($value->from_date))? $taxYearStartDate : Carbon::parse($value->from_date);
                if ($reportYear == $this->calcTaxYear()) {
                    $usageEnd = $value->to_date == null ? Carbon::now() : (Carbon::parse($taxYearEndDate)->gt(Carbon::parse($value->to_date))? Carbon::parse($value->to_date) : $taxYearEndDate) ;
                }
                else{
                    $usageEnd = $value->to_date == null ? $taxYearEndDate : (Carbon::parse($taxYearEndDate)->gt(Carbon::parse($value->to_date))? Carbon::parse($value->to_date) : $taxYearEndDate) ;
                }
                $vehicleUsage = $value->vehicle_history->usage_type ? $value->vehicle_history->usage_type : ($value->vehicle_history->type->usage_type ? $value->vehicle_history->type->usage_type : '');

                $usageEnd = explode(' ', $usageEnd)[0];
                $usageStart = explode(' ', $usageStart)[0];
                //$vehicleUsedDays = $usageEnd->diffForHumans($usageStart);
                $vehicleUsedDays = Carbon::parse($usageEnd)->diff(Carbon::parse($usageStart))->days+1;
                
                $vehicleCo2 = $value->vehicle_history->CO2 ? $value->vehicle_history->CO2 : ($value->vehicle_history->type->co2 ? $value->vehicle_history->type->co2 : 0);
                $hmrc_setting_key = 'hmrc_co2_'.$taxYearRange[0].'_'.$taxYearRange[1];//2019_2020
                $vehicleCo2Percentage = $this->calcHmrcCo2Percentage($value->vehicle_history->type->fuel_type, $vehicleCo2, $hmrc_setting_key);

                if ($vehicleUsage == 'Commercial') {
                    $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                    $fuelBenefitCash = $fuel_benefit_commercial->value;
                }
                else{
                    $fuelBenefitCash = ($value->vehicle_history->P11D_list_price * $vehicleCo2Percentage)/100;
                }
                if ($vehicleUsage == 'Commercial') {
                    $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                    $fuelBenefitCharge = $fuel_benefit_commercial->value;

                }
                else{
                    $fuel_benefit_noncommercial = Settings::where('key','fuel_benefit_noncommercial')->first();
                    $fuelBenefitCharge = $fuel_benefit_noncommercial->value;

                }

                $vehiclePrivateUseDays = $this->calcPrivateUseDays($value->user_id, $value->vehicle_id);
                $fuelCardIssued = $user->fuel_card_issued == 1 ? 'Yes' : 'No';
                $fuelCardForPersonalUse = $user->fuel_card_personal_use == 1 ? 'Yes' : 'No';

                $data = [

                    $user->first_name.' '.$user->last_name,
                    // $value->vehicle_history->type->vehicle_type,
                    $vehicleUsage,
                    $value->vehicle_history->type->fuel_type,
                    $value->vehicle_history->registration,
                    $value->vehicle_history->type->manufacturer,
                    $value->vehicle_history->type->model,
                    $value->vehicle_history->dt_registration,
                    $vehicleCo2,
                    $vehicleCo2Percentage,
                    //$value->vehicle_history->type->hmrc_co2 ? $value->vehicle_history->type->hmrc_co2 : 0,
                    $value->vehicle_history->type->engine_size,
                    Carbon::parse($usageStart)->format('d M Y'),
                    Carbon::parse($usageEnd)->format('d M Y'),
                    $value->vehicle_history->P11D_list_price,
                    $fuelBenefitCash,
                    $vehicleUsedDays,
                    number_format((float)(($fuelBenefitCash/365)*$vehicleUsedDays), 2, '.', ''),
                    $vehiclePrivateUseDays,
                    $fuelCardIssued,
                    $fuelCardForPersonalUse,
                    ($fuelCardIssued == 'No' || $vehiclePrivateUseDays == 0) ? 0 : $fuelBenefitCharge,
                    ($fuelCardIssued == 'No' || $vehiclePrivateUseDays == 0) ? 0 : number_format((float)(($fuelBenefitCharge/365)*$vehicleUsedDays), 2, '.', ''),
                ];
                array_push($dataArray, $data);
            }
            
        }

        $excelFileDetail=array(
            'title' => "P11D_Benefits_in_Kind_".$reportYear,
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
        if ($downloadFlag == 'yes') {
            $reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','yes','no');
        }
        else{
            $reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','no','no');
        }
        return $reportFile;
    }
}