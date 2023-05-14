<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class VehicleTypesRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $this->Database = DB::table('vehicle_types')->select('vehicle_types.id', 'vehicle_types.vehicle_type',
            DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'),
            DB::raw('CASE WHEN vehicle_types.vehicle_subcategory = "" OR vehicle_types.vehicle_subcategory IS NULL THEN "None" ELSE CONCAT(UCASE(LEFT(vehicle_subcategory, 1)), SUBSTRING(vehicle_subcategory, 2)) END AS vehicle_subcategory'),
            'vehicle_types.manufacturer', 'vehicle_types.model', 'vehicle_types.model_picture', 'vehicle_types.tyre_size_drive', 'vehicle_types.tyre_size_steer', 'vehicle_types.tyre_pressure_drive', 'vehicle_types.tyre_pressure_steer', 'vehicle_types.nut_size', 'vehicle_types.re_torque', 'vehicle_types.body_builder', 'vehicle_types.fuel_type', 'vehicle_types.gross_vehicle_weight','vehicle_types.length','vehicle_types.width','vehicle_types.height','vehicle_types.engine_type','vehicle_types.oil_grade','vehicle_types.profile_status','vehicle_types.deleted_at','vehicle_types.co2','vehicle_types.service_inspection_interval',
            // 'vehicle_types.vehicle_tax',

            DB::raw('CASE WHEN (
                    (
                        (
                        JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_to_date") IS NOT NULL 
                        AND REPLACE(JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_to_date"), "\"","") <> ""
                        ) AND 
                        STR_TO_DATE(REPLACE(JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_to_date"), "\"",""), "%d %M %Y") >= CURDATE()
                    ) OR
                    (
                        (
                        JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_to_date") IS NULL 
                        OR REPLACE(JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_to_date"), "\"","") = ""
                        ) AND 
                        STR_TO_DATE(REPLACE(JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_from_date"), "\"",""), "%d %M %Y") <= CURDATE()
                    )
                )
                THEN CONCAT("£", FORMAT(REPLACE(JSON_EXTRACT(JSON_EXTRACT(vehicle_tax,CONCAT("$[",JSON_LENGTH(vehicle_tax)-1,"]")), "$.cost_value"), "\"",""), 2))
                ELSE 0 END AS vehicle_tax'),

            DB::raw('CASE WHEN odometer_setting = "km" THEN "KM" WHEN odometer_setting = "miles" THEN "Miles" ELSE "" END AS odometer_setting')

        );

        // $this->Database = $this->Database->whereNull('deleted_at');


        $this->visibleColumns = [
            'vehicle_types.id', 'vehicle_types.vehicle_type', 'vehicle_types.vehicle_category', 'vehicle_types.vehicle_subcategory', 'vehicle_types.manufacturer',
            'vehicle_types.model', 'vehicle_types.model_picture', 'vehicle_types.tyre_size_drive', 'vehicle_types.tyre_size_steer', 
            'vehicle_types.tyre_pressure_drive', 'vehicle_types.tyre_pressure_steer', 'vehicle_types.nut_size', 'vehicle_types.re_torque', 
            'vehicle_types.body_builder', 'vehicle_types.fuel_type', 'vehicle_types.gross_vehicle_weight','vehicle_types.length','vehicle_types.width','vehicle_types.height','vehicle_types.engine_type','vehicle_types.oil_grade','vehicle_types.profile_status','vehicle_types.deleted_at','vehicle_types.co2','vehicle_types.service_inspection_interval','vehicle_types.vehicle_tax','vehicle_types.odometer_setting'
        ];
        //$this->whereNull('deleted_at')
        $this->orderBy = [['vehicle_types.vehicle_type', 'ASC']];
    }
}