<?php
namespace App\Repositories;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\DefectHistory;
use App\Models\TemporaryImage;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\Settings;  

class DefectsRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $vorOpportunityCostPerDay = 0;
        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostJson = $fleetCost->value;
        $fleetCostData = json_decode($fleetCostJson, true);
        if(isset($fleetCostData['vor_opportunity_cost_per_day'])){
            $vorOpportunityCostPerDay = $fleetCostData['vor_opportunity_cost_per_day'] ? $fleetCostData['vor_opportunity_cost_per_day'] : 0;
        }

        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $this->Database = DB::table('defects')
            ->select('defects.id',
                // 'defects.title',
                DB::raw('CASE WHEN defects.title IS NULL THEN defect_master.defect ELSE defects.title END as defect_title'),

                // 'defects.status',
                DB::raw('CASE WHEN defects.duplicate_flag = 1 THEN CONCAT(defects.status, " (D)") ELSE defects.status END AS status'),

                'companies.name as workshop_name',
                'vehicles.registration', 'defects.vehicle_id', 'defects.workshop',
                // 'defect_master.defect',
                'defect_master.page_title',

                // 'vehicles.dt_added_to_fleet',
                DB::raw("DATE_FORMAT(vehicles.dt_added_to_fleet,'%d %b %Y') as dt_added_to_fleet"),

                'vehicles.status as vehicleStatus',

                // 'vehicle_types.vehicle_category',
                DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'),

                'vehicle_types.vehicle_type','vehicle_types.manufacturer','vehicle_types.oil_grade','vehicle_types.model','defects.description',

                // 'checks.type',
                DB::raw('CASE WHEN LOWER(checks.type) = "vehicle check" THEN "Vehicle take out" 
                        WHEN LOWER(checks.type) = "vehicle check on-call" THEN "Vehicle take out (On-call)" 
                        WHEN LOWER(checks.type) = "return check" THEN "Vehicle return"
                        WHEN LOWER(checks.type) = "defect report" AND checks.defect_report_type = "manual" THEN "Defect report (manual)"  
                        WHEN LOWER(checks.type) = "defect report" THEN "Defect report"
                        ELSE checks.type END AS type'),

                'vehicle_regions.name as vehicle_region','defects.duplicate_flag',
                // 'defects.est_completion_date',
                DB::raw('CASE WHEN defects.est_completion_date IS NULL THEN "N/A" ELSE defects.est_completion_date END est_completion_date'),

                // 'defects.cost',
                DB::raw('CASE WHEN defects.cost IS NULL THEN "N/A" ELSE CONCAT("£ ", defects.cost) END cost'),

                // 'defects.invoice_date',
                DB::raw("CASE WHEN defects.invoice_date IS NULL THEN 'N/A' ELSE DATE_FORMAT(CONVERT_TZ(defects.invoice_date, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d') END AS invoice_date"),

                // 'defects.invoice_number',
                DB::raw('CASE WHEN defects.invoice_number IS NULL THEN "N/A" ELSE defects.invoice_number END invoice_number'),

                // 'defects.estimated_defect_cost_value',
                DB::raw("CASE WHEN defects.estimated_defect_cost_value IS NULL THEN '£0.00' ELSE CONCAT('£', FORMAT(defects.estimated_defect_cost_value, 2)) END AS estimated_defect_cost_value"),

                // 'defects.actual_defect_cost_value',
                DB::raw("CASE WHEN defects.actual_defect_cost_value IS NULL THEN '£0.00' ELSE CONCAT('£', FORMAT(defects.actual_defect_cost_value, 2)) END AS actual_defect_cost_value"),

                DB::raw("CONCAT(checks.odometer_reading, ' ',vehicle_types.odometer_setting) as 'odometer_reading'"),
                'createdUser.first_name', 'createdUser.last_name',
                DB::raw("CONCAT(UPPER(SUBSTRING(createdUser.first_name,1,1)), ' ', createdUser.last_name) as createdBy "),
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as driver_name "),
                DB::raw("(case when (DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road)) then DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) * ".$vorOpportunityCostPerDay." else 0 end)  as vor_cost "),
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy "),

                DB::raw("CASE WHEN vehicle_vor_logs.dt_off_road IS NULL THEN '0' WHEN vehicle_vor_logs.dt_off_road = '' THEN '0' ELSE DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) END as vorDuration"),

                DB::raw("DATE_FORMAT(CONVERT_TZ(defects.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%H:%i:%s %d %b %Y') as 'defects_created_at'"),

                // DB::raw("DATE_FORMAT(CONVERT_TZ(defects.report_datetime, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                //     '%H:%i:%s %d %b %Y') as 'date_created_reported'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(defects.report_datetime, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%Y-%m-%d %H:%i:%s') as 'date_created_reported'"),
                // DB::raw("CONVERT_TZ(defects.updated_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'modified_date'"))
                DB::raw("CASE WHEN defects.updated_at IS NULL THEN 'N/A' WHEN DATEDIFF(CURRENT_TIMESTAMP, defects.updated_at) = 0 THEN 'Today' WHEN DATEDIFF(CURRENT_TIMESTAMP, defects.updated_at) = 1 THEN '1 day ago' ELSE CONCAT(DATEDIFF(CURRENT_TIMESTAMP, defects.updated_at), ' days ago') END AS modified_date"),

                DB::raw("CASE WHEN defects.updated_at IS NULL THEN 0 ELSE DATEDIFF(CURRENT_TIMESTAMP, defects.updated_at) END AS modified_date_sort")

            )

            ->leftJoin('users as workshop', 'defects.workshop' , '=' ,'workshop.id')
            ->leftJoin('companies', 'companies.id', '=', 'workshop.company_id')
            ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
            ->join('defect_master', 'defects.defect_master_id', '=', 'defect_master.id')
            ->join('checks', 'defects.check_id', '=', 'checks.id')
            ->join('users as createdUser', 'defects.created_by', '=', 'createdUser.id')
            ->leftjoin('users as updatedUser', 'defects.updated_by', '=', 'updatedUser.id')
            ->leftJoin('vehicle_vor_logs', function($join){
                $join->on('defects.vehicle_id', '=', 'vehicle_vor_logs.vehicle_id')->whereNull('dt_back_on_road');
            })
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            // ->whereIn('vehicles.vehicle_region', Auth::user()->accessible_regions)
            ->whereIn('vehicles.vehicle_region_id', $userRegions)
            ->whereNull('defects.deleted_at');

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
                $this->Database->where('defects.created_by',Auth::user()->id);
            } 

        $this->visibleColumns = [
            'defects.id','defects.title', 'defects.status', 'companies.name as workshop_name','defects.description',
            'vehicles.registration','defects.vehicle_id','defects.duplicate_flag',
            'defect_master.defect','defect_master.page_title','vehicles.dt_added_to_fleet', 'vehicle_types.vehicle_category','vehicle_types.vehicle_type',
            'vehicle_types.manufacturer','vehicle_types.model','vehicles.status as vehicleStatus', 'defects_created_at', 'report_datetime',
            'checks.type', 'defects.est_completion_date','defects.workshop', 'date_created_reported',
            'defects.cost','defects.invoice_date','defects.invoice_number', 'createdBy', 'updatedBy','createdUser.first_name', 'createdUser.last_name',

            'vehicle_divisions.name as vehicle_division', 'vehicle_regions.name as vehicle_region',
            'vorDuration','vor_cost','estimated_defect_cost_value','actual_defect_cost_value',
            'odometer_reading'
        ];
        $this->orderBy = [['defects.report_datetime', 'DESC']];
    }

    public function resolveDefect($data)
    {
        $comment = "";
        $company = null;
        $defect = null;

        if((!isset($data['defect_id']) || $data['defect_id'] === '') && isset($data['defect_temp_id']) && $data['defect_temp_id'] !== '') {
            $defect = Defect::where('temp_id', $data['defect_temp_id'])->first();
        } else {
            $defect = Defect::find($data['defect_id']);
        }

        if($defect->status === 'Resolved') {
            return true;
        }

        if($data['selected_workshop'] !== 'other') {
            $company = Company::find($data['selected_workshop']);
            $comment = "Workshop: " . $company->name . "\n";
        } else {
            $data['other_workshop'] = trim($data['other_workshop']);
            $comment = "Workshop: " . $data['other_workshop'] . "\n";
            $company = Company::where('name', $data['other_workshop'])->first();

            if(!$company) {
               /* $company = new Company();
                $company->name = $data['other_workshop'];
                $companyNameLength = strlen($data['other_workshop']);
                if($companyNameLength >= 3) {
                    $company->abbreviation = substr($data['other_workshop'], 0, 3);
                } else {
                    $company->abbreviation = $data['other_workshop'];
                }
                $company->user_type = 'Workshop';
                $company->save();*/

                $companyId = null;
            } else {
                $companyId = $company->id;
            }
        }

        $comment .= "Engineer: " . $data['engineer_first_name'] . " " . $data['engineer_last_name'] . ((isset($data['engineer_id']) && $data['engineer_id']) ? " (" . $data['engineer_id'] . ")" : "") . "\n";
        $comment .= "Job details: " . $data['reference_number'] . "\n\n";
        if(isset($data['additional_information']) && $data['additional_information'] !== '') {
            $comment .= "Comments: " . $data['additional_information'];
        }

        $defectHistory = new DefectHistory();
        $defectHistory->defect_id = $defect->id;
        $defectHistory->workshop_company_id = $data['selected_workshop'] === 'other' ?$companyId : $company->id;
        $defectHistory->comments = $comment;
        $defectHistory->defect_status_comment = 'set defect status to "Resolved"';
        $defectHistory->created_by = Auth::id();
        $defectHistory->updated_by = Auth::id();
        $defectHistory->report_datetime = isset($data['report_datetime']) ? $data['report_datetime'] : Carbon::now();
        $defectHistory->save();

        if(isset($data['job_details_images']) && $data['job_details_images']) {
            foreach($data['job_details_images'] as $tempImageId) {
                $tempImage = TemporaryImage::where('temp_id', $tempImageId)->first();
                if ($tempImage) {
                    $media = $tempImage->getMedia()->first();
                    if (!$media) {
                        return $this->response->error('There was an error while saving the image.', 404);
                    }
                    $media->model_id = $defectHistory->id;
                    $media->model_type = DefectHistory::class;
                    $media->save();
                } else {
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = $defectHistory->id;
                    $tempImage->model_type = DefectHistory::class;
                    $tempImage->temp_id = $tempImageId;
                    $tempImage->save();
                }
            }
        }

        if(isset($data['additional_image_array'])) {
            foreach($data['additional_image_array'] as $tempImageId) {
                $tempImage = TemporaryImage::where('temp_id', $tempImageId)->first();
                if ($tempImage) {
                    $media = $tempImage->getMedia()->first();
                    if (!$media) {
                        return $this->response->error('There was an error while saving the image.', 404);
                    }
                    $media->model_id = $defectHistory->id;
                    $media->model_type = DefectHistory::class;
                    $media->save();
                } else {
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = $defectHistory->id;
                    $tempImage->model_type = DefectHistory::class;
                    $tempImage->temp_id = $tempImageId;
                    $tempImage->save();
                }
            }
        }

        $defect->status = "Resolved";
        $defect->resolved_datetime = Carbon::now();
        $defect->save();

        $vehicle = Vehicle::with([
            'defects' => function($query){
                $query->where('status','<>','Resolved');
            }])->where('id', $defect->vehicle_id)->first();

        if(strtolower($data['status']) == "unsafetooperate") {
            $vehicle->status = "VOR";
            $vehicle->on_road = 0;
        }
        if(strtolower($data['status']) == "safetooperate") {
            $status = "Roadworthy";
            if($vehicle->defects->count() > 0) {
                $status = "Roadworthy (with defects)";
            }
            $vehicle->status = $status;
            $vehicle->on_road = 1;
        }
        $vehicle->save();
    }
}
