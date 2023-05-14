<?php
namespace App\Repositories;

use App\Models\MaintenanceEvents;
use App\Models\Vehicle;
use Auth;
use App\Models\User;
use App\Models\Check;
use App\Models\Defect;
use Carbon\Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class VehiclesPlanningRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $maintenanceEvents = MaintenanceEvents::all()->keyBy('slug');

        //DATE_FORMAT(vehicles.dt_first_use_inspection,"%Y-%m-%d") as dt_first_use_inspection_original,
        //DATE_FORMAT(vehicles.dt_vehicle_disposed,"%Y-%m-%d") as dt_vehicle_disposed_original,
        $this->Database = Vehicle::selectRaw('
            vehicles.id as vehId,
            vehicles.registration,
            vehicles.next_service_inspection_distance,
            vehicle_types.vehicle_category,
            vehicle_types.vehicle_type,
            vehicle_types.service_interval_type,
            vehicle_divisions.name as vehicle_division,
            vehicle_regions.name as vehicle_region,
            vehicle_locations.name as vehicle_location,
            DATE_FORMAT(vehicles.adr_test_date,"%Y-%m-%d") as dt_adr_test_original,
            DATE_FORMAT(vehicles.dt_repair_expiry,"%Y-%m-%d") as dt_repair_expiry_original,
            DATE_FORMAT(vehicles.dt_mot_expiry,"%Y-%m-%d") as dt_mot_expiry_original,
            DATE_FORMAT(vehicles.dt_next_service_inspection,"%Y-%m-%d") as dt_next_service_inspection_original,
            DATE_FORMAT(vehicles.dt_tacograch_calibration_due,"%Y-%m-%d") as dt_tacograch_calibration_due_original,
            DATE_FORMAT(vehicles.dt_tax_expiry,"%Y-%m-%d") as dt_tax_expiry_original,
            DATE_FORMAT(vehicles.dt_loler_test_due,"%Y-%m-%d") as dt_loler_test_due_original,
            DATE_FORMAT(vehicles.dt_annual_service_inspection,"%Y-%m-%d") as dt_annual_service_inspection_original,
            DATE_FORMAT(vehicles.next_pto_service_date,"%Y-%m-%d") as next_pto_service_date_original,
            DATE_FORMAT(vehicles.next_invertor_service_date,"%Y-%m-%d") as next_invertor_service_date_original,
            DATE_FORMAT(vehicles.first_pmi_date,"%Y-%m-%d") as first_pmi_date_original,
            DATE_FORMAT(vehicles.next_pmi_date,"%Y-%m-%d") as next_pmi_date_original,
            DATE_FORMAT(vehicles.next_compressor_service,"%Y-%m-%d") as next_compressor_service_original,
            vehicles.next_service_inspection_distance - vehicles.last_odometer_reading as diff,
            (select event_date from vehicle_maintenance_history where vehicle_id = vehicles.id and event_status = "Complete" AND event_type = "preventative_maintenance_inspection" order by event_date desc limit 1 ) as last_pmi_inspection,
            last_distance.event_planned_distance as incomplete_distance,
            last_distance.event_planned_distance as incomplete_distance,
            last_distance.event_plan_date as incomplete_date,
            vehicles.id as id,
            pmi_interval,
            vehicle_types.odometer_setting,
            (select event_plan_date from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicles.id  = vehicle_maintenance_history.vehicle_id AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete" ORDER BY vehicle_maintenance_history.event_plan_date DESC LIMIT 1) as completed_pmi,
            (select event_plan_date from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_planned_distance = vehicles.next_service_inspection_distance AND vehicles.id  = vehicle_maintenance_history.vehicle_id AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['next_service_inspection_distance']->id.' AND vehicle_maintenance_history.event_status = "Incomplete" ORDER BY vehicle_maintenance_history.event_plan_date DESC LIMIT 1) as next_distance_date')
            /*->with(['maintenanceHistories' => function ($q) use ($maintenanceEvents) {
                $q->selectRaw('vehicle_maintenance_history.*,DATE_FORMAT(vehicle_maintenance_history.event_plan_date,"%Y-%m-%d") as event_plan_date_original');
                if (request()->has('time') && request('time') != "" && request('time') == 'Distance') {
                    if (request('startRange') == 'undefined') {
                        $q->where('vehicle_maintenance_history.event_plan_date','<',request('endRange'));
                    } else {
                        $q->whereBetween('vehicle_maintenance_history.event_plan_date',[request('startRange'),request('endRange')]);
                    }
                } else if (request()->has('distance') && request('distance') != "" && request('distance') == 'Exceeded') {
                    //do nothing
                }
                else {
                    //do nothing
                }
                $q->where('vehicle_maintenance_history.event_type_id',$maintenanceEvents['next_service_inspection_distance']->id);
                $q->where('vehicle_maintenance_history.event_status','Incomplete');
                $q->whereNotNull('vehicle_maintenance_history.event_planned_distance');
                $q->orderBy('vehicle_maintenance_history.event_plan_date','DESC');
            }])*/
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->leftJoin('vehicle_maintenance_history as last_distance',function ($join){
                $join->on('vehicles.id', '=', 'last_distance.vehicle_id');
                $join->on(DB::raw('last_distance.event_planned_distance=(next_service_inspection_distance - REPLACE(service_inspection_interval,",","")) AND last_distance.event_status = "Incomplete"'),DB::raw(''),DB::raw(''));
                $join->on(DB::raw('last_distance.id IS NOT NULL'),DB::raw(''),DB::raw(''));
            })
            ->leftJoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftJoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->leftJoin('vehicle_locations','vehicles.vehicle_location_id', '=', 'vehicle_locations.id')
            ->whereNull('vehicles.deleted_at')
            //->whereIn('vehicles.vehicle_region', Auth::user()->accessible_regions);
            ->whereIn('vehicles.vehicle_region_id', $userRegions);


        if (request()->has('region') && request('region') != 'all') {
            $this->Database->where('vehicles.vehicle_region_id',request('region'));
        }

        if (request()->has('field') && request('field') == 'pmi' && request()->has('period')) {
            $period = request('period');
            if ($period === 'other') {
                $start_range = NULL;
                //$end_range = Carbon::today()->addDays(7)->toDateString();
                $end_range = Carbon::today()->toDateString();

                $this->Database->where(function ($query) use ($start_range,$end_range,$maintenanceEvents) {
                    $query->where(function ($q) use ($start_range,$end_range,$maintenanceEvents) {
                        //$q->where('first_pmi_date', '<=', $end_range);
                        $q->where('next_pmi_date', '<', $end_range);

                    })->orWhereRaw('vehicles.id IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date < "'.$end_range.'" AND vehicle_maintenance_history.event_plan_date IS NOT NULL AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Incomplete")');

                       /* ->orWhere(function($q) use ($start_range,$end_range,$maintenanceEvents) {
                        $q->where('pmi_events.event_plan_date','<',$end_range)
                        ->whereNotNull('pmi_events.event_plan_date')
                        ->where('pmi_events.event_type_id',$maintenanceEvents['preventative_maintenance_inspection']->id)
                        ->whereRaw('(pmi_events.event_plan_date !=vehicles.first_pmi_date OR vehicles.first_pmi_date < DATE(NOW())) AND pmi_events.event_plan_date != vehicles.next_pmi_date')
                        ->where('pmi_events.event_status','Incomplete')
                        ->whereNotNull('pmi_events.id');
                    });*/

                });
            } else {
                if ($period === 'red') {
                    //$start_range = NULL;
                    $start_range = Carbon::today()->addDays(0)->toDateString();;
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
                $this->Database->where(function ($query) use ($start_range,$end_range,$maintenanceEvents){
                    $query->where(function ($sql) use ($start_range,$end_range,$maintenanceEvents){
                        $sql->whereDate('first_pmi_date','>=',$start_range);
                        $sql->whereDate('first_pmi_date','<=',$end_range);
                        $sql->whereRaw('vehicles.id NOT IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date = vehicles.first_pmi_date AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Complete")');
                    });
                    $query->orWhere(function ($sql) use ($start_range,$end_range){
                        $sql->whereDate('next_pmi_date','>=',$start_range);
                        $sql->whereDate('next_pmi_date','<=',$end_range);
                    })->orWhereRaw('vehicles.id IN (select vehicle_id from vehicle_maintenance_history WHERE vehicle_maintenance_history.event_plan_date BETWEEN "'.$start_range.'" AND "'.$end_range.'" AND vehicle_maintenance_history.event_plan_date IS NOT NULL AND vehicle_maintenance_history.event_type_id = '.$maintenanceEvents['preventative_maintenance_inspection']->id.' AND vehicle_maintenance_history.event_status = "Incomplete")');

                    /*->orWhere(function($q) use ($start_range,$end_range,$maintenanceEvents) {
                        $q->where('pmi_events.event_plan_date','>=',$start_range);
                        $q->where('pmi_events.event_plan_date','<=',$end_range)
                            ->where('pmi_events.event_type_id',$maintenanceEvents['preventative_maintenance_inspection']->id)
                            ->where('pmi_events.event_status','Incomplete');
                    });*/
                });
            }
        }

        if (request()->has('distance') && request('distance') != "") {
            $range = request('distance');
            $this->Database->whereHas('maintenanceHistories',function ($query) use ($range,$maintenanceEvents){
                $query->where('vehicle_types.service_interval_type','Distance');
                $query->where('vehicle_maintenance_history.event_type_id',$maintenanceEvents['next_service_inspection_distance']->id);
                // if ($range == 'Exceeded') {
                //     $this->Database->whereRaw('last_distance.event_planned_distance = (vehicles.next_service_inspection_distance - REPLACE(vehicle_types.service_inspection_interval,",",""))');
                //     $this->Database->where('last_distance.event_status','Incomplete');
                //     //$this->Database->whereRaw('(event_planned_distance - last_odometer_reading) < 0');
                // }
            });

            if ($range == 'Exceeded') {
                $this->Database->where(function ($query) {
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) < 0');
                });
            }

            if ($range == '0-1000') {
                $this->Database->where(function ($query) {
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) >= 0');
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) <= 1000');
                });
            }

            if ($range == '1001-2000') {
                $this->Database->where(function ($query) {
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) >= 1001');
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) <= 2000');
                });
            }

            if ($range == '2001-3000') {
                $this->Database->where(function ($query) {
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) >= 2001');
                    $query->whereRaw('(next_service_inspection_distance - last_odometer_reading) <= 3000');
                });
            }

            if ($range == '3000Plus') {
                $this->Database->whereRaw('(next_service_inspection_distance - last_odometer_reading) >= 3001');
            }
        }

        if (request()->has('time') && request('time') != "") {
            $range = request('filters');
            $startRange = request('startRange');
            $endRange = request('endRange');
            $inteval = request('time');

            if($inteval == 'Distance') {
                $this->Database->where('vehicle_types.service_interval_type',request('time'));
            }

            if ($startRange == 'undefined') {
                $this->Database->where(function ($query) use ($startRange,$endRange) {
                    $query->whereHas('maintenanceHistories',function($sql) use ($startRange,$endRange) {
                        return $sql->where('vehicle_maintenance_history.event_plan_date','<',$endRange)
                            ->where('vehicle_maintenance_history.event_status','Incomplete')
                            ->whereNotNull('vehicle_maintenance_history.event_planned_distance')
                            ->where('vehicle_types.service_interval_type','Distance');
                    });

                    $query->orWhere(function($sql) use ($startRange,$endRange) {
                        return $sql->where('dt_next_service_inspection','<',$endRange)
                            ->whereNotNull('dt_next_service_inspection')
                            ->where('vehicle_types.service_interval_type','Time ');
                    });
                });
            } else {
                $this->Database->where(function ($query) use ($startRange,$endRange) {
                    $query->whereHas('maintenanceHistories',function($sql) use ($startRange,$endRange) {
                        return $sql->where('vehicle_maintenance_history.event_plan_date','>=',$startRange)
                            ->where('vehicle_maintenance_history.event_plan_date','<=',$endRange)
                            ->where('vehicle_maintenance_history.event_status','Incomplete')
                            ->whereNotNull('vehicle_maintenance_history.event_planned_distance');
                    });
                    $query->orWhere(function($sql) use ($startRange,$endRange) {
                        return $sql->where('dt_next_service_inspection','>=',$startRange)
                            ->where('dt_next_service_inspection','<=',$endRange);
                    });
                });
            }
        }

        $user = User::with('roles')->findOrFail(Auth::user()->id);
        $userInformationOnly = 0;
        foreach ($user['roles'] as $value) {
            if($value['id'] == 14) {
                $userInformationOnly = 1;
                break;
            }
        }

        if($userInformationOnly == 1) {
            $checkVehicles = Check::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $defectVehicles = Defect::where('created_by',Auth::user()->id)->distinct()->get(['vehicle_id'])->pluck('vehicle_id')->toArray();
            $vehicleIds = array_unique (array_merge ($checkVehicles, $defectVehicles));
            // $vehicleIds = array_merge ($checkVehicles, $defectVehicles);
           $this->Database->whereIn('vehicles.id',$vehicleIds);
        }

        $this->visibleColumns = [
            'diff',
            'vehicles.id',
            'vehicles.registration',
            'vehicle_types.vehicle_category',
            'vehicle_types.vehicle_type',
            'vehicles.adr_test_date',
            'vehicles.dt_repair_expiry',
            'vehicles.dt_mot_expiry',
            'vehicles.dt_tax_expiry',
            'vehicles.dt_annual_service_inspection',
            'vehicles.dt_next_service_inspection',
            'vehicles.dt_loler_test_due',
            'vehicles.dt_tacograch_calibration_due',
            'vehicles.next_pto_service_date',
            'vehicles.next_pmi_date',
            'vehicles.first_pmi_date',
            'vehicles.next_compressor_service',
            'vehicles.next_invertor_service_date',
            'vehicle_division',
            'vehicle_region',
            'vehicle_location',
            'vehicles.next_service_inspection_distance',
            'vehicle_types.service_interval_type',
            'vehicles.last_odometer_reading',
            'vehId',
            'vehicle_maintenance_history.event_plan_date',
            'vehicle_maintenance_history.event_status',
            'vehicle_maintenance_history.event_planned_distance',
            'pmi_planned_date_max',
            'pmi_planned_date_min',
            'incomplete_distance',
            'incomplete_date',
            //'maintenanceHistories',
            'completed_pmi',
            'next_distance_date',
        ];

        $this->orderBy = [['vehicles.registration', 'ASC']];
    }
}
