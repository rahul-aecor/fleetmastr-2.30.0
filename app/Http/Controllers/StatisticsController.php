<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;
use Illuminate\Http\Request;

use DB;
use Carbon\Carbon;
use App\Http\Requests;
use App\Models\Check;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\Settings;
use App\Models\VehicleType;
use App\Models\ColumnManagements;
use App\Custom\Helper\Common;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\VehicleArchiveHistory;

class StatisticsController extends Controller
{
    private function calcGivenMonthMilesTraveled($vehicleId, $givenDate,$odoMeterReading = [],$previousMonthsReading = [],$resultSent = 0)
    {

        if ($resultSent == 1) {
            $odometer_readings = $odoMeterReading;
            $startDate  = $givenDate->startOfMonth()->format('Y-m-d');
            $endDate = $givenDate->endOfMonth()->format('Y-m-d');
        } else {

            $startDate  = $givenDate->startOfMonth()->format('Y-m-d');
            $endDate = $givenDate->endOfMonth()->format('Y-m-d');
            $odometer_readings = Check::select('id','odometer_reading','type')
                ->where('vehicle_id',$vehicleId)
                ->whereIn('type',['Vehicle Check','Return Check'])
                ->whereDate('report_datetime', '>=', $startDate)
                ->whereDate('report_datetime', '<=', $endDate)
                ->orderBy('report_datetime')->get();
        }

        $odometer_readings = $odoMeterReading;
        $startDate  = $givenDate->startOfMonth()->format('Y-m-d');
        $endDate = $givenDate->endOfMonth()->format('Y-m-d');

        $miles_traveled = 0;
        if (count($odometer_readings) > 0) {
            if ($odometer_readings->first()['type'] == 'Return Check') {

                if ($resultSent == 1) {
                    $previousMonthsReading = isset($previousMonthsReading[0]) ? $previousMonthsReading[0] : false;
                } else {
                    $previousDate = strtotime($startDate.' -1 month');
                    $previousDate = date('Y-m-d', $previousDate);
                    $previousDate = Carbon::parse($previousDate);
                    $startDateValue = $previousDate->startOfMonth()->format('Y-m-d');
                    $endDateValue = $previousDate->endOfMonth()->format('Y-m-d');
                    $previousMonthsReading = Check::select('odometer_reading','type')
                        ->where('vehicle_id',$vehicleId)
                        ->where('type','=','Vehicle Check')
                        ->whereDate('report_datetime', '>=', $startDateValue)
                        ->whereDate('report_datetime', '<=', $endDateValue)
                        ->orderBy('report_datetime','DESC')->first();
                }
                


                if ($previousMonthsReading) {
                    return  $miles_traveled = $odometer_readings->last()['odometer_reading'] - $previousMonthsReading->odometer_reading;
                } else {
                    return  $miles_traveled = $odometer_readings->last()['odometer_reading'] - $odometer_readings->first()['odometer_reading'];
                }
            }

            if ($odometer_readings->last()['type'] == 'Vehicle Check') {

                /*$secondLastReading = Check::select('id','odometer_reading','type')
                        ->where('vehicle_id',$vehicleId)
                        ->where('type','=','Return Check')
                        ->whereDate('report_datetime', '>=', $startDate)
                        ->whereDate('report_datetime', '<=', $endDate)
                        ->orderBy('report_datetime','DESC')->first();*/

                $secondLastReading = $odometer_readings->where('type','=','Return Check')
                    ->where('report_datetime', '>=', $startDate)
                    ->where('report_datetime', '<=', $endDate)
                    ->sortByDesc('report_datetime')->first();

                if ($secondLastReading) {
                    return  $miles_traveled = $secondLastReading->odometer_reading - $odometer_readings->first()['odometer_reading'];
                } else {
                    return  $miles_traveled = $odometer_readings->last()['odometer_reading'] - $odometer_readings->first()['odometer_reading'];
                }
            }
            return  $miles_traveled = $odometer_readings->last()['odometer_reading'] - $odometer_readings->first()['odometer_reading'];
        }
    }

