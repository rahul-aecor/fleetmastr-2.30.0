<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class ChecksRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $this->Database = DB::table('checks')
            ->select('checks.id', 
            
            // 'checks.type',
                DB::raw('CASE WHEN LOWER(checks.type) = "vehicle check" THEN "Vehicle take out" WHEN LOWER(checks.type) = "vehicle check on-call" THEN "Vehicle take out (On-call)" WHEN LOWER(checks.type) = "return check" THEN "Vehicle return" WHEN LOWER(checks.type) = "report defect" THEN "Defect report" ELSE checks.type END AS type'), 
            
            'vehicles.id as vehicle_id', 'vehicles.registration', 
            // 'vehicles.status as vehicles.status',
            'vehicle_divisions.name as vehicle_division',
            'vehicle_regions.name as vehicle_region',
            'vehicle_locations.name as vehicle_location',
            'vehicle_types.vehicle_type', 
            'vehicle_types.manufacturer',
            
            // 'vehicle_types.vehicle_category',
            DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'), 

            'vehicle_types.model',
            // 'checks.odometer_reading',
            DB::raw("CONCAT(FORMAT(odometer_reading, 0), ' ', vehicle_types.odometer_setting) as odometer_reading"),
            
            // 'checks.check_duration',
            DB::raw("CASE WHEN check_duration IS NOT NULL THEN
                CONCAT(
                CASE WHEN floor(TIME_TO_SEC(check_duration) / 60) = '00' THEN '00 min ' ELSE CONCAT(floor(TIME_TO_SEC(check_duration) / 60), ' min ') END,
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1) = '00' THEN ' 00 sec ' ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1), ' sec') END
                ) ELSE 'N/A' END AS  check_duration"),
            /* DB::raw("CASE WHEN check_duration IS NOT NULL THEN
                CONCAT(
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 1), ':', -1) = '00' THEN '' ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 1), ':', -1), ' hours ') END,
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 2), ':', -1) = '00' THEN '' ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 2), ':', -1), ' mins ') END,
                CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1) = '00' THEN '' ELSE CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(check_duration, ':', 3), ':', -1), ' seconds') END
                ) ELSE 'N/A' END AS  check_duration"), */
            
            'checks.created_at', 'checks.updated_at', 

            // 'vehicles.dt_added_to_fleet',
            DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_added_to_fleet, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d') as dt_added_to_fleet"),

            // 'checks.status',
            DB::raw('CASE WHEN checks.status = "SafeToOperate" THEN "Safe to operate" WHEN checks.status = "UnsafeToOperate" THEN "Unsafe to operate" ELSE "Roadworthy" END AS status'), 
            'vehicles.status as vehicles_status',
            //'vehicle_types.odometer_setting',

            DB::raw("DATE_FORMAT(CONVERT_TZ(checks.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%H:%i:%s %d %b %Y') as 'created_at'"),

            // DB::raw("DATE_FORMAT(CONVERT_TZ(checks.report_datetime, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%H:%i:%s %d %b %Y') as 'date_created'"),

            DB::raw("DATE_FORMAT(CONVERT_TZ(checks.report_datetime, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d %H:%i:%s') as 'report_datetime'"),

            DB::raw("DATE_FORMAT(CONVERT_TZ(checks.updated_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d %H:%i:%s') as 'date_updated'"),

            // DB::raw("CONCAT(users.first_name, ' ', users.last_name) as createdBy "),
            DB::raw("CONCAT(UPPER(SUBSTRING(users.first_name,1,1)), ' ', users.last_name) as createdBy"),
            DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy"))

            ->join('vehicles', 'checks.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->leftjoin('vehicle_locations','vehicles.vehicle_location_id', '=', 'vehicle_locations.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->join('users', 'checks.created_by', '=', 'users.id')
            ->leftjoin('users as updatedUser', 'checks.updated_by', '=', 'updatedUser.id')
            ->where('checks.type', '<>', 'Defect Report')
            //->whereIn('vehicles.vehicle_region', Auth::user()->accessible_regions)
            ->whereIn('vehicles.vehicle_region_id', $userRegions)
            ->whereNull('checks.deleted_at');

            if(request()->has('vehicleDisplay') && request()->get('vehicleDisplay') == true){
                $this->Database->whereNotNull('vehicles.deleted_at');
            } else {
                $this->Database->whereNull('vehicles.deleted_at');
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
                $this->Database->where('checks.created_by',Auth::user()->id);
            } 


        $this->visibleColumns = [
            'checks.id', 'checks.type',
            'vehicles.id as vehicle_id', 'vehicles.registration', 
            // 'vehicles.status as vehicles.status', 
            'vehicle_types.vehicle_type',
            'checks.status', 'vehicle_category', 'checks.created_at', 'checks.updated_at',
            'checks.check_duration', 'vehicles.dt_added_to_fleet','vehicle_types.manufacturer',
            'createdBy', 'updatedBy','vehicles.status as vehicles_status','vehicle_divisions.name as vehicle_division', 'vehicle_regions.name as vehicle_region','vehicle_locations.name as vehicle_location',
        ];
        $this->orderBy = [['checks.report_datetime', 'DESC']];
    }
}
