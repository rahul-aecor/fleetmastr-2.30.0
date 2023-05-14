<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Vehicle;

class VehicleDocumentRepository extends EloquentRepositoryAbstract {

    public function __construct($data)
    {
        $vehicle = Vehicle::withTrashed()->find($data['vehicle_id']);
        $maintenanceHistoryIds = $vehicle->maintenanceHistories->pluck('id');

        $documentsData = DB::table('media')
            ->select('id', 'size', 'file_name', 'name', 'custom_properties', DB::raw('(select concat(first_name, " ", last_name) from users where id=json_extract(custom_properties, "$.createdBy"))  as user_name'), DB::raw('"Documents" as section'), DB::raw('CONCAT(REPLACE(json_extract(custom_properties, "$.caption"), "\"", ""), REPLACE(file_name, REPLACE(NAME, " ", "_"), "")) AS filename'), DB::raw('SUBSTRING_INDEX(file_name,".",-1) as extension'), DB::raw("DATE_FORMAT(CONVERT_TZ(created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'created_at'"))
            ->where('model_id', $data['vehicle_id'])
            ->where('model_type', 'App\\Models\\Vehicle')
            ->where('collection_name', 'vehicles');

        $maintenanceHistoryData = DB::table('media')
                ->select('id', 'size', 'file_name', 'name', 'custom_properties', DB::raw('(select concat(first_name, " ", last_name) from users where id=json_extract(custom_properties, "$.createdBy"))  as user_name'), DB::raw('"Maintenance" as section'), DB::raw('CONCAT(REPLACE(json_extract(custom_properties, "$.caption"), "\"", ""), REPLACE(file_name, REPLACE(NAME, " ", "_"), "")) AS filename'), DB::raw('SUBSTRING_INDEX(file_name,".",-1) as extension'), DB::raw("DATE_FORMAT(CONVERT_TZ(created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'created_at'"))
                ->whereIn('model_id', $maintenanceHistoryIds)
                ->where('model_type', 'App\\Models\\VehicleMaintenanceHistory')
                ->where('collection_name', 'vehicle_maintenance_docs');

        if(isset($data['media_id']) && $data['media_id'] && $data['media_id'] != '') {
            $mediaId = strtolower($data['media_id']);
            $documentsData = $documentsData->whereRaw('LOWER(CONCAT(REPLACE(json_extract(custom_properties, "$.caption"), "\"", ""), REPLACE(file_name, REPLACE(NAME, " ", "_"), ""))) LIKE "%'.addslashes($mediaId).'%"');
            $maintenanceHistoryData = $maintenanceHistoryData->whereRaw('LOWER(CONCAT(REPLACE(json_extract(custom_properties, "$.caption"), "\"", ""), REPLACE(file_name, REPLACE(NAME, " ", "_"), ""))) LIKE "%'.addslashes($mediaId).'%"');
        }

        if(isset($data['section'])) {
            if($data['section'] == 'Documents') {
                $this->Database = $documentsData;
            } else if($data['section'] == 'Maintenance') {
                $this->Database = $maintenanceHistoryData;
            } else {
                $this->Database = $documentsData->union($maintenanceHistoryData);
            }
        } else {
            $this->Database = $documentsData->union($maintenanceHistoryData);
        }

        $this->orderBy = [['created_at', 'DESC']];

    }
}