    public function vehicleFleetCostStats()
    {
        $monthly_fleet_cost = 0;
        $monthly_fleet_miles = 0;
        $monthly_fleet_cost_per_mile = 0;
        $currentMonth = Carbon::now()->format("M Y");
        $monthly_defect_cost = $this->calcGivenMonthDefectCost(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        $vehicles = Vehicle::with(['archiveHistory','type','defects'])->withTrashed()->get();

        $date1 = Carbon::now()->startOfMonth();
        $date2 = Carbon::now()->endOfMonth();

        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostSettingsData = json_decode($fleetCost->value, true);
        if (isset($fleetCostSettingsData['manual_cost_adjustment'])) {
            $commonHelper = new Common();
            $currentMonthManualCostAdjustment = $commonHelper->calcCurrentMonthBasedOnPeriod($fleetCostSettingsData['manual_cost_adjustment'],$currentMonth);
            $monthly_fleet_cost += $currentMonthManualCostAdjustment['currentCost'];

        }

        $startDate  = $date1->copy()->format('Y-m-d');
        $endDate = $date2->copy()->format('Y-m-d');
        $odometer_readings = Check::select('id','vehicle_id','odometer_reading','type')
            //->where('vehicle_id',$vehicleId)
            ->whereIn('type',['Vehicle Check','Return Check'])
            ->whereDate('report_datetime', '>=', $startDate)
            ->whereDate('report_datetime', '<=', $endDate)
            ->orderBy('report_datetime')->get();

        $odometer_readings = $odometer_readings->groupBy('vehicle_id');

        $previousDate = strtotime($startDate.' -1 month');
        $previousDate = date('Y-m-d', $previousDate);
        $previousDate = Carbon::parse($previousDate);
        $startDateValue = $previousDate->startOfMonth()->format('Y-m-d');
        $endDateValue = $previousDate->endOfMonth()->format('Y-m-d');
        $previousMonthsReading = Check::select('vehicle_id','odometer_reading','type')
            //->where('vehicle_id',$vehicleId)
            ->where('type','=','Vehicle Check')
            ->whereDate('report_datetime', '>=', $startDateValue)
            ->whereDate('report_datetime', '<=', $endDateValue)
            ->orderBy('report_datetime','DESC')
            ->groupBy('vehicle_id')
            ->get();

        $previousMonthsReading = $previousMonthsReading->groupBy('vehicle_id');
        foreach ($vehicles as $key => $vehicle) {
            //$vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicle->id)->orderBy('event_date_time','DESC')->first();
            $vehicleArchiveHistory = $vehicle->archiveHistory;
            $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
            $isInsuranceCostOverride = $vehicle->is_insurance_cost_override;
            $isTelematicsCostOverride = $vehicle->is_telematics_cost_override;
            $vehicleOdometerReading = isset($odometer_readings[$vehicle->id]) ? $odometer_readings[$vehicle->id] : [];
            $vehiclePrevOdometerReading = ( isset($previousMonthsReading[$vehicle->id]) && isset($previousMonthsReading[$vehicle->id][0]) ) ? $previousMonthsReading[$vehicle->id] : [];
            $monthly_fleet_cost = $monthly_fleet_cost + $vehicle->calcGivenMonthFixedCost($currentMonth,$vehicle->id,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,$fleetCost,$vehicle) + $vehicle->calcGivenMonthVariableCost($currentMonth,$vehicle->id,$vehicleArchiveHistory,$date1,$date2,$fleetCost,$vehicle);
            $miles_traveled = $this->calcGivenMonthMilesTraveled($vehicle->id, Carbon::now()->startOfMonth(),$vehicleOdometerReading,$vehiclePrevOdometerReading,1);


            if($vehicle->odometer_reading_unit == 'km'){
                $miles_traveled * 0.621371;
            }
           $monthly_fleet_miles += $miles_traveled;
        }

        if ($monthly_fleet_miles != 0) {
            $monthly_fleet_cost_per_mile = $monthly_fleet_cost/$monthly_fleet_miles;
        }
        $monthly_fleet_cost_per_mile = number_format($monthly_fleet_cost_per_mile,2);
        $monthly_fleet_cost = number_format($monthly_fleet_cost,2);
        $monthly_fleet_miles = number_format($monthly_fleet_miles);
        $monthly_defect_cost = number_format($monthly_defect_cost,2);
        return compact('monthly_fleet_cost', 'monthly_fleet_miles', 'monthly_fleet_cost_per_mile', 'monthly_defect_cost');
    }

    private function calcGivenMonthDefectCost($givenDate1, $givenDate2){
        $startOfMonth = $givenDate1->startOfMonth()->format('Y-m-d');
        $endOfMonth = $givenDate2->endOfMonth()->format('Y-m-d');

        $defects = DB::table('defects')
            ->select('actual_defect_cost_value')->where('report_datetime', '>=', $startOfMonth)
            ->where('report_datetime', '<=', $endOfMonth)
            ->where('status','Resolved')->get();

        $totalDefectCost = 0;
        foreach ($defects as $defect) {
            if ($defect->actual_defect_cost_value != null && is_numeric($defect->actual_defect_cost_value)) {
                $totalDefectCost = $totalDefectCost + $defect->actual_defect_cost_value;
            }
        }

        return $totalDefectCost;
    }

    public function vehicleFleetCostChartStats(Request $request) {
        //print_r($request->all());exit;
        //[montharray] => Array ( [0] => 2019-11-01 [1] => 2019-12-01 [2] => 2020-01-01 [3] => 2020-02-01 [4] => 2020-03-01 )
        // save period for dashboard fleet costs
        $data['from_date'] = $request->get('from_date');
        $data['to_date'] = $request->get('to_date');
        $user_id = Auth::id();
        $columnManagementData = ColumnManagements::where('user_id', $user_id)->where('section','dashboardFleetCost')->first();
        if(empty($columnManagementData)) {
            $columnManagement = new ColumnManagements();
        } else {
            $columnManagement = ColumnManagements::findOrFail($columnManagementData['id']);
        }

        $columnManagement->user_id = Auth::id();
        $columnManagement->section = 'dashboardFleetCost';
        $columnManagement->data = $data;
        $columnManagement->save();

        $montharray = $request->get('montharray');
        $vehicles = Vehicle::with('archiveHistory','type','defects')->withTrashed()->get();

        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostSettingsData = json_decode($fleetCost->value, true);

        $monthly_variable_fleet_cost = [];
        $monthly_fixed_fleet_cost = [];
        $monthly_forecast_variable_fleet_cost = [];
        $monthly_forecast_fixed_fleet_cost = [];

        $cummulative_fleet_cost = [];
        $cummulative_forecast_fleet_cost = [];

        $monthly_defect_actual_cost = [];
        $monthly_defect_forecast_cost = [];
        $cummulative_defect_actual_cost = [];
        $cummulative_defect_forecast_cost = [];
        $cummulative_defect_actual_cost_unformatted = [];

        $variable_costs_per_mile = [];
        $fixed_costs_per_mile = [];
        $total_costs_per_mile = [];
        $forecast_variable_costs_per_mile = [];
        $forecast_fixed_costs_per_mile = [];
        $forecast_total_costs_per_mile = [];

        $forecast_monthly_fleet_miles = [];
        $monthly_fleet_miles = [];
        $monthly_forecast_miles = [];
        $current_month = Carbon::now()->month;
        $currentMonthDispalyValue = Carbon::now();
        $current_year = Carbon::now()->year;
        $startDate = $montharray[0];
        $endDate = $montharray[count($montharray) -1];


        $odometerReadingsAll = Check::selectRaw('id,vehicle_id,odometer_reading,type,DATE_FORMAT(report_datetime,"%m-%Y") as month')
            //->where('vehicle_id',$vehicleId)
            ->whereIn('type',['Vehicle Check','Return Check'])
            ->whereDate('report_datetime', '>=', $startDate)
            ->whereDate('report_datetime', '<=', $endDate)
            ->orderBy('report_datetime')->get();

        $odometerReadingsAll = $odometerReadingsAll->groupBy('month');

        $defectsCost = Defect::selectRaw("DATE_FORMAT(report_datetime,'%m-%Y') as month, sum(actual_defect_cost_value) as actual_defect_cost_value")
            ->whereDate('report_datetime', '>=', $startDate)
            ->whereDate('report_datetime', '<=', $endDate)
            ->where('status','Resolved')
            ->groupBy(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(report_datetime,'%m-%Y')"))->get();

        $defectsCost = $defectsCost->groupBy('month');

        foreach ($montharray as $month) {
            $formated_month = Carbon::createFromFormat('Y-m-d', $month)->format("M Y");
            $numeric_month = Carbon::createFromFormat('Y-m-d', $month)->month;
            $numericMonthDisplayValue = Carbon::createFromFormat('Y-m-d', $month);
            $numeric_year = Carbon::createFromFormat('Y-m-d', $month)->year;
            $date1 = Carbon::createFromFormat('Y-m-d', $month);

             $startDate  = $date1->startOfMonth()->format('Y-m-d');
             $endDate = $date1->endOfMonth()->format('Y-m-d');

            $startDateMonthYear  = $date1->startOfMonth()->format('m-Y');
            $monthDate = $startDateMonthYear;
            /*$odometer_readings = Check::select('id','vehicle_id','odometer_reading','type')
                //->where('vehicle_id',$vehicleId)
                ->whereIn('type',['Vehicle Check','Return Check'])
                ->whereDate('report_datetime', '>=', $startDate)
                ->whereDate('report_datetime', '<=', $endDate)
                ->orderBy('report_datetime')->get();

            $odometer_readings = $odometer_readings->groupBy('vehicle_id');*/

            if (isset($odometerReadingsAll[$monthDate])) {
                $odometer_readings = $odometerReadingsAll[$monthDate]->groupBy('vehicle_id');
            } else {
                $odometer_readings = [];
            }

            $preMonth = $date1->subMonth(1)->format('m-Y');
            $previousDate = strtotime($startDate.' -1 month');
            $previousDate = date('Y-m-d', $previousDate);
            $previousDate = Carbon::parse($previousDate);
            $startDateValue = $previousDate->startOfMonth()->format('Y-m-d');
            $endDateValue = $previousDate->endOfMonth()->format('Y-m-d');


             $previousMonthsReading = Check::select('vehicle_id','odometer_reading','type')
                 //->where('vehicle_id',$vehicleId)
                 ->where('type','=','Vehicle Check')
                 ->whereDate('report_datetime', '>=', $startDateValue)
                 ->whereDate('report_datetime', '<=', $endDateValue)
                 ->orderBy('report_datetime','DESC')
                 ->groupBy('vehicle_id')
                 ->get();

             $previousMonthsReading = $previousMonthsReading->groupBy('vehicle_id');

            $givenMonthVariableCost = 0;
            $givenMonthFixedCost = 0;
            $givenMonthFixedCostUnformatted = 0;
            $givenMonthForecastVariableCost = 0;
            $givenMonthForecastVariableCostUnformatted = 0;
            $givenMonthForecastFixedCost = 0;
            $givenMonthForecastFixedCostUnformatted = 0;
            $givenMonthCummulativeFleetCost = 0;
            $givenMonthCummulativeForecastFleetCost = 0;
            $givenMonthMilesTraveled = 0;
            $givenMonthForecastMilesTraveled = 0;
            if(isset($fleetCostSettingsData['forecast_cost_per_month'])){
                $givenMonthForecastVariableCost = number_format($fleetCostSettingsData['forecast_cost_per_month'][$numeric_month],2,'.','');
                $givenMonthForecastVariableCostUnformatted = $fleetCostSettingsData['forecast_cost_per_month'][$numeric_month];
            }
            if(isset($fleetCostSettingsData['forecast_fixed_cost_per_month'])){
                $givenMonthForecastFixedCost = number_format($fleetCostSettingsData['forecast_fixed_cost_per_month'][$numeric_month],2,'.','');
                $givenMonthForecastFixedCostUnformatted = $fleetCostSettingsData['forecast_fixed_cost_per_month'][$numeric_month];
            }
            foreach ($vehicles as $vehicle) {
                //$vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicle->id)->orderBy('event_date_time','DESC')->first();
                $vehicleOdometerReading = isset($odometer_readings[$vehicle->id]) ? $odometer_readings[$vehicle->id] : [];
                $vehiclePrevOdometerReading = ( isset($previousMonthsReading[$vehicle->id]) && isset($previousMonthsReading[$vehicle->id][0]) ) ? $previousMonthsReading[$vehicle->id] : [];

                $vehicleArchiveHistory = $vehicle->archiveHistory;
                $vehicleDtAddedToFleet = $vehicle->dt_added_to_fleet;
                $isInsuranceCostOverride = $vehicle->is_insurance_cost_override;
                $isTelematicsCostOverride = $vehicle->is_telematics_cost_override;
                $givenMonthVariableCost = number_format($givenMonthVariableCost + $vehicle->calcGivenMonthVariableCost($formated_month,$vehicle->id,$vehicleArchiveHistory,$date1->startOfMonth(), $date1->lastOfMonth(),$fleetCost,$vehicle),2,'.','');

                $givenMonthFixedCostValue = $vehicle->calcGivenMonthFixedCost($formated_month,$vehicle->id,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,$fleetCost,$vehicle);
                $givenMonthFixedCost = number_format($givenMonthFixedCost + $givenMonthFixedCostValue,2,'.','');
                $givenMonthFixedCostUnformatted = $givenMonthFixedCostUnformatted + $givenMonthFixedCostValue;

                $miles_traveled = $this->calcGivenMonthMilesTraveled($vehicle->id, $date1->startOfMonth(),$vehicleOdometerReading,$vehiclePrevOdometerReading,1);

                $givenMonthMilesTraveled = $givenMonthMilesTraveled + $miles_traveled;

            }
            //ManualCost Adjustment Value
            $currentMonthManualCostAdjustment = 0;
            $monthly_fleet_cost = 0;
            $currentMonth = Carbon::now()->format("M Y");
            //$fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
            $fleetCostSettingsData = json_decode($fleetCost->value, true);
            if (isset($fleetCostSettingsData['manual_cost_adjustment'])) {
                $commonHelper = new Common();
                $currentMonthManualCostAdjustment = $commonHelper->calcCurrentMonthBasedOnPeriod($fleetCostSettingsData['manual_cost_adjustment'],$formated_month);
                $monthly_fleet_cost += $currentMonthManualCostAdjustment['currentCost'];
            }

            // if (isset($defectsCost[$monthDate])) {
            //     $defectsCost[$monthDate] = 0;
            // }
            
            if($currentMonthManualCostAdjustment) {
                $givenMonthVariableCost = $givenMonthVariableCost + $monthly_fleet_cost + (isset($defectsCost[$monthDate]) && isset($defectsCost[$monthDate][0]->actual_defect_cost_value) ? $defectsCost[$monthDate][0]->actual_defect_cost_value : 0);
            }

            //#481 - condition here to add 0 to prev months for forecast var and coming months for actual variables
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                //set actual value
                array_push($monthly_variable_fleet_cost, $givenMonthVariableCost);
                array_push($monthly_fixed_fleet_cost, $givenMonthFixedCost);
                array_push($monthly_forecast_variable_fleet_cost, 0 );
                array_push($monthly_forecast_fixed_fleet_cost, 0 );
            }
            else if ($numeric_year < $current_year) {
                //set actual value
                array_push($monthly_variable_fleet_cost, $givenMonthVariableCost);
                array_push($monthly_fixed_fleet_cost, $givenMonthFixedCost);
                array_push($monthly_forecast_variable_fleet_cost, 0 );
                array_push($monthly_forecast_fixed_fleet_cost, 0 );
            } else {
                //set forecast value
                array_push($monthly_variable_fleet_cost, 0);
                array_push($monthly_fixed_fleet_cost, 0);
                array_push($monthly_forecast_variable_fleet_cost, $givenMonthForecastVariableCostUnformatted );
                array_push($monthly_forecast_fixed_fleet_cost, $givenMonthForecastFixedCostUnformatted );
            }

            //chart2
            $givenMonthCummulativeForecastFleetCost = number_format(last($cummulative_forecast_fleet_cost) + $givenMonthForecastVariableCostUnformatted + $givenMonthForecastFixedCostUnformatted,2,'.','');
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                //set actual value
                $givenMonthCummulativeFleetCost = number_format(last($cummulative_fleet_cost) + $givenMonthVariableCost + $givenMonthFixedCost,2,'.','');
                array_push($cummulative_fleet_cost, $givenMonthCummulativeFleetCost);
                //array_push($cummulative_forecast_fleet_cost, 0);
            } else if ($numeric_year < $current_year) {
                $givenMonthCummulativeFleetCost = number_format(last($cummulative_fleet_cost) + $givenMonthVariableCost + $givenMonthFixedCost,2,'.','');
                array_push($cummulative_fleet_cost, $givenMonthCummulativeFleetCost);
                //array_push($cummulative_forecast_fleet_cost, 0);
            } //else {
            array_push($cummulative_forecast_fleet_cost, $givenMonthCummulativeForecastFleetCost);
            //}

            //chart3
            //$cummulative_defect_cost_this_month = $this->calcGivenMonthDefectCost($date1->startOfMonth(), $date1->lastOfMonth());
            $cummulative_defect_cost_this_month = isset($defectsCost[$monthDate]) && isset($defectsCost[$monthDate][0]->actual_defect_cost_value) ? $defectsCost[$monthDate][0]->actual_defect_cost_value : 0;
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                array_push($monthly_defect_actual_cost, $cummulative_defect_cost_this_month);
                if(isset($fleetCostSettingsData['fleet_damage_cost_per_month'])) {
                    array_push($monthly_defect_forecast_cost, 0);
                }
            } else if ($numeric_year < $current_year) {
                array_push($monthly_defect_actual_cost, $cummulative_defect_cost_this_month);
                if(isset($fleetCostSettingsData['fleet_damage_cost_per_month'])) {
                    array_push($monthly_defect_forecast_cost, 0);
                }
            } else {
                if(isset($fleetCostSettingsData['fleet_damage_cost_per_month'])) {
                    array_push($monthly_defect_forecast_cost, $fleetCostSettingsData['fleet_damage_cost_per_month'][$numeric_month]);
                }
            }

            //chart4
            $givenMonthCummulativeDefectForecastCost = 0;
            if(isset($fleetCostSettingsData['fleet_damage_cost_per_month'])){
                $givenMonthCummulativeDefectForecastCost = number_format(last($cummulative_defect_forecast_cost) + $fleetCostSettingsData['fleet_damage_cost_per_month'][$numeric_month],2,'.','');
            }
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                //set actual value
                $givenMonthCummulativeDefectActualCost = last($cummulative_defect_actual_cost_unformatted) + $cummulative_defect_cost_this_month;
                array_push($cummulative_defect_actual_cost, $givenMonthCummulativeDefectActualCost);
                //array_push($cummulative_defect_forecast_cost, 0);
            } else if ($numeric_year < $current_year) {
                $givenMonthCummulativeDefectActualCost = last($cummulative_defect_actual_cost_unformatted) + $cummulative_defect_cost_this_month;
                array_push($cummulative_defect_actual_cost, $givenMonthCummulativeDefectActualCost);
                //array_push($cummulative_defect_forecast_cost, 0);
            } //else {
            array_push($cummulative_defect_forecast_cost, $givenMonthCummulativeDefectForecastCost);
            //}

