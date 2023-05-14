<?php
namespace App\Repositories;

use Auth;
use DB;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportDataset;
use App\Models\ReportColumn;
use App\Models\Vehicle;
use App\Models\Defect;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Models\Check;
use App\Models\VehicleMaintenanceHistory;
use App\Models\MaintenanceEvents;
use Carbon\Carbon;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class CustomReportRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $this->Database = Report::join('users as created_by', 'reports.created_by', '=', 'created_by.id')
            ->join('report_categories', 'reports.report_category_id', '=', 'report_categories.id')
            ->leftjoin(DB::raw('(SELECT report_id, filename, created_at as last_generated_date FROM report_downloads 
                    WHERE created_at IN (SELECT MAX(created_at) FROM report_downloads WHERE deleted_at IS NULL AND is_auto_download = "1" GROUP BY report_id)) as report_downloads'), 'reports.id', '=', 'report_downloads.report_id')
            ->where('is_custom_report', 1);

        if(Auth::check()) {
            $this->Database = $this->Database->where('reports.created_by', Auth::user()->id);
        }

        $this->Database = $this->Database->groupBy('reports.id')
            ->select('reports.id', 'reports.name', 'reports.description',
                'report_categories.name as category_name', 'reports.last_downloaded_at',
                DB::raw("CONVERT_TZ(reports.last_downloaded_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_downloaded_at'"),
                DB::raw("CONVERT_TZ(reports.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'created_at'"),
                'created_by.first_name', 'created_by.last_name',
                DB::raw("CONVERT_TZ(report_downloads.last_generated_date, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_generated_date'")
            );

        $this->visibleColumns = [
            'reports.id', 'reports.name', 'reports.description', 'category_name',
            DB::raw("CONVERT_TZ(reports.last_downloaded_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_downloaded_at'"),
            DB::raw("CONVERT_TZ(reports.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'created_at'"),
            'created_by.first_name', 'created_by.last_name', DB::raw("CONVERT_TZ(report_downloads.last_generated_date, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_generated_date'")
        ];

        $this->orderBy = [['reports.id', 'DESC']];
    }

    public function report($id)
    {
        return Report::find($id);
    }

    public function reportCategories()
    {
        return ReportCategory::where('name', '!=', 'Standard')->orderBy('name')->get();
    }

    public function reportStandardCategories()
    {
        return ReportCategory::where('name', 'Standard')->orderBy('name')->get();
    }

    public function exitingReportCategories()
    {
        return Report::distinct()->get(['report_category_id'])->pluck('report_category_id');
    }

    public function create($data, $oldReportData)
    {
        $report = Report::create([
            'name'=> $data['report_name'],
            'description'=> $data['report_description'],
            'report_category_id'=> $oldReportData['report_category_id'],
            'created_by'=> Auth::id(),
            'updated_by'=> Auth::id(),
        ]);

        $this->storeReportColumns($data, $report->id);

        return $report;
    }

    public function update($report, $data)
    {
        $report->name = $data['report_name'];
        $report->description = $data['report_description'];
        $report->report_category_id = $data['category_id'];
        $report->updated_by = Auth::id();
        $report->save();
        if($data['is_dataset_changed'] === 'true') {
            ReportColumn::where('report_id', $report->id)->delete();
            $this->storeReportColumns($data, $report->id);
        } else {
            if($data['dataset_order']) {
                $order = json_decode($data['dataset_order'], true);
                $this->updateDatasetColumnOrders($order, $report->id);
            }
        }

        return $report;
    }

    private function storeReportColumns($data, $reportId)
    {
        if(isset($data['field_name']) && !empty($data['field_name'])) {
            // $fields = array_keys($data['field_name']);
            $fields = $data['field_name'];
            $order = json_decode($data['dataset_order'], true);
            foreach($fields as $value) {
                $reportColumn = new ReportColumn();
                $reportColumn->report_id = $reportId;
                $reportColumn->report_dataset_id = $value;
                $reportColumn->order = isset($order[$value]) ? $order[$value] : 0;
                $reportColumn->save();
            }
        }
    }

    public function updateDatasetColumnOrders($order, $reportId)
    {
        $ids = array_keys($order);
        $reportColumns = ReportColumn::where('report_id', $reportId)->whereIn('report_dataset_id', $ids)->get();
        foreach($reportColumns as $column) {
            $column->order = isset($order[$column->report_dataset_id]) ? $order[$column->report_dataset_id] : 0;
            $column->save();
        }
    }

    public function getCategoryData()
    {
        return ReportCategory::orderBy('name')->get();
    }

    public function addReportCategory($data)
    {
        $category = new ReportCategory();
        $category->name = $data['name'];
        $category->save();
        return $category;
    }

    public function updateCategoryName($data)
    {
        $category = ReportCategory::find($data['pk']);
        if(isset($category)) {
            $category->name = $data['value'];
            $category->save();
        }
    }

    public function deleteReportCategory($id)
    {
        return ReportCategory::where('id', $id)->delete();
    }

    public function getCateoryDataset($category_id)
    {
        $categoryDataset = ReportCategory::with('dataset')->find($category_id);
        return $categoryDataset;
    }

    public function getReportDataSet()
    {
        return ReportDataset::where('is_active', true)->get();
    }

    public function getReportDataSetColumns($id, $reportColumns)
    {
        // return ReportDataset::whereHas('reportColumns', function($query) use($id, $reportColumns) {
        //         $query->whereIn('report_dataset_id', $reportColumns)
        //             ->where('report_id', $id);
        //     })->get()->sortBy('order');

        return ReportDataset::join('report_columns', 'report_dataset.id', '=', 'report_columns.report_dataset_id')
                                ->whereIn('report_dataset_id', $reportColumns)
                                ->where('report_id', $id)
                                ->orderBy('order')
                                ->get(['report_dataset.*']);


    }

    public function lastLoginReportData($data, $sortBy, $isActive = true)
    {
        if($isActive) {
            $users = User::with('company');
        } else {
            $users = User::withDisabled()->with('company')->where('is_disabled', '1');
        }
        return $users->where(function ($query) {
                   $query->where('workshops_user_flag', '=', '0')
                        ->orWhere('workshops_user_flag', '=', '2');
                })
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('role_user')
                        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                        ->whereRaw('role_user.user_id = users.id')
                        ->where('roles.name', '=', 'App version handling');
                })->orderBy($sortBy);
    }

    public function lastLoginDetails($data) {
        $userDetails = User::
                        withDisabled()
                        ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                        ->where(function ($query) {
                           $query->where('workshops_user_flag', '=', '0')
                                ->orWhere('workshops_user_flag', '=', '2');
                        })
                        ->whereNotExists(function($query) {
                            $query->select(DB::raw(1))
                                ->from('role_user')
                                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                                ->whereRaw('role_user.user_id = users.id')
                                ->where('roles.name', '=', 'App version handling');
                        });

        if(isset($data['accessible_regions'])) {
            $userDetails = $userDetails->where(function ($query) use ($data) {
                                $query->whereNull('users.user_region_id');
                                $query->orWhereIn('users.user_region_id', $data['accessible_regions']);
                            });
        }

        $userDetails = $userDetails->selectRaw('users.first_name, users.last_name, companies.name as company_id, user_divisions.name as user_division_id, user_regions.name as user_region_id, users.username, users.email, users.mobile, 
            CASE WHEN roles.id = "1" THEN "Super admin"
            WHEN roles.id = "14" THEN "User information only"
            WHEN roles.id = "8" THEN "App access only"
            ELSE roles.name
            END as roles, users.last_login, CASE WHEN users.is_disabled = 1 THEN "Yes" ELSE "No" END as is_disabled');

        return $userDetails;

    }

    public function activityReportData($data)
    {
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        return \DB::table('users')
                    ->select(\DB::raw('users.first_name, users.last_name, users.email, user_regions.name as region,
                        (SELECT COUNT(checks.id) FROM checks WHERE checks.created_by = users.id AND checks.type = "Vehicle Check" AND checks.created_at >= "' . $startDate . '" AND checks.created_at <= "' . $endDate . '") AS totalVehicleCheck,
                        (SELECT COUNT(checks.id) FROM checks WHERE checks.created_by = users.id AND checks.type = "Return Check" AND checks.created_at >= "' . $startDate . '" AND checks.created_at <= "' . $endDate . '") AS totalReturnCheck'))
                    ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                    ->whereNull('users.deleted_at')
                    ->orderBy('totalVehicleCheck', 'DESC')
                    ->orderBy('totalReturnCheck', 'DESC');
    }

    public function vorReportData($data) {
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        return Defect::
                with('history')
                ->join('vehicles', 'vehicle_id', '=', 'vehicles.id')
                // ->whereBetween('report_datetime',[$startDate,$endDate])
                ->whereDate('report_datetime', '>=', $startDate)
                ->whereDate('report_datetime', '<=', $endDate)
                ->whereHas('vehicle', function ($query) use($data) {
                    $query->whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined']);
                    // ->whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                });
    }

    public function vorReportDetails($data) {
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $defects = Defect::
                    leftjoin('defect_history', 'defects.id', '=', 'defect_history.defect_id')
                    ->leftjoin('defect_master','defects.defect_master_id','=','defect_master.id')
                    ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
                    ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                    ->leftjoin('vehicle_locations','vehicles.vehicle_location_id','=','vehicle_locations.id')
                    ->leftjoin('vehicle_repair_locations','vehicles.vehicle_repair_location_id','=','vehicle_repair_locations.id')
                    ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id','=','vehicle_divisions.id')
                    ->leftjoin('vehicle_regions','vehicles.vehicle_region_id','=','vehicle_regions.id')
                    ->leftjoin('vehicle_vor_logs','vehicles.id','=','vehicle_vor_logs.vehicle_id')
                    // ->whereBetween('defects.report_datetime',[$startDate,$endDate])
                    ->whereDate('defects.report_datetime', '>=', $startDate)
                    ->whereDate('defects.report_datetime', '<=', $endDate)
                    ->whereHas('vehicle', function ($query) use($data) {
                        $query->whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined']);
                    });

        if(isset($data['accessible_regions'])) {
            $defects = $defects->whereHas('vehicle', function ($query) use($data) {
                        $query->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
                    });
        }

        $defects = $defects->orderBy('defects.report_datetime')->orderBy('defects.vehicle_id');

        $defects = $defects->selectRaw('vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, CASE WHEN vehicle_types.manufacturer = "hgv" THEN "HGV" ELSE "Non-HGV" END as hgv_non_hgv, vehicle_types.vehicle_type as vehicle_type_id, vehicle_types.manufacturer, vehicle_types.model, CASE WHEN vehicles.vehicle_location_id = NULL THEN "" ELSE vehicle_locations.name END as vehicle_location_id, CASE WHEN vehicles.vehicle_repair_location_id = NULL THEN "" ELSE vehicle_repair_locations.name END as repair_location_name, CASE WHEN vehicle_vor_logs.dt_off_road = NULL THEN "" ELSE vehicle_vor_logs.dt_off_road END as dated_vor_d, CASE WHEN vehicle_vor_logs.dt_off_road = NULL THEN "N/A" ELSE DATEDIFF(NOW(),vehicle_vor_logs.dt_off_road) END as vor_duration_days, vehicles.status, CASE WHEN defects.defect_master_id = NULL THEN "" ELSE defect_master.page_title END as page_title, CASE WHEN defects.defect_master_id = NULL THEN "" ELSE defect_master.defect END as defect, defects.id, defects.est_completion_date, CASE WHEN defect_history.report_datetime = NULL THEN "" ELSE defect_history.report_datetime END as last_comment_date, CASE WHEN defect_history.comments = NULL THEN "" ELSE defect_history.comments END as last_comment');

        return $defects;
    }

    public function defectReportData($data) {
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        return Defect::with('history')
                    ->whereDate('report_datetime', '>=', $startDate)
                    ->whereDate('report_datetime', '<=', $endDate);
    }

    public function defectReportDetails($data, $reportDownload) {
        // echo "<pre>"; print_r($reportDownload->toArray());  echo "</pre>";
        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        // echo "<pre>"; print_r($response);  echo "</pre>";
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $defects = Defect::
                    leftjoin('defect_history', 'defects.id', '=', 'defect_history.defect_id')
                    ->leftjoin('defect_master','defects.defect_master_id','=','defect_master.id')
                    ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
                    ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                    ->leftjoin('vehicle_locations','vehicles.vehicle_location_id','=','vehicle_locations.id')
                    ->leftjoin('vehicle_repair_locations','vehicles.vehicle_repair_location_id','=','vehicle_repair_locations.id')
                    ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id','=','vehicle_divisions.id')
                    ->leftjoin('vehicle_regions','vehicles.vehicle_region_id','=','vehicle_regions.id')
                    // ->whereBetween('defects.report_datetime',[$startDate,$endDate]);
                    ->whereDate('defects.report_datetime', '>=', $startDate)
                    ->whereDate('defects.report_datetime', '<=', $endDate);

        if(isset($data['accessible_regions'])) {
            $defects = $defects->whereHas('vehicle', function($q) use($data) {
                            $q->whereIn('vehicle_region_id', $data['accessible_regions']);
                        });
        }

        $defects = $defects->orderBy('defects.report_datetime');

        $response = setDataCoumns($response);
        // echo "<pre>"; print_r($response);  echo "</pre>";
        $visibleColumns = implode(",", $response);
        // echo "<pre>"; print_r($visibleColumns);  echo "</pre>";

        $defects = $defects->selectRaw($visibleColumns);

        // $defects = $defects->selectRaw('vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, CASE WHEN vehicle_types.manufacturer = "hgv" THEN "HGV" ELSE "Non-HGV" END as hgv_non_hgv, vehicle_types.vehicle_type as vehicle_type_id, vehicle_types.manufacturer, vehicle_types.model, CASE WHEN vehicles.vehicle_location_id = NULL THEN "" ELSE vehicle_locations.name END as vehicle_location_id, CASE WHEN vehicles.vehicle_repair_location_id = NULL THEN "" ELSE vehicle_repair_locations.name END as repair_location_name, DATE_FORMAT(CONVERT_TZ(defects.report_datetime, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d-%M-%Y") as report_datetime, defects.id, vehicles.last_odometer_reading, CASE WHEN defects.defect_master_id = NULL THEN "" ELSE defect_master.page_title END as page_title, CASE WHEN defects.defect_master_id = NULL THEN "" ELSE defect_master.defect END as defect, vehicles.status, defects.status as defect_status, CASE WHEN defect_history.report_datetime = NULL THEN "" ELSE defect_history.report_datetime END as last_comment_date, CASE WHEN defect_history.comments = NULL THEN "" ELSE defect_history.comments END as last_comment');

        return $defects;
    }

    public function drivingEventData($data, $ns) {
        $drivingEvents = DB::table(DB::raw('telematics_journey_details force index (telematics_journey_details_time_index)'))
                ->join('telematics_journeys', 'telematics_journey_id', '=', 'telematics_journeys.id')
                ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id','=','vehicle_divisions.id')
                ->leftjoin('vehicle_regions','vehicles.vehicle_region_id','=','vehicle_regions.id')
                ->whereNull('telematics_journey_details.deleted_at')
                ->where('telematics_journey_details.time', '>=', $data['date_from']." 00:00:00")
                ->where('telematics_journey_details.time', '<=', $data['date_to']." 23:59:59")
                ->whereIn('telematics_journey_details.ns', $ns);

        if(isset($data['accessible_regions'])) {
            $drivingEvents = $drivingEvents->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        $drivingEvents = $drivingEvents->selectRaw('CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d-%M-%Y") as incident_date, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i") as incident_time, telematics_journey_details.ns, concat(street," ",town," ",post_code) as street');

        return $drivingEvents;
    }

    public function speedingData($data, $ns) {
        $speedingDetails = DB::table(DB::raw('telematics_journey_details force index (telematics_journey_details_time_index)'))
                ->join('telematics_journeys', 'telematics_journey_id', '=', 'telematics_journeys.id')
                ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('vehicle_divisions','vehicles.vehicle_division_id','=','vehicle_divisions.id')
                ->leftJoin('vehicle_regions','vehicles.vehicle_region_id','=','vehicle_regions.id')
                ->whereNull('telematics_journey_details.deleted_at')
                ->where('telematics_journey_details.time', '>=', $data['date_from']." 00:00:00")
                ->where('telematics_journey_details.time', '<=', $data['date_to']." 23:59:59")
                ->where('telematics_journey_details.ns', $ns);

        if(isset($data['accessible_regions'])) {
            $speedingDetails = $speedingDetails->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        $speedingDetails = $speedingDetails->selectRaw('CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, vehicles.status, speed, street_speed, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d-%M-%Y") as incident_date, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i") as incident_time, telematics_journey_details.ns, concat(street," ",town," ",post_code) as street');

        return $speedingDetails;
    }

    public function getJourneyDataForReport($data)
    {
        $journeys = DB::table(DB::raw('telematics_journeys force index (telematics_journeys_start_time_index)'))
                        ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                        ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                        ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                        ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                        ->whereNull('telematics_journeys.deleted_at')
                        ->where('start_time', '>=', $data['date_from']." 00:00:00")
                        ->where('start_time', '<=', $data['date_to']." 23:59:59")
                        ->where('vehicles.is_telematics_enabled', '=', '1');
                        // ->where('telematics_journeys.fuel', '!=', 0)
                        // ->where('telematics_journeys.gps_distance', '!=', 0)
                        // ->where('telematics_journeys.gps_idle_duration', '!=', 0);

        if(isset($data['accessible_regions'])) {
            $journeys = $journeys->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        return $journeys;
    }

    public function getJourneyDetails($data) {

        $journeys = $this->getJourneyDataForReport($data);

        $journeys = $journeys->selectRaw('vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i:%s %d %b %Y") as start_time,
            DATE_FORMAT(CONVERT_TZ(end_time, "UTC","'.config('config-variables.format.displayTimezone').'"),"%H:%i:%s %d %b %Y") AS end_time, telematics_journeys.engine_duration, telematics_journeys.gps_distance, telematics_journeys.id as start_location, concat(end_street," ",end_town," ",end_post_code) as end_location, telematics_journeys.incident_count, telematics_journeys.fuel, telematics_journeys.fuel as mpg_actual, vehicles.vehiclefuelsum as mpg_expected, telematics_journeys.co2, vehicles.vehiclefuelsum, vehicles.vehicledistancesum');

        return $journeys;
    }

    public function getFuelUsageAndEmissionDetails($data) {

        $journeys = $this->getJourneyDataForReport($data);

        $journeys = $journeys->selectRaw('vehicles.registration, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id, SUM(telematics_journeys.engine_duration) as engine_duration, SUM(telematics_journeys.gps_distance) as gps_distance, SUM(telematics_journeys.gps_distance) as actual_driving_time, sum(telematics_journeys.gps_idle_duration) as gps_idle_duration, telematics_journeys.fuel, telematics_journeys.fuel as mpg_actual, vehicles.vehiclefuelsum as mpg_expected, telematics_journeys.co2, vehicles.vehiclefuelsum, vehicles.vehicledistancesum')
        ->groupBy('telematics_journeys.vehicle_id');

        return $journeys;
    }

    public function vehicleAndUserDefectReportData($data, $filter) {
        $defectsData = Defect::
                        join('defect_master', 'defects.defect_master_id', '=', 'defect_master.id')
                        ->join('checks', 'check_id', '=', 'checks.id')
                        ->join('users', 'checks.created_by', '=', 'users.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
                        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                        ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id')
                        // ->whereBetween('defects.report_datetime',[$data['date_from'],$data['date_to']]);
                        ->whereDate('defects.report_datetime', '>=', $data['date_from'])
                        ->whereDate('defects.report_datetime', '<=', $data['date_to']);

        if(isset($data['accessible_regions']) && $filter == 'vehicle') {
            $defectsData = $defectsData->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        } elseif(isset($data['accessible_regions']) && $filter == 'user') {
            $defectsData = $defectsData->whereIn('users.user_region_id', $data['accessible_regions']);
        }

        return $defectsData;
    }

    public function vehicleAndUserDefectData($data, $filter, $reportDownload) {

        $defectsData = $this->vehicleAndUserDefectReportData($data, $filter);

        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();

        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);

        $defectsData = $defectsData->selectRaw($visibleColumns);

        return $defectsData;
    }

    public function vehicleAndUserIncidentReportData($data, $incidentTypes, $filter) {
        $incidentEvents = DB::table(DB::raw('telematics_journey_details force index (telematics_journey_details_time_index)'))
                            ->join('telematics_journeys', 'telematics_journey_id', '=', 'telematics_journeys.id')
                            ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                            ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                            ->leftJoin('user_regions','user_regions.id','=','users.user_region_id')
                            ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                            ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                            ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id')
                            ->whereNull('telematics_journey_details.deleted_at')
                            ->where('telematics_journey_details.time', '>=', $data['date_from']." 00:00:00")
                            ->where('telematics_journey_details.time', '<=', $data['date_to']." 23:59:59")
                            ->where('ns', '!=', 'tm8.dfb2.spd')
                            ->where('vehicles.is_telematics_enabled','1')
                            ->whereIn('ns', $incidentTypes);

        if(isset($data['accessible_regions']) && $filter == 'vehicle') {
            $incidentEvents = $incidentEvents->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        } else if (isset($data['accessible_regions']) && $filter == 'user') {
            $incidentEvents = $incidentEvents->whereIn('users.user_region_id', $data['accessible_regions']);
        }

        return $incidentEvents;
    }

    public function vehicleAndUserIncidentData($data, $incidentTypes, $filter, $reportDownload) {

        $incidentEvents = $this->vehicleAndUserIncidentReportData($data, $incidentTypes, $filter);

        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();

        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);

        $incidentEvents = $incidentEvents->selectRaw($visibleColumns);

        return $incidentEvents;
    }

    public function vehicleAndUserCheckReportData($data, $filter) {
        $checkData =  Check::
                join('users', 'checks.created_by', '=', 'users.id')
                ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                ->join('vehicles', 'checks.vehicle_id', '=', 'vehicles.id')
                ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id')
                ->where('checks.type', '<>', 'Defect Report')
                ->whereDate('checks.report_datetime', '>=', $data['date_from'])
                ->whereDate('checks.report_datetime', '<=', $data['date_to']);

        if(isset($data['accessible_regions']) && $filter == 'vehicle') {
            $checkData = $checkData->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        } elseif(isset($data['accessible_regions']) && $filter == 'user') {
            $checkData = $checkData->whereIn('users.user_region_id', $data['accessible_regions']);
        }

        return $checkData;
    }

    public function vehicleAndUserCheckData($data, $filter, $reportDownload)
    {
        $checkData = $this->vehicleAndUserCheckReportData($data, $filter);

        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();

        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);

        $checkData = $checkData->selectRaw($visibleColumns);

        return $checkData;
    }

    public function vehicleAndUserJourneyReportData($data , $filter, $visibleColumns) {
        // $data['date_from'] = '2022-06-01';
        // $data['date_to'] = '2022-06-02';
        $journeyData = DB::table(DB::raw('telematics_journeys force index (telematics_journeys_start_time_index)'))
                            ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                            ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                            ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                            ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                            ->whereNull('telematics_journeys.deleted_at')
                            ->where('start_time', '>=', $data['date_from']." 00:00:00")
                            ->where('start_time', '<=', $data['date_to']." 23:59:59")
                            ->where('vehicles.is_telematics_enabled','=','1')
                            ->selectRaw($visibleColumns);
                            // ->where('telematics_journeys.fuel', '!=', 0)
                            // ->where('telematics_journeys.gps_distance', '!=', 0)
                            // ->where('telematics_journeys.gps_idle_duration', '!=', 0);

        if(isset($data['accessible_regions']) && $filter == 'vehicle') {
            $journeyData = $journeyData->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        } elseif(isset($data['accessible_regions']) && $filter == 'user') {
            $journeyData = $journeyData->whereIn('users.user_region_id', $data['accessible_regions']);
        }

        return $journeyData;
    }

    public function vehicleAndUserJourneyData($data, $filter, $reportDownload)
    {
        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);

        $journeyData = $this->vehicleAndUserJourneyReportData($data, $filter, $visibleColumns);
        // $journeyData = $journeyData->selectRaw($visibleColumns);

        return $journeyData;
    }

    public function vehicleProfileReport($data) {

        $vehicleProfile = Vehicle::leftJoin('users', 'vehicles.nominated_driver', '=', 'users.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                        ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id');
                        // ->whereBetween('vehicles.created_at',[$data['date_from'],$data['date_to']]);
                        // ->whereDate('vehicles.created_at', '>=', $data['date_from'])
                        // ->whereDate('vehicles.created_at', '<=', $data['date_to']);

        if(isset($data['accessible_regions'])) {
            $vehicleProfile = $vehicleProfile->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        return $vehicleProfile;

    }

    public function vehicleProfileData($data, $reportDownload) {

        $vehicleProfile = $this->vehicleProfileReport($data);

        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();

        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);

        $vehicleProfile = $vehicleProfile->selectRaw($visibleColumns);

        return $vehicleProfile;
    }

    public function userDetailsReport($data) {
        $userData = User::leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->leftJoin('vehicles', 'users.id', '=', 'vehicles.created_by')
                        ->leftJoin('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                        ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id');
                        // ->whereBetween('users.created_at',[$data['date_from'],$data['date_to']]);
                        // ->whereDate('users.created_at', '>=', $data['date_from'])
                        // ->whereDate('users.created_at', '<=', $data['date_to']);

        if(isset($data['accessible_regions'])) {
            $userData = $userData->whereIn('users.user_region_id', $data['accessible_regions']);
        }

        $userData = $userData->selectRaw('users.email, CASE WHEN users.id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN users.id = 1 THEN "Unknown" ELSE users.last_name END as last_name, DATE_FORMAT(CONVERT_TZ(users.created_at, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as user_created_at, users.engineer_id, users.mobile, users.landline, companies.name as company_id, CASE WHEN users.driver_tag = "dallas_key" THEN users.driver_tag_key ELSE null END as dallas_key, users.imei, users.base_location, user_divisions.name as user_division_id, user_regions.name as user_region_id, vehicles.registration, vehicle_types.vehicle_type as vehicle_type_id, vehicle_types.vehicle_category, vehicle_types.vehicle_subcategory, vehicle_divisions.name as vehicle_division_id, vehicle_regions.name as vehicle_region_id');

        return $userData;
    }

    public function vehiclePlanningReportData($data, $visibleColumns, $sortBy) {
        $planningData = Vehicle::join('users', 'vehicles.created_by', '=', 'users.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicles.vehicle_region_id')
                        ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicles.vehicle_division_id')
                        ->selectRaw($visibleColumns)
                        ->orderBy($sortBy);

        if(isset($data['accessible_regions'])) {
            $planningData = $planningData->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        $planningData = $planningData->get()->keyBy('registration');

        $annualServiceData = Vehicle::whereDate('vehicles.dt_annual_service_inspection', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_annual_service_inspection', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_annual_service_inspection, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Annual Service" as service_type')
                                    ->orderBy($sortBy);

        $nextCompressorServiceData = Vehicle::whereDate('vehicles.next_compressor_service', '>=', $data['date_from'])
                                    ->whereDate('vehicles.next_compressor_service', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.next_compressor_service, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Compressor Service" as service_type')
                                    ->orderBy($sortBy);

        $nextInvertorServiceData = Vehicle::whereDate('vehicles.next_invertor_service_date', '>=', $data['date_from'])
                                    ->whereDate('vehicles.next_invertor_service_date', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.next_invertor_service_date, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Invertor Service" as service_type')
                                    ->orderBy($sortBy);

        $dtLolerTestDueData = Vehicle::whereDate('vehicles.dt_loler_test_due', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_loler_test_due', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_loler_test_due, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "LOLER Test" as service_type')
                                    ->orderBy($sortBy);

        $dtMotExpiryData = Vehicle::whereDate('vehicles.dt_mot_expiry', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_mot_expiry', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_mot_expiry, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "MOT" as service_type')
                                    ->orderBy($sortBy);

        $firstPmiDateData = Vehicle::whereDate('vehicles.first_pmi_date', '>=', $data['date_from'])
                                    ->whereDate('vehicles.first_pmi_date', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.first_pmi_date, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "First PMI" as service_type')
                                    ->orderBy($sortBy);

        $nextPmiDateData = Vehicle::whereDate('vehicles.next_pmi_date', '>=', $data['date_from'])
                                    ->whereDate('vehicles.next_pmi_date', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.next_pmi_date, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Next PMI" as service_type')
                                    ->orderBy($sortBy);

        $nextPtoServiceDateData = Vehicle::whereDate('vehicles.next_pto_service_date', '>=', $data['date_from'])
                                    ->whereDate('vehicles.next_pto_service_date', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.next_pto_service_date, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "PTO Service" as service_type')
                                    ->orderBy($sortBy);

        $dtNextServiceInspectionData = Vehicle::whereDate('vehicles.dt_next_service_inspection', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_next_service_inspection', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_next_service_inspection, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Service" as service_type')
                                    ->orderBy($sortBy);

        $dtTacograchCalibrationDueData = Vehicle::whereDate('vehicles.dt_tacograch_calibration_due', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_tacograch_calibration_due', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_tacograch_calibration_due, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Tacho Service" as service_type')
                                    ->orderBy($sortBy);

        $dtTaxExpiryData = Vehicle::whereDate('vehicles.dt_tax_expiry', '>=', $data['date_from'])
                                    ->whereDate('vehicles.dt_tax_expiry', '<=', $data['date_to'])
                                    ->selectRaw('registration, DATE_FORMAT(CONVERT_TZ(vehicles.dt_tax_expiry, "UTC", "Europe/London"),"%d %b %Y") AS service_date, "Tax" as service_type')
                                    ->orderBy($sortBy);

        $serviceData = $annualServiceData->union($nextCompressorServiceData)
                                        ->union($nextInvertorServiceData)
                                        ->union($dtLolerTestDueData)
                                        ->union($dtMotExpiryData)
                                        ->union($firstPmiDateData)
                                        ->union($nextPmiDateData)
                                        ->union($nextPtoServiceDateData)
                                        ->union($dtNextServiceInspectionData)
                                        ->union($dtTacograchCalibrationDueData)
                                        ->union($dtTaxExpiryData)
                                        // ->orderBy('service_date')
                                        ->get();

        foreach($serviceData as $id => $service) {
            if(isset($planningData[$service->registration])) {
                $result = array_merge($planningData[$service->registration]->toArray(), $serviceData[$id]->toArray());
                $serviceData[$id] = collect($result);
            }
        }

        return $serviceData;
    }

    public function vehiclePlanningData($data, $reportDownload, $sortBy)
    {
        $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        $response = setDataCoumns($response);
        $visibleColumns = implode(",", $response);
        $planningData = $this->vehiclePlanningReportData($data, $visibleColumns, $sortBy);

        return $planningData;
    }

    public function vehicleWeeklyMaintananceReport($data, $events, $sortBy) {

        $startDt = Carbon::parse($data['date_from']);
        $endDt = Carbon::parse($data['date_to']);


        $vehiclesForServiceInspectionDistance = Vehicle::join('vehicle_maintenance_history','vehicles.id', '=', 'vehicle_maintenance_history.vehicle_id')
                    ->whereRaw('vehicles.next_service_inspection_distance = vehicle_maintenance_history.event_planned_distance')
                    ->where('vehicle_maintenance_history.event_status', '=', 'Incomplete')
                    ->where('event_plan_date', '>=', $startDt)
                    ->where('event_plan_date', '<=', $endDt)
                    ->select('vehicles.*', 'vehicle_maintenance_history.event_plan_date')
                    ->get();

        $vehicles = Vehicle::with('type')->where(function($query) use ($events, $startDt, $endDt) {
            foreach ($events as $event => $values) {
                $column = $values['column'];
                $query = $query->orWhere(function($q) use ($column, $startDt, $endDt) {
                    $q->whereNotNull($column)
                        ->where($column, '>=', $startDt)
                        ->where($column, '<=', $endDt);
                });
            }
        });

        if(isset($data['accessible_regions'])) {
            $vehicles = $vehicles->whereIn('vehicle_region_id', $data['accessible_regions']);
        }

        $vehicles = $vehicles->orderBy($sortBy)->get();

        $pmiEventId = MaintenanceEvents::where('slug', 'preventative_maintenance_inspection')->first()->id;
        $i = 0;
        foreach($vehicles as $vehicleKey => $vehicle) {
            $serviceInterval = $vehicle->type->pmi_interval;
            $event = 'first_pmi_date';
            $interval = \DateInterval::createFromDateString($serviceInterval);
            $nextPmiDate = Carbon::parse($vehicle->next_pmi_date);
            $eventDate = $nextPmiDate->sub($interval);
            $year = $eventDate->format('Y');
            $month = $eventDate->format('n');
            $isUpdatedNextPmi = 0;
            $futurePmiEvents = collect();
            while($endDt->diffInDays($eventDate, false) <= 0){
                if ($isUpdatedNextPmi == 0 && $eventDate->gte($nextPmiDate)) {
                    $eventDate = $nextPmiDate;
                    $isUpdatedNextPmi = 1;
                }
                $evDate = clone $eventDate;
                $dt = $evDate->format('F Y');
                $nxtServiceDt = $evDate->format('d M Y');

                $checkEventIsCompleted = $vehicle->maintenanceHistories()->where('event_type_id', $pmiEventId)
                                                                    ->where('event_status', 'Incomplete')
                                                                    ->orderBy('event_plan_date', 'desc')
                                                                    ->first(['event_plan_date']);

                if(!isset($checkEventIsCompleted) || $evDate->gte(Carbon::parse($checkEventIsCompleted->event_plan_date))) {
                    //If conditions points to #6550
                    if(isset($maintenanceList[$dt])) {
                        $maintenanceList[$dt] = array_values($maintenanceList[$dt]);
                        if (array_search($eventName, array_column($maintenanceList[$dt], 'event')) !== FALSE) {
                            $eventKey = array_search($eventName, array_column($maintenanceList[$dt], 'event'));
                            unset($maintenanceList[$dt][$eventKey]);
                            $maintenanceList[$dt] = array_values($maintenanceList[$dt]);
                        }
                    }
                    $futurePmiEvents->push($nxtServiceDt);
                }
                $eventDate = $eventDate->add($interval);
                $year = $evDate->format('Y');
                $i++;
            }
            $vehicles[$vehicleKey]->futurePmiEvents = $futurePmiEvents;
        }

        return [$vehicles, $vehiclesForServiceInspectionDistance];
    }

    public function getReportDataSetFieldByTitle($title)
    {
        return ReportDataset::where('title', $title)->first()->field_name;
    }

    public function pmiPeformanceReport($data, $pmiEventId) {
        $vehicles = VehicleMaintenanceHistory::join('vehicles', 'vehicle_maintenance_history.vehicle_id', '=', 'vehicles.id')
                        ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
                        ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                        ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                        ->leftjoin('vehicle_locations','vehicles.vehicle_location_id', '=', 'vehicle_locations.id')
                        ->leftjoin('vehicle_repair_locations','vehicles.vehicle_repair_location_id', '=', 'vehicle_repair_locations.id')
                        // ->whereIn('vehicles.id', $vehicleIds)
                        ->where('event_plan_date', '>=', $data['date_from'])
                        ->where('event_plan_date', '<=', $data['date_to'])
                        ->where('event_type_id', $pmiEventId);

        if(isset($data['accessible_regions'])) {
            $vehicles = $vehicles->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }

        $vehicles = $vehicles->selectRaw('vehicle_maintenance_history.*, vehicles.registration,vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category, CASE WHEN vehicle_types.vehicle_subcategory = "" OR vehicle_types.vehicle_subcategory IS NULL THEN "None" ELSE CONCAT(UCASE(LEFT(vehicle_subcategory, 1)), SUBSTRING(vehicle_subcategory, 2)) END AS vehicle_subcategory, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, vehicle_locations.name as location_name, vehicle_repair_locations.name as repair_location_name');

        return $vehicles;
    }
}
