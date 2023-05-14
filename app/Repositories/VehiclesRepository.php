<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use App\Models\Check;
use App\Models\Defect;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use \Carbon\Carbon;

class VehiclesRepository extends EloquentRepositoryAbstract {

    public function __construct($data)
    {
        if(isset($data['download']) && $data['download'] == true) {
            $status1 = 'vehicle_status';
            $status2 = 'status';
        } else {
            $status1 = 'status';
            $status2 = 'vehicle_status';
        }
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $this->Database = DB::table('vehicles')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->leftJoin('vehicle_vor_logs', function($join){
                $join->on('vehicles.id', '=', 'vehicle_vor_logs.vehicle_id')->whereNull('dt_back_on_road');
            })
            ->leftJoin(DB::raw("(SELECT vehicle_id, MAX(id) as id, MAX(report_datetime) as created_at from checks group by vehicle_id order by created_at desc) as items_count"),function($join){
                $join->on('vehicles.id', '=', 'items_count.vehicle_id');
                $join->on('items_count.created_at', '>=', DB::raw('"'.Carbon::now()->startOfDay().'"'));
            })
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->leftjoin('vehicle_locations','vehicles.vehicle_location_id', '=', 'vehicle_locations.id')
            // ->join('users', 'vehicles.created_by', '=', 'users.id')
            ->leftjoin('users as createdUser', 'vehicles.created_by', '=', 'createdUser.id')                
            ->leftjoin('users as nominatedDriver', 'vehicles.nominated_driver', '=', 'nominatedDriver.id')
            ->leftjoin('users as updatedUser', 'vehicles.updated_by', '=', 'updatedUser.id')
            ->whereIn('vehicles.vehicle_region_id', $userRegions)
            ->select('vehicles.id',

                // 'items_count.id as checkid',
                DB::raw('CASE WHEN items_count.id IS NOT NULL THEN "Yes" ELSE "No" END AS checkid'),

                'vehicles.registration',

                // 'vehicle_types.vehicle_category',
                DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'),

                // 'vehicle_types.vehicle_subcategory',
                DB::raw('CASE WHEN vehicle_types.vehicle_subcategory = "" OR vehicle_types.vehicle_subcategory IS NULL THEN "None" ELSE CONCAT(UCASE(LEFT(vehicle_subcategory, 1)), SUBSTRING(vehicle_subcategory, 2)) END AS vehicle_subcategory'),

                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_added_to_fleet, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d') as dt_added_to_fleet"),

                'vehicles.dt_mot_expiry', 'vehicles.dt_next_service_inspection', 'vehicles.dt_tacograch_calibration_due',
                'vehicle_types.vehicle_type', 'vehicle_types.manufacturer', 'vehicle_types.model',

                // 'vehicles.last_odometer_reading','vehicle_types.odometer_setting',
                DB::raw("CONCAT(FORMAT(vehicles.last_odometer_reading, 0), ' ', vehicle_types.odometer_setting) as last_odometer_reading"),

                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_registration, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d') as 'dt_registration'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_first_use_inspection, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d') as 'dt_first_use_inspection'"),
                'vehicles.contract_id',

                // 'vehicles.P11D_list_price',
                DB::raw("CASE WHEN vehicles.P11D_list_price IS NULL THEN '' ELSE CONCAT('£ ', FORMAT(vehicles.P11D_list_price, 0)) END AS P11D_list_price"),

                DB::raw("CASE WHEN vehicles.is_telematics_enabled = '1' THEN 'Yes' ELSE 'No' END as is_telematics_enabled"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.lease_expiry_date, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d') as 'lease_expiry_date'"),
                'vehicle_divisions.name as vehicle_division', 'vehicle_regions.name as vehicle_region', 'vehicle_locations.name', 'vehicle_types.engine_type', 'vehicle_types.oil_grade',

                // 'vehicle_types.co2',
                DB::raw("CASE WHEN vehicle_types.co2 IS NULL OR vehicle_types.co2 = '' THEN '' ELSE CONCAT(vehicle_types.co2, ' grams p/km') END AS co2"),

                'vehicle_types.tyre_size_drive', 'vehicle_types.tyre_size_steer', 'vehicle_types.nut_size', 'vehicle_types.re_torque', 'vehicle_types.tyre_pressure_drive', 'vehicle_types.tyre_pressure_steer', 'vehicle_types.body_builder', 'vehicle_types.fuel_type', 'vehicle_types.fuel_type',

                // 'vehicle_types.length', 'vehicle_types.width', 'vehicle_types.height',
                DB::raw("CONCAT(CASE WHEN vehicle_types.length IS NOT NULL THEN CONCAT('L-', FORMAT(vehicle_types.length, 0), ';') ELSE '' END, CASE WHEN vehicle_types.width IS NOT NULL THEN CONCAT(' W-', FORMAT(vehicle_types.width, 0), ';') ELSE '' END, CASE WHEN vehicle_types.height IS NOT NULL THEN CONCAT(' H-', FORMAT(vehicle_types.height, 0), ';') ELSE '' END) as length"),

                'vehicle_types.gross_vehicle_weight', 'vehicle_types.service_inspection_interval',
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'createdDate'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.updated_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'updatedDate'"),
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy"),
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy"),
                DB::raw("CONCAT(nominatedDriver.first_name, ' ', nominatedDriver.last_name) as nominatedDriverName"),

                // DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration"),
                'vehicles.status as '.$status1,
                DB::raw("CASE WHEN LOWER(vehicles.status) = 'vor' OR LOWER(vehicles.status) = 'vor - bodyshop' OR LOWER(vehicles.status) = 'vor - mot' OR LOWER(vehicles.status) = 'vor - accident damage' OR LOWER(vehicles.status) = 'vor - service' OR LOWER(vehicles.status) = 'vor - bodybuilder' OR LOWER(vehicles.status) = 'vor - quarantined' THEN CONCAT(vehicles.status, ' (', CASE WHEN DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) < 1 THEN 'Today' 
                    WHEN DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) > 1 THEN CONCAT(DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road), ' days') ELSE CONCAT(DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road), ' day') END, ')') ELSE vehicles.status END AS ".$status2)
            );

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
                
                $this->Database->whereIn('vehicles.id',$vehicleIds);
                // $this->Database->where('vehicles.created_by',Auth::user()->id);
            }    

        if(isset($data['showActivevehiclesOnly'])) {
            if('showActivevehiclesOnly' == true) {
                $this->Database = $this->Database
                    ->whereNotIn('status',['Archived','Archived - De-commissioned','Archived - Written off']);    
            } else {
                $this->Database = DB::table('vehicles')
                     ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                     ->whereIn('vehicles.vehicle_region_id', $userRegions);
            }
        }

        if(isset($data['showDeletedRecords'])) {
            if($data['showDeletedRecords'] == 'false') { 
                $this->Database = $this->Database->whereNull('vehicles.deleted_at');
            }
        }
        
        $this->visibleColumns = [
            'vehicles.id', 'items_count.id as checkid', 'vehicles.registration', 'vehicle_types.vehicle_category', 'vehicle_types.vehicle_subcategory', 'vehicles.status', DB::raw("DATE_FORMAT(vehicles.dt_added_to_fleet, '%d %b %Y') as 'dt_added_to_fleet'"), 'vehicles.status',
                'vehicles.dt_mot_expiry', 'vehicles.dt_next_service_inspection', 'vehicles.dt_tacograch_calibration_due', 'vehicles.is_telematics_enabled',
                'vehicle_types.vehicle_type', 'vehicle_types.manufacturer', 'vehicle_types.model', 'vehicles.last_odometer_reading','vehicle_types.odometer_setting',
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_registration, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%d %b %Y') as 'dt_registration'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.dt_first_use_inspection, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%d %b %Y') as 'dt_first_use_inspection'"),
                'vehicles.contract_id', 'vehicles.P11D_list_price',
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.lease_expiry_date, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%d %b %Y') as 'lease_expiry_date'"),
                'vehicle_divisions.name as vehicle_division', 'vehicle_regions.name as vehicle_region', 'vehicle_locations.name', 'vehicle_types.engine_type', 'vehicle_types.oil_grade', 'vehicle_types.co2', 'vehicle_types.tyre_size_drive', 'vehicle_types.tyre_size_steer', 'vehicle_types.nut_size', 'vehicle_types.re_torque', 'vehicle_types.tyre_pressure_drive', 'vehicle_types.tyre_pressure_steer', 'vehicle_types.body_builder', 'vehicle_types.fuel_type', 'vehicle_types.fuel_type', 'vehicle_types.length', 'vehicle_types.width', 'vehicle_types.height', 'vehicle_types.gross_vehicle_weight', 'vehicle_types.service_inspection_interval',
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%H:%i:%s %d %b %Y') as 'createdDate'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(vehicles.updated_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%H:%i:%s %d %b %Y') as 'updatedDate'"),
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy"),
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy"),
                DB::raw("CONCAT(nominatedDriver.first_name, ' ', nominatedDriver.last_name) as nominatedDriverName"),
                DB::raw("DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) as vorDuration")
        ];
        $this->orderBy = [['registration', 'ASC']];
    }
}