            //chart5
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                if ($givenMonthMilesTraveled != 0) {
                    array_push($variable_costs_per_mile, $givenMonthVariableCost/$givenMonthMilesTraveled);
                    array_push($fixed_costs_per_mile, $givenMonthFixedCost/$givenMonthMilesTraveled);
                    array_push($total_costs_per_mile, ($givenMonthFixedCost + $givenMonthVariableCost) /$givenMonthMilesTraveled);
                    array_push($forecast_variable_costs_per_mile, 0);
                    array_push($forecast_fixed_costs_per_mile, 0);
                    //Note : we are adding actual_total_costs_per_mile to forecast_total_costs_per_mile so that forecast line joins actual cost line after current month as per discussion
                    array_push($forecast_total_costs_per_mile, 0);
                }
                else{
                    array_push($variable_costs_per_mile, 0);
                    array_push($fixed_costs_per_mile, 0);
                    array_push($total_costs_per_mile, 0);
                    array_push($forecast_variable_costs_per_mile, 0);
                    array_push($forecast_fixed_costs_per_mile, 0);
                    array_push($forecast_total_costs_per_mile, 0);
                }
            } else if ($numeric_year < $current_year) {
                if ($givenMonthMilesTraveled != 0) {
                    array_push($variable_costs_per_mile, $givenMonthVariableCost/$givenMonthMilesTraveled);
                    array_push($fixed_costs_per_mile, $givenMonthFixedCost/$givenMonthMilesTraveled);
                    array_push($total_costs_per_mile, ($givenMonthFixedCost + $givenMonthVariableCost) /$givenMonthMilesTraveled);
                    array_push($forecast_variable_costs_per_mile, 0);
                    array_push($forecast_fixed_costs_per_mile, 0);
                    //Note : we are adding actual_total_costs_per_mile to forecast_total_costs_per_mile so that forecast line joins actual cost line after current month as per discussion
                    array_push($forecast_total_costs_per_mile, 0);
                }
                else{
                    array_push($variable_costs_per_mile, 0);
                    array_push($fixed_costs_per_mile, 0);
                    array_push($total_costs_per_mile, 0);
                    array_push($forecast_variable_costs_per_mile, 0);
                    array_push($forecast_fixed_costs_per_mile, 0);
                    array_push($forecast_total_costs_per_mile, 0);
                }
            }
            else{
                if (isset($fleetCostSettingsData['fleet_miles_per_month']) && $fleetCostSettingsData['fleet_miles_per_month'][$numeric_month] != 0) {
                    $givenMonthForecastMilesTraveled = $fleetCostSettingsData['fleet_miles_per_month'][$numeric_month];
                    array_push($forecast_variable_costs_per_mile, $givenMonthForecastVariableCostUnformatted/$givenMonthForecastMilesTraveled);

                    array_push($forecast_fixed_costs_per_mile, $givenMonthForecastFixedCostUnformatted/$givenMonthForecastMilesTraveled);
                    array_push($forecast_total_costs_per_mile, ($givenMonthForecastFixedCostUnformatted + $givenMonthForecastVariableCostUnformatted) /$givenMonthForecastMilesTraveled);

                }
                else{
                    array_push($forecast_variable_costs_per_mile, 0);
                    array_push($forecast_fixed_costs_per_mile, 0);
                    array_push($forecast_total_costs_per_mile, 0);
                }
            }

            //chart6
            if ($numeric_month < $current_month && $numeric_year == $current_year) {
                //set actual value
                array_push($monthly_fleet_miles, $givenMonthMilesTraveled);
                /*if(isset($fleetCostSettingsData['fleet_miles_per_month'])){
                    array_push($monthly_forecast_miles, 0);
                }*/
            } else if ($numeric_year < $current_year) {
                array_push($monthly_fleet_miles, $givenMonthMilesTraveled);
                /*if(isset($fleetCostSettingsData['fleet_miles_per_month'])){
                    array_push($monthly_forecast_miles, 0);
                }*/
            } //else {
            if(isset($fleetCostSettingsData['fleet_miles_per_month'])){
                array_push($monthly_forecast_miles, $fleetCostSettingsData['fleet_miles_per_month'][$numeric_month]);
            }
            //}
        }

        if(!empty($cummulative_defect_actual_cost)){
            foreach ($cummulative_defect_actual_cost as $key => $value) {
                if($key > 0){
                    $cummulative_defect_actual_cost[$key] = $cummulative_defect_actual_cost[$key] + $cummulative_defect_actual_cost[$key - 1];
                }
            }
        }

        return compact('monthly_variable_fleet_cost', 'monthly_forecast_variable_fleet_cost', 'monthly_fixed_fleet_cost', 'monthly_forecast_fixed_fleet_cost', 'cummulative_fleet_cost', 'cummulative_forecast_fleet_cost', 'monthly_defect_actual_cost', 'monthly_defect_forecast_cost', 'cummulative_defect_actual_cost', 'cummulative_defect_forecast_cost', 'variable_costs_per_mile', 'fixed_costs_per_mile', 'total_costs_per_mile', 'monthly_fleet_miles', 'monthly_forecast_miles','forecast_variable_costs_per_mile','forecast_fixed_costs_per_mile','forecast_total_costs_per_mile');
    }

    public function vehicleFleetStats() {
        // get total vehicles
        $total_vehicle_count = Vehicle::whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->count();
        // get roadworthy vehicles
        $roadworthy_vehicle_count = Vehicle::whereIn('status', ['Roadworthy','Roadworthy (with defects)'])->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->count();
        // get vehicles with defects
        // $defects_count = Defect::join('vehicles', 'vehicles.id', '=', 'defects.vehicle_id')
        //     ->where('defects.status', '<>', 'Resolved')
        //     ->whereNull('vehicles.deleted_at')
        //     ->groupBy('defects.vehicle_id')
        //     ->get()
        //     ->count();

        // get other vehicles
        $other_vehicle_count = Vehicle::whereIn('status', ['Awaiting kit', 'Re-positioning', 'Other'])->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->count();

        // get vehicles off road
        $vor_vehicle_counts = Vehicle::whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined'])->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->count();
        // get pie chart data
        $pie_data = $this->formatPieChartData([
            'Roadworthy' => $roadworthy_vehicle_count,
            'Other' => $other_vehicle_count,
            'VOR' => $vor_vehicle_counts,
        ]);
        return compact('total_vehicle_count', 'roadworthy_vehicle_count', 'other_vehicle_count', 'vor_vehicle_counts', 'pie_data');
    }

    public function vehicleChecksStats() {
        // get total vehicles
        $total_vehicle_count = Vehicle::whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->count();
        // get percentage vehicle checked today
        $checks_completed_today = Check::whereHas('vehicle', function($q)
                {
                    $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                })
            ->select(array('id'))->whereRaw('DATE(created_at) = CURDATE()')
            ->where('type', '=', 'Vehicle Check')
            ->groupBy('vehicle_id')
            ->get()
            ->count();
        $total_checks_count = 0;
        if($total_vehicle_count > 0){
            $total_checks_count = $checks_completed_today * 100 / $total_vehicle_count;
        }
        $checks_completed_today_with_status = Check::whereHas('vehicle', function($q)
                {
                    $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                })
            ->withoutDefectChecks()
            ->where('type', '=', 'Vehicle Check')
            ->selectRaw('status as label, count(id) as data')
            ->whereRaw('DATE(created_at) = CURDATE()')
            ->groupBy('status')
            ->get()
            ->toArray();
        $emptyData = ['RoadWorthy' => 0, 'SafeToOperate' => 0, 'UnsafeToOperate' => 0];
        $result = $this->formatChecksData($checks_completed_today_with_status, $emptyData);
        $color = [
            'RoadWorthy' => 'green',
            'SafeToOperate' => 'orange',
            'UnsafeToOperate' => 'red'
        ];
        $display = [
            'RoadWorthy' => 'Roadworthy',
            'SafeToOperate' => 'Safe to operate',
            'UnsafeToOperate' => 'Unsafe to operate'
        ];
        foreach ($checks_completed_today_with_status as $key => $value) {
            if(!isset($display[$value['label']]) || !isset($result[$value['label']])) {
                continue;
            }
            if (isset($checks_completed_today_with_status[$key]['color'])) {
                $checks_completed_today_with_status[$key]['color'] = $color[$value['label']];
            }
            $checks_completed_today_with_status[$key]['label'] = $display[$value['label']];
            $checks_completed_today_with_status[$key]['data'] = $result[$value['label']];
            $checks_completed_today_with_status[$key]['color'] = $color[$value['label']];
        }

        $total_unchecks_count = 100 - $total_checks_count;

        return compact('total_checks_count','total_unchecks_count', 'result', 'checks_completed_today_with_status');
    }

    public function vehicleOffroadStats(Request $request, $region) {
        //$regions_to_query = ['all' => 'All'] + Auth::user()->regions->lists('name','id')->toArray();
        //foreach ($regions_to_query as $key => $index) {
            $vor_and_total_data[$region] = $this->getVorVsTotalVehicleData($region);
            $vor_and_total_counts[$region] = $this->getVorVsTotalVehicleCount($region);
        //}
        asort($vor_and_total_data);
        return compact('vor_and_total_data', 'vor_and_total_counts');
    }

    public function fetchUpcomingInspections(Request $request) {
        $region = $request->get('region');
        $upcoming_expires_data[$region]['interval4'] = $this->getUpcomingInspections(-1, -1, $region);
        $upcoming_expires_data[$region]['interval1'] = $this->getUpcomingInspections(0, 6, $region);
        $upcoming_expires_data[$region]['interval2'] = $this->getUpcomingInspections(7, 13, $region);
        $upcoming_expires_data[$region]['interval3'] = $this->getUpcomingInspections(14, 29, $region);
        return compact('upcoming_expires_data');
    }

    public function fetchUpcomingExpires(Request $request) {
        $region = $request->get('region');
        $upcoming_expires_data[$region]['interval4'] = $this->getUpcomingExpires(-1, -1, $region);
        $upcoming_expires_data[$region]['interval1'] = $this->getUpcomingExpires(0, 6, $region);
        $upcoming_expires_data[$region]['interval2'] = $this->getUpcomingExpires(7, 13, $region);
        $upcoming_expires_data[$region]['interval3'] = $this->getUpcomingExpires(14, 29, $region);
        return compact('upcoming_expires_data');
    }

