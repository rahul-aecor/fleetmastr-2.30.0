<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;
use App\Services\VehicleService;
use Illuminate\Support\Facades\DB;
use Mail;
use Auth;
use Hash;
use View;
use Input;
use JavaScript;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Http\Requests;
use Illuminate\Http\Request;
use Carbon\Carbon as Carbon;


class PlannerController extends Controller
{
    public $title= 'Planner';

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
        $eventsForFilter = config('config-variables.planner_events');
        $eventsForFilter = MaintenanceEvents::where('is_standard_event',1)->orderBy('name')->get()->lists('name','slug');
        return view('planner.index', compact('eventsForFilter'));
    }

    private function arrayMergeWithSum($firstArray,$secondArray,$matchKey,$sumKey = 'total') {
        $result = [];
        $secondArray = collect($secondArray)->keyBy($matchKey)->toArray();

        foreach ($firstArray as $value){
            $value = (array)$value;
            $single = $value;
            if (isset($secondArray[$value[$matchKey]]) ) {
                $single[$sumKey] = $value[$sumKey] + $secondArray[$value[$matchKey]]->{$sumKey};
                unset($secondArray[$value[$matchKey]]);
            }

            array_push($result,$single);
        }

        $result = array_merge($result,array_values($secondArray));

        return $result;
    }

    public function getPlannerDetails(Request $request) {
        $data = [];

        $start = Carbon::parse($request->startDate);
        $end = Carbon::parse($request->endDate);

        $maintenanceEvents = MaintenanceEvents::all()->keyBy('slug');

        if ($request->selectedEvent == 'loler_test' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_loler_test_due = \DB::table('vehicles')->select('dt_loler_test_due', \DB::raw('count(*) as total'))
                // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_loler_test_due')
                ->whereBetween('dt_loler_test_due', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_loler_test_due')
                ->get();

            $loler_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_loler_test_due,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_loler_test_due')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['loler_test']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();

            $data['LollerTestDueDates'] = $this->arrayMergeWithSum($dt_loler_test_due,$loler_maintenance_entries,'dt_loler_test_due');
        }

        if ($request->selectedEvent == 'maintenance_expiry' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_repair_expiry = \DB::table('vehicles')->select('dt_repair_expiry', \DB::raw('count(*) as total'))
                // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_repair_expiry')
                ->whereBetween('dt_repair_expiry', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_repair_expiry')
                ->get();

            $repair_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_repair_expiry,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_repair_expiry')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['maintenance_expiry']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($dt_repair_expiry);
            $data['RepairMaintenanceContractExpiry'] = $this->arrayMergeWithSum($dt_repair_expiry,$repair_maintenance_entries,'dt_repair_expiry');
            // \Log::info($dt_loler_test_due);
        }


        if ($request->selectedEvent == 'mot' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_mot_expiry = \DB::table('vehicles')->select('dt_mot_expiry', \DB::raw('count(*) as total'))
                // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_mot_expiry')
                ->whereBetween('dt_mot_expiry', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_mot_expiry')
                ->get();

            $mot_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_mot_expiry,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_mot_expiry')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['mot']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($dt_mot_expiry);
            $data['MOTExpiry'] = $this->arrayMergeWithSum($dt_mot_expiry,$mot_maintenance_entries,'dt_mot_expiry');
        }

        if ($request->selectedEvent == 'vehicle_tax' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_tax_expiry = \DB::table('vehicles')->select('dt_tax_expiry', \DB::raw('count(*) as total'))
                // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_tax_expiry')
                ->whereBetween('dt_tax_expiry', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_tax_expiry')
                ->get();

            $vehicle_tax_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_tax_expiry,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_tax_expiry')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['vehicle_tax']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($dt_tax_expiry);
            $data['TaxExpiry'] = $this->arrayMergeWithSum($dt_tax_expiry,$vehicle_tax_maintenance_entries,'dt_tax_expiry');
        }

        if ($request->selectedEvent == 'adr_test' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {

            $adr_test = \DB::table('vehicles')->select('adr_test_date', \DB::raw('count(*) as total'))
                //->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('adr_test_date')
                ->whereBetween('adr_test_date', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('adr_test_date')
                ->get();

            $annual_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as adr_test_date, count(*) as total')
                ->whereRaw('event_plan_date != vehicles.adr_test_date')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['adr_test']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();

            // \Log::info($adr_test);
            $data['AdrTest'] = $this->arrayMergeWithSum($adr_test,$annual_maintenance_entries,'adr_test_date');;
        }

        if ($request->selectedEvent == 'annual_service_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {

            $dt_annual_service_inspection = \DB::table('vehicles')->select('dt_annual_service_inspection', \DB::raw('count(*) as total'))
                //->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_annual_service_inspection')
                ->whereBetween('dt_annual_service_inspection', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_annual_service_inspection')
                ->get();

            $annual_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_annual_service_inspection,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_annual_service_inspection')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['annual_service_inspection']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();

            // \Log::info($dt_annual_service_inspection);
            $data['AnnualService'] = $this->arrayMergeWithSum($dt_annual_service_inspection,$annual_maintenance_entries,'dt_annual_service_inspection');;
        }

        if ($request->selectedEvent == 'next_service_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_next_service_inspection = \DB::table('vehicles')->select('dt_next_service_inspection', \DB::raw('count(*) as total'))
                // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_next_service_inspection')
                ->whereBetween('dt_next_service_inspection', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_next_service_inspection')
                ->get();

            $next_service_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_next_service_inspection,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_next_service_inspection')
                ->whereNotNull('event_plan_date')
                ->where('event_type_id',$maintenanceEvents['next_service_inspection']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->whereNull('vehicles.deleted_at')
                ->groupBy('event_plan_date')
                ->get();

            // \Log::info($dt_next_service_inspection);
            $data['NextService'] = $this->arrayMergeWithSum($dt_next_service_inspection,$next_service_maintenance_entries,'dt_next_service_inspection');
        }

        if ($request->selectedEvent == 'next_service_inspection_distance' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $maintenanceType = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();
            $vehicleRegions = Vehicle::whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
            if (count($vehicleRegions) > 0) {
                $dt_next_service_inspection_distance = VehicleMaintenanceHistory::selectRaw('DATE_FORMAT(event_plan_date,"%Y-%m-%d") as event_plan_date_formatted,count(*) as total')
                    ->where('event_type_id', $maintenanceType->id)
                    ->whereIn('vehicle_id', $vehicleRegions)
                    ->whereNotNull('event_plan_date')
                    ->where('event_status','Incomplete')
                    ->whereBetween('event_plan_date', [$start, $end])
                    ->groupBy('event_plan_date')
                    ->get();

            } else {
                $dt_next_service_inspection_distance = [];
            }
            // \Log::info($dt_next_service_inspection_distance);
            $data['NextServiceDistance'] = $dt_next_service_inspection_distance;
        }

        if ($request->selectedEvent == 'preventative_maintenance_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {

            $pmiEvent = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();


            $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();

            if (count($vehicleIds) > 0) {
                $next_pmi_date = DB::table('vehicles')->select('next_pmi_date', \DB::raw('count(*) as total'),\DB::raw('group_concat(id) as vehicle_id_arr'))
                    ->whereIn('vehicles.id',$vehicleIds)
                    ->whereNotNull('next_pmi_date')
                    ->whereNull('deleted_at')
                    ->whereBetween('next_pmi_date', [$start, $end])
                    ->groupBy('next_pmi_date')
                    ->get();

                $first_pmi_date = DB::table('vehicles')->select(\DB::raw('first_pmi_date as next_pmi_date'), \DB::raw('count(*) as total'),\DB::raw('group_concat(id) as vehicle_id_arr'))
                    ->whereIn('vehicles.id',$vehicleIds)
                    ->whereNotNull('first_pmi_date')
                    ->whereNull('deleted_at')
                    ->whereBetween('first_pmi_date', [$start, $end])
                    ->where('first_pmi_date','>=',Carbon::now()->toDateString())
                    ->whereNull('vehicles.deleted_at')
                    ->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")')
                    ->groupBy('first_pmi_date')
                    ->get();

                $vehiclePmiMaintenance = VehicleMaintenanceHistory::where('event_type_id', $pmiEvent->id)
                    ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                    ->whereIn('vehicle_id',$vehicleIds)
                    ->whereNotNull('event_plan_date')
                    ->whereBetween('event_plan_date',[$start,$end])
                    ->where('event_status','Incomplete')
                    ->whereNull('vehicles.deleted_at')
                    ->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW()))  AND event_plan_date !=vehicles.next_pmi_date')
                    ->selectRaw('DATE_FORMAT(event_plan_date,"%Y-%m-%d") as next_pmi_date,count(*) as total, group_concat(vehicle_id) as vehicle_id_arr')
                    ->groupBy('event_plan_date')
                    ->get()->keyBy('next_pmi_date')->toArray();

                //dd($first_pmi_date,$vehiclePmiMaintenance,$next_pmi_date);

                $final = [];

                foreach ($next_pmi_date as $value){
                    $value = (array)$value;
                    $single = $value;
                    if (isset($vehiclePmiMaintenance[$value['next_pmi_date']]) ) {
                        $single['total'] = $value['total'] + $vehiclePmiMaintenance[$value['next_pmi_date']]['total'];
                        unset($vehiclePmiMaintenance[$value['next_pmi_date']]);
                    }

                    array_push($final,$single);
                }

                $final = array_merge($final,array_values($vehiclePmiMaintenance));

                $finalCollection = collect($final)->keyBy('next_pmi_date');

                $finalCollection = $finalCollection->toArray();

                foreach ($first_pmi_date as $key => $value) {
                    $value = (array)$value;
                    if (isset($finalCollection[$value['next_pmi_date']]) ) {
                        $finalCollection[$value['next_pmi_date']]['total'] = $value['total'] + $finalCollection[$value['next_pmi_date']]['total'];
                        unset($first_pmi_date[$key]);
                    }
                }

                $first_pmi_date = json_decode(json_encode($first_pmi_date), true);
                $final = array_merge(array_values($finalCollection),array_values($first_pmi_date));

                $final = $this->fetchFuturePmiEvents($final, $vehicleIds, clone $start, clone $end);

                // \Log::info($next_pmi_date);
                $data['pmiDate'] = $final;
            }
        }

        if ($request->selectedEvent == 'invertor_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {

            $next_invertor_service_date = \DB::table('vehicles')->select('next_invertor_service_date', \DB::raw('count(*) as total'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('next_invertor_service_date')
                ->whereBetween('next_invertor_service_date', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('next_invertor_service_date')
                ->get();

            $invertor_service_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as next_invertor_service_date,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.next_invertor_service_date')
                ->whereNotNull('event_plan_date')
                ->whereNull('vehicles.deleted_at')
                ->where('event_type_id',$maintenanceEvents['invertor_inspection']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->groupBy('event_plan_date')
                ->get();

            // \Log::info($next_invertor_service_date);
            $data['invertorServiceDate'] = $this->arrayMergeWithSum($next_invertor_service_date,$invertor_service_maintenance_entries,'next_invertor_service_date');
        }

        if ($request->selectedEvent == 'pto_service_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $next_pto_service_date = \DB::table('vehicles')->select('next_pto_service_date', \DB::raw('count(*) as total'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('next_pto_service_date')
                ->whereBetween('next_pto_service_date', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('next_pto_service_date')
                ->get();

            $pto_service_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as next_pto_service_date,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.next_pto_service_date')
                ->whereNotNull('event_plan_date')
                ->whereNull('vehicles.deleted_at')
                ->where('event_type_id',$maintenanceEvents['pto_service_inspection']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($next_pto_service_date);
            $data['ptoServiceDate'] = $this->arrayMergeWithSum($next_pto_service_date,$pto_service_maintenance_entries,'next_pto_service_date');
        }

        if ($request->selectedEvent == 'compressor_inspection' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $next_compressor_service = \DB::table('vehicles')->select('next_compressor_service', \DB::raw('count(*) as total'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('next_compressor_service')
                ->whereBetween('next_compressor_service', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('next_compressor_service')
                ->get();

            $compressor_inspection_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as next_compressor_service,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.next_compressor_service')
                ->whereNotNull('event_plan_date')
                ->whereNull('vehicles.deleted_at')
                ->where('event_type_id',$maintenanceEvents['compressor_inspection']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($next_compressor_service);
            $data['compressorService'] = $this->arrayMergeWithSum($next_compressor_service,$compressor_inspection_maintenance_entries,'next_compressor_service');
        }

        if ($request->selectedEvent == 'tachograph_calibration' || $request->selectedEvent == '' || $request->selectedEvent == 'All') {
            $dt_tacograch_calibration_due = \DB::table('vehicles')->select('dt_tacograch_calibration_due', \DB::raw('count(*) as total'))
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->whereNotNull('dt_tacograch_calibration_due')
                ->whereBetween('dt_tacograch_calibration_due', [$start, $end])
                ->whereNull('deleted_at')
                ->groupBy('dt_tacograch_calibration_due')
                ->get();

            $tachograph_calibration_maintenance_entries = DB::table('vehicle_maintenance_history')
                ->join('vehicles','vehicles.id','=','vehicle_maintenance_history.vehicle_id')
                ->selectRaw('event_plan_date as dt_tacograch_calibration_due,count(*) as total')
                ->whereRaw('event_plan_date != vehicles.dt_tacograch_calibration_due')
                ->whereNotNull('event_plan_date')
                ->whereNull('vehicles.deleted_at')
                ->where('event_type_id',$maintenanceEvents['tachograph_calibration']->id)
                ->whereBetween('event_plan_date', [$start, $end])
                ->where('event_status','Incomplete')
                ->groupBy('event_plan_date')
                ->get();
            // \Log::info($dt_tacograch_calibration_due);
            $data['tachographCalibration'] = $this->arrayMergeWithSum($dt_tacograch_calibration_due,$tachograph_calibration_maintenance_entries,'dt_tacograch_calibration_due');
        }

        return $data;
    }

    /**
     * Get Next Service Inspection event-dates to show on UI-calendar
     */
    public function getSelectedDateData(Request $request,$date){
        $dateDetails = [];
        // $plannerEvents = config('config-variables.planner_events_names');
        $plannerEvents = config('config-variables.eventSlugWithVehicleFields');
        $maintenanceEvents = MaintenanceEvents::all()->keyBy('slug');

        foreach ($plannerEvents as $key => $value) {
            if($key == 'preventative_maintenance_inspection') {
                $data = Vehicle::where(function ($query) use ($value, $date,$maintenanceEvents) {
                            $query->where(function ($q) use ($value,$date,$maintenanceEvents){
                                $q->where($value,$date,$maintenanceEvents)
                                    ->orWhere(function ($q) use ($value,$date,$maintenanceEvents){
                                            $q->where('first_pmi_date', $date);
                                            $q->where('first_pmi_date','>=', Carbon::now()->toDateString());
                                            $q->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")');
                                        }
                                    );
                            });
                            $query->orWhereHas('pmiMaintenanceHistories',function ($q) use ($date){
                                $q->where('event_plan_date',$date)
                                    ->where('event_status','Incomplete')
                                    ->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW()))  AND event_plan_date !=vehicles.next_pmi_date');
                            });
                        })
                        ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                        ->get();


                $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                $start = Carbon::parse($date);
                $end = Carbon::parse($date)->addDay(1);
                $final = $this->fetchFuturePmiEvents([], $vehicleIds, $start, $end, true);

                $data = $data->merge($final);
                $uniqueData = $data->unique('registration');
                $dateDetails[$key] = $uniqueData->values()->all();

            } elseif ($key == 'next_service_inspection_distance') {
                $maintenanceEvent = MaintenanceEvents::where('slug',$key)->first();
                $vehicleRegions = Vehicle::whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceEvent->id)
                    ->where('event_plan_date',$date)
                    ->where('event_status','Incomplete')
                    ->whereIn('vehicle_id', $vehicleRegions)
                    ->get()->pluck('vehicle_id')->toArray();
                if (count($vehicleMaintenanceHistory) > 0) {
                    $data = Vehicle::whereIn('id',$vehicleMaintenanceHistory)->get();
                } else {
                    $data = [];
                }
                $dateDetails[$key] = $data;

            } else {
               /* $data = Vehicle::where($value, $date)
                    ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                    ->get();*/
                $event = $maintenanceEvents[$key];
                $data = Vehicle::where(function ($query) use ($value,$date,$event){
                    $query->where($value,$date);
                    $query->orWhereHas('maintenanceHistories',function ($q) use ($event,$date){
                        $q->where('event_plan_date',$date);
                        $q->where('event_type_id',$event->id);
                        $q->where('event_status','Incomplete');
                    });
                })
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->get();
                $dateDetails[$key] = $data;
            }
        }
        //$eventsForFilter = config('config-variables.planner_events');
        $eventsForFilter = MaintenanceEvents::whereIn('is_standard_event',[1,2])->orderBy('name')->get()->lists('name','slug')->toArray();

        $eventsForFilter = ['All' => 'All'] + $eventsForFilter;
        $selectedEvent = $request->selectedEvent;
        $dateDetailHtml = view('planner.date_detail', array('dateDetails' => $dateDetails, 'date' => $date, 'plannerEvents' => $plannerEvents, 'eventsForFilter' => $eventsForFilter,'selectedEvent' =>$selectedEvent))->render();

        return $dateDetailHtml;
    }

    public function getSelectedEventData(Request $request) {
        $selectedEvent = $request->key;
        $date = $request->selectedDate;
        //$plannerEvents = config('config-variables.planner_events_names');

        $plannerEventsFields = config('config-variables.eventSlugWithVehicleFields');
        $plannerEvents = MaintenanceEvents::whereIn('is_standard_event',[1,2])->orderBy('name')->get()->lists('name','slug');
        $maintenanceEvents = MaintenanceEvents::all()->keyBy('slug');
        $field = $plannerEventsFields[$selectedEvent];
        if($selectedEvent == 'preventative_maintenance_inspection') {
            $data = Vehicle::where(function ($query) use ($field, $date,$maintenanceEvents) {
                $query->where(function ($q) use ($field,$date,$maintenanceEvents){
                    $q->where($field,$date)
                        ->orWhere(function ($q) use ($field,$date,$maintenanceEvents){
                            $q->where('first_pmi_date', $date);
                            $q->where('first_pmi_date','>=', Carbon::now()->toDateString());
                            $q->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")');
                        }
                        );
                });
                $query->orWhereHas('pmiMaintenanceHistories',function ($q) use ($date){
                    $q->where('event_plan_date',$date)
                        ->where('event_status','Incomplete')
                        ->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW()))  AND event_plan_date !=vehicles.next_pmi_date');
                });
            })
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->get();

            $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
            $start = Carbon::parse($date);
            $end = Carbon::parse($date)->addDay(1);
            $final = $this->fetchFuturePmiEvents([], $vehicleIds, $start, $end, true);

            $data = $data->merge($final);
            $uniqueData = $data->unique('registration');
            $data = $uniqueData->values()->all();

        } else if ($selectedEvent == 'next_service_inspection_distance') {
            $maintenanceEvent = MaintenanceEvents::where('slug',$selectedEvent)->first();
            $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceEvent->id)
                ->where('event_plan_date',$date)
                ->where('event_status','Incomplete')
                ->get()->pluck('vehicle_id')->toArray();
            if (count($vehicleMaintenanceHistory) > 0) {
                $data = Vehicle::whereIn('id',$vehicleMaintenanceHistory)->get();
            } else {
                $data = [];
            }
        }else {
            //$data = Vehicle::where($plannerEventsFields[$selectedEvent], $date)->get();
            $event = $maintenanceEvents[$selectedEvent];
            //dd($field,$date,$event->id);
            $data = Vehicle::where(function ($query) use ($field,$date,$event){
                $query->where($field,$date);
                $query->orWhereHas('maintenanceHistories',function ($q) use ($event,$date){
                    $q->where('event_plan_date',$date);
                    $q->where('event_type_id',$event->id);
                    $q->where('event_status','Incomplete');
                });
            })->get();
        }
        $eventDetailHtml = view('planner.event', array('selectedEvent' => $selectedEvent, 'date' => $date, 'data' => $data,'plannerEvents' => $plannerEvents))->render();

        return $eventDetailHtml;
    }

    /**
     * export DayEvents to PDF
     */
    public function exportDayEvents($eventDate, $filter){
        $dateDetails = [];
        $date = new \DateTime(date('H:i:s d M Y'));
        $maintenanceEventAll = MaintenanceEvents::whereIn('is_standard_event',[1,2])->orderBy('name','ASC')->get()->keyBy('slug');
        $plannerEvents = config('config-variables.eventSlugWithVehicleFields');
        if ($filter == "" || $filter == "all") {
            foreach ($plannerEvents as $key => $value) {
                if($key == 'preventative_maintenance_inspection') {
                    $data = Vehicle::where(function ($query) use ($value, $eventDate,$maintenanceEventAll) {
                        $query->where(function ($q) use ($value,$eventDate,$maintenanceEventAll){
                            $q->where($value,$eventDate)
                                ->orWhere(function ($q) use ($value,$eventDate,$maintenanceEventAll){
                                    $q->where('first_pmi_date', $eventDate);
                                    $q->where('first_pmi_date','>=', Carbon::now()->toDateString());
                                    $q->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEventAll['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")');
                                }
                                );
                        });
                        $query->orWhereHas('pmiMaintenanceHistories',function ($q) use ($eventDate){
                            $q->where('event_plan_date',$eventDate)
                                ->where('event_status','Incomplete')
                                ->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW()))  AND event_plan_date !=vehicles.next_pmi_date');
                        });
                    })
                    ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                    ->get();

                    $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                    $start = Carbon::parse($eventDate);
                    $end = Carbon::parse($eventDate)->addDay(1);
                    $final = $this->fetchFuturePmiEvents([], $vehicleIds, $start, $end, true);

                    $data = $data->merge($final);
                    $uniqueData = $data->unique('registration');
                    $data = $uniqueData->values()->all();

                } else if ($key == 'next_service_inspection_distance') {
                    $maintenanceEvent = MaintenanceEvents::where('slug',$key)->first();
                    $vehicleRegions = Vehicle::whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                    $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceEvent->id)
                        ->where('event_plan_date',$eventDate)
                        ->where('event_status','Incomplete')
                        ->whereIn('vehicle_id', $vehicleRegions)
                        ->get()->pluck('vehicle_id')->toArray();
                    if (count($vehicleMaintenanceHistory) > 0) {
                        $data = Vehicle::whereIn('id',$vehicleMaintenanceHistory)->get();
                    } else {
                        $data = collect([]);
                    }
                } else {
                  /*  $data = Vehicle::where($value, $eventDate)
                        ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                        ->get();*/

                    $field = $plannerEvents[$key];
                    $event = $maintenanceEventAll[$key];
                    $data = Vehicle::where(function ($query) use ($field,$eventDate,$event){
                        $query->where($field,$eventDate);
                        $query->orWhereHas('maintenanceHistories',function ($q) use ($event,$eventDate){
                            $q->where('event_plan_date',$eventDate);
                            $q->where('event_type_id',$event->id);
                            $q->where('event_status','Incomplete');
                        });
                    })->get();
                }

                $dateDetails[$key] = $data;
            }
        } else {
            //$data = Vehicle::where($plannerEvents[$filter],$eventDate)->get();
            $field = $plannerEvents[$filter];
            $event = $maintenanceEventAll[$filter];
            $data = Vehicle::where(function ($query) use ($field,$eventDate,$event){
                $query->where($field,$eventDate);
                $query->orWhereHas('maintenanceHistories',function ($q) use ($event,$eventDate){
                    $q->where('event_plan_date',$eventDate);
                    $q->where('event_type_id',$event->id);
                    $q->where('event_status','Incomplete');
                });
            })->get();

            if($filter == 'preventative_maintenance_inspection') {
                $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                $start = Carbon::parse($eventDate);
                $end = Carbon::parse($eventDate)->addDay(1);
                $final = $this->fetchFuturePmiEvents([], $vehicleIds, $start, $end, true);

                $data = $data->merge($final);
                $uniqueData = $data->unique('registration');
                $data = $uniqueData->values()->all();
            }

            $dateDetails[$filter] = $data;
        }

        $pdf = \PDF::loadView('pdf.dayEventsExport', array('dateDetails' => $dateDetails, 'date' => $eventDate, 'plannerEvents' => $plannerEvents,'maintenanceEventAll' => $maintenanceEventAll))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOrientation('portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));

        $filename = $eventDate . '_' . 'planner.pdf';
        return $pdf->download($filename);
    }

    /**
     * export selected event to PDF
     */
    public function exportSelectedEvents($eventDate, $filter)
    {
        $date = new \DateTime(date('H:i:s d M Y'));

        $maintenanceEvents = MaintenanceEvents::whereIn('is_standard_event',[1,2])->get()->keyBy('slug');
        $plannerEvents = config('config-variables.eventSlugWithVehicleFields');
        if (isset($plannerEvents[$filter])) {
            /*if ($filter == 'next_service_inspection_distance') {
                $maintenanceEvent = MaintenanceEvents::where('slug',$filter)->first();
                $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceEvent->id)
                    ->whereDate('event_plan_date','=',$eventDate)->get()->pluck('vehicle_id')->toArray();
                if (count($vehicleMaintenanceHistory) > 0) {
                    $data = Vehicle::whereIn('id',$vehicleMaintenanceHistory)->get();
                } else {
                    $data = [];
                }
            } else {
                $data = Vehicle::where($plannerEvents[$filter], $eventDate)->get();
            }*/

            $value = $plannerEvents[$filter];
            //$date = $eventDate;
            if($filter == 'preventative_maintenance_inspection') {
                $data = Vehicle::where(function ($query) use ($value, $eventDate,$maintenanceEvents) {
                    $query->where(function ($q) use ($value,$eventDate,$maintenanceEvents){
                        $q->where($value,$eventDate)
                            ->orWhere(function ($q) use ($value,$eventDate,$maintenanceEvents){
                                $q->where('first_pmi_date', $eventDate);
                                $q->where('first_pmi_date','>=', Carbon::now()->toDateString());
                                $q->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")');
                            }
                            );
                    });
                    $query->orWhereHas('pmiMaintenanceHistories',function ($q) use ($eventDate){
                        $q->where('event_plan_date',$eventDate)
                            ->where('event_status','Incomplete')
                            ->whereRaw('(event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW()))  AND event_plan_date !=vehicles.next_pmi_date');
                    });
                })
                    ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                    ->get();

                $vehicleIds = Vehicle::whereIn('vehicle_region_id',Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                $start = Carbon::parse($eventDate);
                $end = Carbon::parse($eventDate)->addDay(1);
                $final = $this->fetchFuturePmiEvents([], $vehicleIds, $start, $end, true);

                $data = $data->merge($final);
                $uniqueData = $data->unique('registration');
                $data = $uniqueData->values()->all();

            } elseif ($filter == 'next_service_inspection_distance') {
                $maintenanceEvent = MaintenanceEvents::where('slug',$filter)->first();
                $vehicleRegions = Vehicle::whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray())->get()->pluck('id')->toArray();
                $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id',$maintenanceEvent->id)
                    ->where('event_plan_date',$eventDate)
                    ->where('event_status','Incomplete')
                    ->whereIn('vehicle_id', $vehicleRegions)
                    ->get()->pluck('vehicle_id')->toArray();
                if (count($vehicleMaintenanceHistory) > 0) {
                    $data = Vehicle::whereIn('id',$vehicleMaintenanceHistory)->get();
                } else {
                    $data = collect([]);
                }
            } else {
              /*  $data = Vehicle::where($plannerEvents[$filter], $eventDate)
                    ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                    ->get();*/

                $event = $maintenanceEvents[$filter];
                $field = $plannerEvents[$filter];
                $data = Vehicle::where(function ($query) use ($field,$eventDate,$event){
                    $query->where($field,$eventDate);
                    $query->orWhereHas('maintenanceHistories',function ($q) use ($event,$eventDate){
                        $q->where('event_plan_date',$eventDate);
                        $q->where('event_type_id',$event->id);
                        $q->where('event_status','Incomplete');
                    });
                })
                ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
                ->get();
            }
        }

        $plannerEvents = config('config-variables.planner_events_names');
        $pdf = \PDF::loadView('pdf.eventExport', array('data' => $data, 'date' => $eventDate, 'plannerEvents' => $plannerEvents, 'filter' => $filter,'maintenanceEvents' =>$maintenanceEvents))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOrientation('portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));

        $filename = $eventDate . '_' . 'planner.pdf';
        return $pdf->download($filename);
    }

    public function get12MonthsCalendar(Request $request,$year) {
        $vehicleService = new VehicleService();
        $dates = $vehicleService->getYearDatesArray($year);

        $html = \Illuminate\Support\Facades\View::make('_partials.fleet_planning.12monthsCalendar',compact('dates'))->render();
        $events = $this->getPlannerDetails($request);

        return [
            'html' => $html,
            'events' => $events
        ];
    }

    public function fetchFuturePmiEvents($final, $vehicleIds, $startDate, $endDate, $onlyVehicleData = false)
    {
        $start = clone $startDate;
        $end = clone $endDate;
        $futureNextYearDate = Carbon::now()->addYear(1)->subMonth(1)->endOfMonth();
        $startDateCheck = $startDate->addMonth(1)->startOfMonth();
        if($startDateCheck->gt($futureNextYearDate)) {
            return $final;
        }
        //FOR FUTURE DATES
        $vehiclesData = Vehicle::whereIn('id', $vehicleIds)->get();
        $finalEntryCount = count($final);
        foreach($vehiclesData as $vehicle) {
            $serviceInterval = $vehicle->type->pmi_interval;
            if($vehicle->next_pmi_date == '' || $vehicle->next_pmi_date == NULL || $serviceInterval == '' || $serviceInterval == 'none' || $serviceInterval == NULL ) {
                continue;
            }
            $event = 'first_pmi_date';
            $interval = \DateInterval::createFromDateString($serviceInterval);
            $nextPmiDate = Carbon::parse($vehicle->next_pmi_date);
            // $eventDate = Carbon::parse($vehicle->next_pmi_date);
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
                if($evDate->gte($start) && $evDate->lte($end)) {
                    if($onlyVehicleData) {
                        $final[$finalEntryCount] = $vehicle;
                        $finalEntryCount++;
                    } else {
                        if (array_search($evDate->format('Y-m-d'), array_column($final, 'next_pmi_date')) !== FALSE) {
                            $entryKey = array_search($evDate->format('Y-m-d'), array_column($final, 'next_pmi_date'));
                            if(isset($final[$entryKey])) {
                                $vehicleIdArr = explode(",", $final[$entryKey]['vehicle_id_arr']);
                                if(!in_array($vehicle->id, $vehicleIdArr)) {
                                    $final[$entryKey]['total'] += 1;
                                    $final[$entryKey]['vehicle_id_arr'] .= ",".$vehicle->id;
                                }
                            }
                        } else {
                            // if(!array_key_exists($evDate->format('Y-m-d'), $maintenanceList['next_pmi_date'])) {
                            $final[$finalEntryCount]['next_pmi_date'] = $evDate->format('Y-m-d');
                            $final[$finalEntryCount]['total'] = 1;
                            $final[$finalEntryCount]['vehicle_id_arr'] = $vehicle->id;
                            $finalEntryCount++;
                        }
                    }
                }
                $eventDate = $eventDate->add($interval);
                $year = $evDate->format('Y');
            }
        }

        return $final;
    }

}