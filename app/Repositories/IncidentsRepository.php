<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use Carbon\Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class IncidentsRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $this->Database = DB::table('incidents')
            ->join('vehicles', 'incidents.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'incidents.created_by', '=', 'users.id')
            ->leftjoin('users as createdBy', 'incidents.created_by', '=', 'createdBy.id')
            ->leftjoin('users as nominatedDriver', 'vehicles.nominated_driver', '=', 'nominatedDriver.id')
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
            ->whereIn('vehicles.vehicle_region_id', $userRegions)
            ->whereNull('incidents.deleted_at')
            // ->select('incidents.*', 'vehicles.*', 'incidents.id as id', 'incidents.status as incidentStatus', 'vehicles.status as vehicleStatus', DB::raw("DATE_FORMAT(CONVERT_TZ(incidents.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),
            //         '%Y-%m-%d %H:%i:%s') as 'date_created_reported'"), 'vehicles.registration as vehicleRegistration', DB::raw("CONCAT(createdBy.first_name, ' ', createdBy.last_name) as createdBy"), 'createdBy.first_name', 'createdBy.last_name');
            ->select('incidents.id', 'incidents.vehicle_id', 'incidents.status as incidentStatus',
            DB::raw("DATE_FORMAT(CONVERT_TZ(incidents.incident_date_time, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%Y-%m-%d %H:%i:%s') as 'incident_date_time'"),
            'incidents.incident_type','incidents.classification','incidents.is_reported_to_insurance', 'incidents.allocated_to', 
            'vehicles.registration as vehicleRegistration', 'vehicles.status as vehicleStatus',
            DB::raw("CONCAT(UPPER(SUBSTRING(createdBy.first_name,1,1)), ' ', createdBy.last_name) as createdBy"),
            DB::raw("DATE_FORMAT(CONVERT_TZ(incidents.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%Y-%m-%d %H:%i:%s') as 'date_created_reported'"));
            
            if(request()->has('vehicleDisplay') && request()->get('vehicleDisplay') == true){
                $this->Database->whereNotNull('vehicles.deleted_at');            
            } else {
                $this->Database->whereNull('vehicles.deleted_at');
            }

        $this->visibleColumns = [
            'incidents.id', 'incidents.vehicle_id', 'incidents.status as incidentStatus',
            DB::raw("DATE_FORMAT(CONVERT_TZ(incidents.incident_date_time, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%Y-%m-%d %H:%i:%s') as 'incident_date_time'"),
            'incidents.incident_type','incidents.classification','incidents.is_reported_to_insurance', 'incidents.allocated_to', 
            'vehicles.registration as vehicleRegistration', 'vehicles.status as vehicleStatus',
            DB::raw("CONCAT(UPPER(SUBSTRING(createdBy.first_name,1,1)), ' ', createdBy.last_name) as createdBy"),
            DB::raw("DATE_FORMAT(CONVERT_TZ(incidents.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%Y-%m-%d %H:%i:%s') as 'date_created_reported'")
        ];
        $this->orderBy = [['incidents.created_at', 'DESC']];
    }
}