/*    public function vehicleInspectionData() {
        $regions_to_query = ['all' => 'All'] + Auth::user()->regions->lists('name','id')->toArray();
        foreach ($regions_to_query as $key => $value) {
            $vehicle_inspection_data[$key]['interval4'] = $this->getUpcomingInspections(-1, 0, $key);
            $vehicle_inspection_data[$key]['interval1'] = $this->getUpcomingInspections(0, 7, $key);
            $vehicle_inspection_data[$key]['interval2'] = $this->getUpcomingInspections(8, 14, $key);
            $vehicle_inspection_data[$key]['interval3'] = $this->getUpcomingInspections(15, 30, $key);
        }
        return compact('vehicle_inspection_data');
    }*/

    private function getUpcomingInspections($start, $end, $region) {
        $start_date = Carbon::today()->addDays($start);
        $end_date = Carbon::today()->addDays($end);


        $distanceMaintenanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
        $pmiEventId = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();

        $basequery_part1 = Vehicle::with(['type','maintenanceHistories' => function($query) use ($distanceMaintenanceEvent,$start_date,$end_date,$start) {
            $query->where('event_type_id',$distanceMaintenanceEvent->id);
            $query->where('event_status','Incomplete');
            if ($start == -1) {
                $query->where('event_plan_date','<=',$end_date);
            } else {
                $query->whereBetween('event_plan_date',[$start_date,$end_date]);
            }
        },
        'pmiMaintenanceHistories' => function($query) use ($start_date,$end_date,$start){
            $query->whereHas('vehicle',function ($q) {
                $q->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW())) AND event_plan_date != vehicles.next_pmi_date');
            })
            ->whereNotNull('event_plan_date');
            if ($start == -1) {
                $query->where('event_plan_date','<=',$end_date);
            } else {
                $query->whereBetween('event_plan_date',[$start_date,$end_date]);
            }
            $query->where('event_status','Incomplete');
        }
        ]);



        if (strtolower($region) !== 'all') {
            $basequery_part1 = $basequery_part1->where('vehicle_region_id', $region);
        } else {
            $vehicleRegions = Vehicle::whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
            $basequery_part1 = $basequery_part1->whereIn('vehicles.id', $vehicleRegions);
        }

        if ($start >= 0) {
            $baseQuery = $basequery_part1->where(function($q) use ($distanceMaintenanceEvent,$start_date, $end_date)
                        {
                            $q->orwhere(function ($query) use ($distanceMaintenanceEvent,$start_date, $end_date) {
                                $query->where('dt_annual_service_inspection', '<=', $end_date)
                                    ->where('dt_annual_service_inspection', '>=', $start_date);
                            })->orWhere(function($query) use ($start_date, $end_date) {
                                $query->where('dt_next_service_inspection', '<=', $end_date)
                                    ->where('dt_next_service_inspection', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_tacograch_calibration_due', '<=', $end_date)
                                    ->where('dt_tacograch_calibration_due', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_mot_expiry', '<=', $end_date)
                                    ->where('dt_mot_expiry', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_tax_expiry', '<=', $end_date)
                                    ->where('dt_tax_expiry', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_repair_expiry', '<=', $end_date)
                                    ->where('dt_repair_expiry', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->whereDate('first_pmi_date', '<=', $end_date)
                                    ->whereDate('first_pmi_date', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('next_pmi_date', '<=', $end_date)
                                    ->where('next_pmi_date', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('next_compressor_service', '<=', $end_date)
                                    ->where('next_compressor_service', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('next_invertor_service_date', '<=', $end_date)
                                    ->where('next_invertor_service_date', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_loler_test_due', '<=', $end_date)
                                    ->where('dt_loler_test_due', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('next_pto_service_date', '<=', $end_date)
                                    ->where('next_pto_service_date', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('adr_test_date', '<=', $end_date)
                                    ->where('adr_test_date', '>=', $start_date);
                            })
                            ->orWhereHas('pmiMaintenanceHistories' ,function($query) use ($start_date,$end_date)
                            {
                                $query->whereHas('vehicle', function ($q) {
                                    $q->whereRaw('event_plan_date != vehicles.first_pmi_date AND event_plan_date != vehicles.next_pmi_date');
                                })
                                    ->whereNotNull('event_plan_date')
                                    ->whereBetween('event_plan_date',[$start_date,$end_date])
                                    ->where('event_status', 'Incomplete');
                            }
                            )->orWhereHas('maintenanceHistories', function($query) use ($distanceMaintenanceEvent,$start_date,$end_date) {
                                $query->where('event_type_id',$distanceMaintenanceEvent->id);
                                $query->where('event_status','Incomplete')
                                ->whereBetween('event_plan_date',[$start_date,$end_date]);
                            }
                            );;
                        })
                        ;
        }
        else{
            $baseQuery = $basequery_part1->where(function($q) use ($distanceMaintenanceEvent,$end_date) {
                              $q->where('dt_annual_service_inspection', '<=', $end_date)
                                ->orWhere('dt_next_service_inspection', '<=', $end_date)
                                ->orWhere('dt_tacograch_calibration_due', '<=', $end_date)
                                //->orWhere('first_pmi_date', '<=', $end_date)
                                ->orWhere('next_pmi_date', '<=', $end_date)
                                ->orWhere('next_compressor_service', '<=', $end_date)
                                ->orWhere('next_invertor_service_date', '<=', $end_date)
                                ->orWhere('dt_loler_test_due', '<=', $end_date)
                                ->orWhere('adr_test_date', '<=', $end_date)
                                ->orWhere('next_pto_service_date', '<=', $end_date)
                                ->orWhereHas('pmiMaintenanceHistories' ,function($query) use ($end_date)
                                {
                                    $query->whereHas('vehicle', function ($q) {
                                        $q->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW())) AND event_plan_date != vehicles.next_pmi_date');
                                    })
                                    ->whereNotNull('event_plan_date')
                                    ->where('event_plan_date', '<=', $end_date)
                                    ->where('event_status', 'Incomplete');
                                }
                                )->orWhereHas('maintenanceHistories', function($query) use ($distanceMaintenanceEvent,$end_date) {
                                    $query->where('event_type_id',$distanceMaintenanceEvent->id);
                                    $query->where('event_status','Incomplete')->where('event_plan_date', '<=', $end_date);
                                }
                                );
                          });
        }



        $baseResponse = $baseQuery->get()->toArray();
        $counts['adrtest'] = 0;
        $counts['annualservice'] = 0;
        $counts['services'] = 0;
        $counts['services_distance'] = 0;
        $counts['tachograph'] = 0;
        $counts['pmi'] = 0;
        $counts['compressorservice'] = 0;
        $counts['invertorservice'] = 0;
        $counts['lolertest'] = 0;
        $counts['ptoservice'] = 0;

        foreach ($baseResponse as $entry) {
            $entry = (object)$entry;
            if ($start >= 0) {
                if($entry->adr_test_date != null){
                    $adr_test_date = Carbon::createFromFormat('d M Y', $entry->adr_test_date)->startOfDay();
                    if ($adr_test_date->lte($end_date) && $adr_test_date->gte($start_date)) {
                        $counts['adrtest'] = $counts['adrtest'] + 1;
                    }
                }

                if($entry->dt_annual_service_inspection != null){
                    $dt_annual_service_inspection = Carbon::createFromFormat('d M Y', $entry->dt_annual_service_inspection)->startOfDay();
                    if ($dt_annual_service_inspection->lte($end_date) && $dt_annual_service_inspection->gte($start_date)) {
                        $counts['annualservice'] = $counts['annualservice'] + 1;
                    }
                }

                if($entry->type['service_interval_type'] && $entry->type['service_interval_type'] == 'Time') {
                    if ($entry->dt_next_service_inspection != null) {
                        $dt_next_service_inspection = Carbon::createFromFormat('d M Y', $entry->dt_next_service_inspection)->startOfDay();
                        if ($dt_next_service_inspection->lte($end_date) && $dt_next_service_inspection->gte($start_date)) {
                            $counts['services'] = $counts['services'] + 1;
                        }
                    }
                } else {
                    if (count($entry->maintenance_histories) > 0) {
                        $counts['services_distance'] = $counts['services_distance'] + 1;
                    }
                }

                if($entry->dt_tacograch_calibration_due != null){
                    $dt_tacograch_calibration_due = Carbon::createFromFormat('d M Y', $entry->dt_tacograch_calibration_due)->startOfDay();
                    if ($dt_tacograch_calibration_due->lte($end_date) && $dt_tacograch_calibration_due->gte($start_date)) {
                        $counts['tachograph'] = $counts['tachograph'] + 1;
                    }
                }
                if($entry->first_pmi_date != null || $entry->next_pmi_date != null){
                    //$first_pmi_date = Carbon::createFromFormat('d M Y', $entry->first_pmi_date);
                    $first_pmi_date = Carbon::parse($entry->first_pmi_date)->startOfDay();
                    $next_pmi_date = Carbon::parse($entry->next_pmi_date)->startOfDay();
                    //$next_pmi_date = Carbon::createFromFormat('d M Y', $entry->next_pmi_date);
                    if ($first_pmi_date->lte($end_date) && $first_pmi_date->gte($start_date)) {
                        if($first_pmi_date->gte(Carbon::now()->startOfDay())){

                            $firstPMIEvent = VehicleMaintenanceHistory::where('event_type_id',$pmiEventId->id)
                                ->where('event_plan_date',Carbon::parse($entry->first_pmi_date)->format('Y-m-d'))
                                ->where('vehicle_id',$entry->id)
                                ->where('event_status','Complete')
                                ->first();

                            if ($firstPMIEvent) {
                                // Do Nothing
                            } else {
                                $counts['pmi'] = $counts['pmi'] + 1;
                            }

                        } else {
                            if (count($entry->pmi_maintenance_histories) > 0) {
                                $counts['pmi'] = $counts['pmi'] + 1;
                            }
                        }
                    } elseif ($next_pmi_date->lte($end_date) && $next_pmi_date->gte($start_date)) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    } else {
                        if (count($entry->pmi_maintenance_histories) > 0) {
                            $counts['pmi'] = $counts['pmi'] + 1;
                        }
                    }
                } else {
                    if (count($entry->pmi_maintenance_histories) > 0) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    }
                }



                if($entry->next_compressor_service != null){
                    $next_compressor_service = Carbon::createFromFormat('d M Y', $entry->next_compressor_service)->startOfDay();
                    if ($next_compressor_service->lte($end_date) && $next_compressor_service->gte($start_date)) {
                        $counts['compressorservice'] = $counts['compressorservice'] + 1;
                    }
                }
                if($entry->next_invertor_service_date != null){
                    $next_invertor_service_date = Carbon::createFromFormat('d M Y', $entry->next_invertor_service_date)->startOfDay();
                    if ($next_invertor_service_date->lte($end_date) && $next_invertor_service_date->gte($start_date)) {
                        $counts['invertorservice'] = $counts['invertorservice'] + 1;
                    }
                }
                if($entry->dt_loler_test_due != null){
                    $dt_loler_test_due = Carbon::createFromFormat('d M Y', $entry->dt_loler_test_due)->startOfDay();
                    if ($dt_loler_test_due->lte($end_date) && $dt_loler_test_due->gte($start_date)) {
                        $counts['lolertest'] = $counts['lolertest'] + 1;
                    }
                }
                if($entry->next_pto_service_date != null){
                    $next_pto_service_date = Carbon::createFromFormat('d M Y', $entry->next_pto_service_date)->startOfDay();
                    if ($next_pto_service_date->lte($end_date) && $next_pto_service_date->gte($start_date)) {
                        $counts['ptoservice'] = $counts['ptoservice'] + 1;
                    }
                }
            } else{
                if($entry->adr_test_date != null){
                    $adr_test_date = Carbon::createFromFormat('d M Y', $entry->adr_test_date)->startOfDay();
                    if ($adr_test_date->lte($end_date)) {
                        $counts['adrtest'] = $counts['adrtest'] + 1;
                    }
                }
                if($entry->dt_annual_service_inspection != null){
                    $dt_annual_service_inspection = Carbon::createFromFormat('d M Y', $entry->dt_annual_service_inspection)->startOfDay();
                    if ($dt_annual_service_inspection->lte($end_date)) {
                        $counts['annualservice'] = $counts['annualservice'] + 1;
                    }
                }

                if($entry->type['service_interval_type'] && $entry->type['service_interval_type'] == 'Time') {
                    if ($entry->dt_next_service_inspection) {
                        $dt_next_service_inspection = Carbon::createFromFormat('d M Y', $entry->dt_next_service_inspection)->startOfDay();
                        if ($dt_next_service_inspection->lte($end_date)) {
                            $counts['services'] = $counts['services'] + 1;
                        }
                    }
                } else {
                    if (count($entry->maintenance_histories) > 0) {
                        $counts['services_distance'] = $counts['services_distance'] + 1;
                    }
                }

                if($entry->dt_tacograch_calibration_due != null){
                    $dt_tacograch_calibration_due = Carbon::createFromFormat('d M Y', $entry->dt_tacograch_calibration_due)->startOfDay();
                    if ($dt_tacograch_calibration_due->lte($end_date)) {
                        $counts['tachograph'] = $counts['tachograph'] + 1;
                    }
                }

               /* if ($entry->first_pmi_date != null) {
                    $first_pmi_date = Carbon::createFromFormat('d M Y', $entry->first_pmi_date)->startOfDay();
                    if ($first_pmi_date->lte($end_date) && $first_pmi_date->gte(Carbon::now()->startOfDay())) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    } else {
                        if (count($entry->pmi_maintenance_histories) > 0) {
                            $counts['pmi'] = $counts['pmi'] + 1;
                        }
                    }
                } else if ($entry->next_pmi_date != null ) {
                    $next_pmi_date = Carbon::createFromFormat('d M Y', $entry->next_pmi_date)->startOfDay();
                    if ($next_pmi_date->lte($end_date)) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    } else {
                        if (count($entry->pmi_maintenance_histories) > 0) {
                            $counts['pmi'] = $counts['pmi'] + 1;
                        }
                    }
                }*/

                if($entry->next_pmi_date != null){

                    $next_pmi_date = Carbon::parse($entry->next_pmi_date)->startOfDay();
                    if ($next_pmi_date->lte($end_date) && $next_pmi_date->gte($start_date)) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    } else {
                        if (count($entry->pmi_maintenance_histories) > 0) {
                            $counts['pmi'] = $counts['pmi'] + 1;
                        }
                    }
                } else {
                    if (count($entry->pmi_maintenance_histories) > 0) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    }
                }

                    /*$next_pmi_date = Carbon::createFromFormat('d M Y', $entry->next_pmi_date)->startOfDay();
                    if ($first_pmi_date->lte($end_date)) {
                        $counts['pmi'] = $counts['pmi'] + 1;
                    } elseif ($next_pmi_date->lte($end_date)) {
                      $counts['pmi'] = $counts['pmi'] + 1;
                    }*/


                if($entry->next_compressor_service != null){
                    $next_compressor_service = Carbon::createFromFormat('d M Y', $entry->next_compressor_service)->startOfDay();
                    if ($next_compressor_service->lte($end_date)) {
                        $counts['compressorservice'] = $counts['compressorservice'] + 1;
                    }
                }
                if($entry->next_invertor_service_date != null){
                    $next_invertor_service_date = Carbon::createFromFormat('d M Y', $entry->next_invertor_service_date)->startOfDay();
                    if ($next_invertor_service_date->lte($end_date)) {
                        $counts['invertorservice'] = $counts['invertorservice'] + 1;
                    }
                }
                if($entry->dt_loler_test_due != null){
                    $dt_loler_test_due = Carbon::createFromFormat('d M Y', $entry->dt_loler_test_due)->startOfDay();
                    if ($dt_loler_test_due->lte($end_date)) {
                        $counts['lolertest'] = $counts['lolertest'] + 1;
                    }
                }
                if($entry->next_pto_service_date != null){
                    $next_pto_service_date = Carbon::createFromFormat('d M Y', $entry->next_pto_service_date)->startOfDay();
                    if ($next_pto_service_date->lte($end_date)) {
                        $counts['ptoservice'] = $counts['ptoservice'] + 1;
                    }
                }
            }
        }

        return $counts;
    }


    private function getUpcomingExpires($start, $end, $region) {
        $start_date = Carbon::today()->addDays($start);
        // $end_date = Carbon::today()->addDays($end)->endOfDay();
	$end_date = Carbon::today()->addDays($end)->endOfDay();
        $basequery_part1 = Vehicle::whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
        if (strtolower($region) !== 'all') {
            $basequery_part1 = Vehicle::where('vehicle_region_id', $region);
        }

        if ($start >= 0) {
            $baseQuery = $basequery_part1->where(function($q) use ($start_date, $end_date)
                        {
                            $q->where(function ($query) use ($start_date, $end_date) {
                                $query->where('dt_mot_expiry', '<=', $end_date)
                                    ->where('dt_mot_expiry', '>=', $start_date);
                            })->orWhere(function($query) use ($start_date, $end_date) {
                                $query->where('dt_tax_expiry', '<=', $end_date)
                                    ->where('dt_tax_expiry', '>=', $start_date);
                            })->orWhere(function($query)  use ($start_date, $end_date) {
                                $query->where('dt_repair_expiry', '<=', $end_date)
                                    ->where('dt_repair_expiry', '>=', $start_date);
                            });
                        })
                        ;
        }
        else{
            $baseQuery = $basequery_part1->where(function($q) use ($end_date) {
                              $q->where('dt_next_service_inspection', '<=', $end_date)
                                ->orWhere('dt_tax_expiry', '<=', $end_date)
                                ->orWhere('dt_mot_expiry', '<=', $end_date)
                                ->orWhere('dt_repair_expiry', '<=', $end_date);

                          });
        }

        $baseResponse = $baseQuery->get();

        $counts['mot'] = 0;
        $counts['tax'] = 0;
        $counts['repair'] = 0;

        foreach ($baseResponse as $entry) {
            if ($start >= 0) {
                if($entry->dt_mot_expiry != null){
                    $dt_mot_expiry = Carbon::createFromFormat('d M Y', $entry->dt_mot_expiry);
                    if ($dt_mot_expiry->lte($end_date) && $dt_mot_expiry->gte($start_date)) {
                        $counts['mot'] = $counts['mot'] + 1;
                    }
                }
                if($entry->dt_tax_expiry != null){
                    $dt_tax_expiry = Carbon::createFromFormat('d M Y', $entry->dt_tax_expiry);
                    if ($dt_tax_expiry->lte($end_date) && $dt_tax_expiry->gte($start_date)) {
                        $counts['tax'] = $counts['tax'] + 1;
                    }
                }
                if($entry->dt_repair_expiry != null){
                    $dt_repair_expiry = Carbon::createFromFormat('d M Y', $entry->dt_repair_expiry);
                    if ($dt_repair_expiry->lte($end_date) && $dt_repair_expiry->gte($start_date)) {
                        $counts['repair'] = $counts['repair'] + 1;
                    }
                }
            }
            else{
                if($entry->dt_mot_expiry != null){
                    $dt_mot_expiry = Carbon::createFromFormat('d M Y', $entry->dt_mot_expiry);
                    if ($dt_mot_expiry->lte($end_date)) {
                        $counts['mot'] = $counts['mot'] + 1;
                    }
                }
                if($entry->dt_tax_expiry != null){
                    $dt_tax_expiry = Carbon::createFromFormat('d M Y', $entry->dt_tax_expiry);
                    if ($dt_tax_expiry->lte($end_date)) {
                        $counts['tax'] = $counts['tax'] + 1;
                    }
                }
                if($entry->dt_repair_expiry != null){
                    $dt_repair_expiry = Carbon::createFromFormat('d M Y', $entry->dt_repair_expiry);
                    if ($dt_repair_expiry->lte($end_date)) {
                        $counts['repair'] = $counts['repair'] + 1;
                    }
                }
            }

        }

        return $counts;
    }
    public function getVorVsTotalVehicleData($region = 'all') {
        $vor_query = Vehicle::whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined'])
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->selectRaw('vehicle_types.vehicle_type as label, count(vehicles.id) as data')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
            ->groupBy('vehicle_types.id');

        $total_query = VehicleType::selectRaw('vehicle_types.vehicle_type as label, count(vehicles.id) as data');

        if(Auth::user()->regions->count() > 0) {
            $total_query = $total_query->leftJoin('vehicles', function ($join) {
                $join->on('vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                    ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                    ->whereNull('vehicles.deleted_at');
            });
        } else {
            $total_query = $total_query->leftJoin('vehicles', function ($join) {
                $join->on('vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                    ->whereNull('vehicles.deleted_at');
            });
        }

        if (strtolower($region) !== 'all') {
            $vor_query = $vor_query->where('vehicles.vehicle_region_id', '=', $region);
            $total_query = VehicleType::selectRaw('vehicle_types.vehicle_type as label, count(vehicles.id) as data')
                ->leftJoin('vehicles', function ($join) use ($region) {
                    $join->on('vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                        ->where('vehicles.vehicle_region_id', '=', $region)
                        ->whereNull('vehicles.deleted_at');
                });
        }

        $total = $total_query->groupBy('vehicle_types.id')->orderBy('vehicle_types.vehicle_type')->get()->toArray();

        foreach ($total as $key => $index) {
            $total[$key] = array_values($index);
        }
        $vor = $vor_query->orderBy('vehicle_types.id')->get()->toArray();

        foreach ($vor as $key => $index) {
            $vor[$key] = array_values($index);
        }
        return compact('vor', 'total');
    }

    public function getVorVsTotalVehicleCount($region = 'all') {
        $total_query = Vehicle::selectRaw('count(vehicles.id) as count')->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
        $vor_query = Vehicle::selectRaw('count(vehicles.id) as count')->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined']);

        if (strtolower($region) !== 'all') {
            $total_query = $total_query->where('vehicles.vehicle_region_id', $region);
            $vor_query = $vor_query->where('vehicles.vehicle_region_id', $region);
        }
        $total = $total_query->get()->toArray();
        $vor = $vor_query->get()->toArray();

        return [
            'total' => $total[0]['count'],
            'vor' => $vor[0]['count']
        ];
    }

    private function formatPieChartData(array $data) {
        $result = [];
        $color = [
            'Roadworthy' => 'green',
            'Other' => 'orange',
            'VOR' => 'red'
        ];
        foreach ($data as $key => $val) {
            array_push($result, [
                'label' => $key,
                'data' => $val,
                'color' => $color[$key]
            ]);
        }
        return $result;
    }

    private function formatChecksData(array $data, $result = []) {
        $total = 0;
        foreach ($data as $key => $value) {
            // $result[$value['label']] = (int) $value['data'];
            $total += $value['data'];
        }
        foreach ($data as $key => $value) {
            $result[ucfirst($value['label'])] = (int) $value['data'] * 100 / $total;
        }
        return $result;
    }

    public function allDashboardStats(Request $request) {

        $response = [];

        $regionvehicleOffroadStats = $request->get('region');
        $regionInspections = $request->get('regionInspections');
        $regionUpcomingExpires = $request->get('regionUpcomingExpires');

        $response['vehicleFleetStats'] = $this->vehicleFleetStats();

        $response['vehicleChecksStats'] = $this->vehicleChecksStats();

        $request->region = $regionInspections;
        $response['fetchUpcomingInspections'] =  $this->fetchUpcomingInspections($request);


        $request->region = $regionUpcomingExpires;
        $response['fetchUpcomingExpires'] = $this->fetchUpcomingExpires($request);


        $response['vehicleOffroadStats'] = $this->vehicleOffroadStats($request,$regionvehicleOffroadStats);
        return $response;

    }
}
