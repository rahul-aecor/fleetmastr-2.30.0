<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\AlertNotifications;
use Carbon\Carbon;
use Auth;

class AlertNotificationsRepository extends EloquentRepositoryAbstract {

    public function __construct($request, $data = null)
    {   
        $this->Database = DB::table('alert_notifications')
        // ->select('alert_notifications.id','alerts.name', 'alerts.description', 'alerts.severity', 'alerts.type', 'alerts.source','alerts.is_active','vehicles.id as vehicle_id','vehicles.registration','users.id as user_id','users.first_name','users.last_name','alerts.id as alerts_id',DB::raw('(CASE WHEN alert_notifications.is_open = 1 "Resolved" ELSE "Open" END) as is_open'), DB::raw("DATE_FORMAT(CONVERT_TZ(alert_notifications.alert_date_time, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%H:%i:%s %d %b %Y') as 'alert_date_time'"))
            ->leftjoin('alerts', 'alert_notifications.alerts_id', '=', 'alerts.id')
            ->leftjoin('vehicles', 'alert_notifications.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('users', 'alert_notifications.user_id', '=', 'users.id');
            // ->selectRaw('CASE WHEN alert_notifications.user_id = 1 THEN "Driver Unknown" ELSE CONCAT(users.first_name, " ", users.last_name) END as user, alert_notifications.id,alerts.name, CONCAT(UPPER(SUBSTRING(alerts.severity,1,1)),LOWER(SUBSTRING(alerts.severity,2))) AS severity,
            //     CONCAT(UPPER(SUBSTRING(alerts.source,1,1)),LOWER(SUBSTRING(alerts.source,2))) AS source,
            //     CASE WHEN alerts.type = "dtc" THEN "DTC" WHEN alerts.type = "fnol" THEN "FNOL" ELSE CONCAT(UPPER(SUBSTRING(alerts.type,1,1)),LOWER(SUBSTRING(alerts.type,2))) END AS type,
            //     vehicles.registration,vehicles.id as vehicle_id, CASE WHEN alert_notifications.is_open = 1 THEN "Resolved" ELSE "Open" END as status, DATE_FORMAT(CONVERT_TZ(alert_notifications.alert_date_time, "UTC", "'.config("config-variables.format.displayTimezone").'"),"%H:%i:%s %d %b %Y") as alert_date_time');


        $decodedRequestFilters = json_decode($request['filters']);

        if(isset($request->reportDownload) && $request->reportDownload) {
            $this->Database = $this->Database->select(DB::raw('CASE WHEN alert_notifications.user_id = 1 THEN "Driver Unknown" ELSE CONCAT(users.first_name, " ", users.last_name) END as user'), 'alerts.name', DB::raw('CONCAT(UPPER(SUBSTRING(alerts.severity,1,1)),LOWER(SUBSTRING(alerts.severity,2))) AS severity'), DB::raw('CONCAT(UPPER(SUBSTRING(alerts.source,1,1)),LOWER(SUBSTRING(alerts.source,2))) AS source'), DB::raw('CASE WHEN alerts.type = "dtc" THEN "DTC" WHEN alerts.type = "fnol" THEN "FNOL" ELSE CONCAT(UPPER(SUBSTRING(alerts.type,1,1)),LOWER(SUBSTRING(alerts.type,2))) END AS type'), 'vehicles.registration', DB::raw('CASE WHEN alert_notifications.is_open = 1 THEN "Resolved" ELSE "Open" END as status'), DB::raw('DATE_FORMAT(CONVERT_TZ(alert_notifications.alert_date_time, "UTC", "'.config("config-variables.format.displayTimezone").'"), "%Y-%m-%d %H:%i:%s") as alert_date_time'));
        } else {
            $this->Database = $this->Database->select('alert_notifications.id','vehicles.id as vehicle_id', DB::raw('CASE WHEN alert_notifications.user_id = 1 THEN "Driver Unknown" ELSE CONCAT(users.first_name, " ", users.last_name) END as user'), 'alerts.name', DB::raw('CONCAT(UPPER(SUBSTRING(alerts.severity,1,1)),LOWER(SUBSTRING(alerts.severity,2))) AS severity'), DB::raw('CONCAT(UPPER(SUBSTRING(alerts.source,1,1)),LOWER(SUBSTRING(alerts.source,2))) AS source'), DB::raw('CASE WHEN alerts.type = "dtc" THEN "DTC" WHEN alerts.type = "fnol" THEN "FNOL" ELSE CONCAT(UPPER(SUBSTRING(alerts.type,1,1)),LOWER(SUBSTRING(alerts.type,2))) END AS type'), 'vehicles.registration', DB::raw('CASE WHEN alert_notifications.is_open = 1 THEN "Resolved" ELSE "Open" END as status'), DB::raw('DATE_FORMAT(CONVERT_TZ(alert_notifications.alert_date_time, "UTC", "'.config("config-variables.format.displayTimezone").'"), "%Y-%m-%d %H:%i:%s") as alert_date_time'));
        }

        if(isset($decodedRequestFilters->startDate) && isset($decodedRequestFilters->endDate)) {
            $startDate = json_decode($request['filters'])->startDate;
            $endDate = json_decode($request['filters'])->endDate;

            $this->Database = $this->Database->whereDate('alert_notifications.alert_date_time','>=', Carbon::parse($startDate)->format('Y-m-d H:i:s'))
            ->whereDate('alert_notifications.alert_date_time','<=', Carbon::parse($endDate)->format('Y-m-d H:i:s'));
        }

        if(isset($decodedRequestFilters->alertStatus)) {
            if(strtolower($decodedRequestFilters->alertStatus) == 'resolved') {
                $this->Database = $this->Database->where('alert_notifications.is_open', 1);
            } else {
                $this->Database = $this->Database->where('alert_notifications.is_open', 0);
            }
        }

        if(Auth::check()) {
            $regionFilterValue = Auth::user()->regions->lists('id')->toArray();
            $this->Database->whereIn("vehicles.vehicle_region_id", $regionFilterValue);
        }
        

        $this->visibleColumns = [
            DB::raw('CASE WHEN alert_notifications.user_id = 1 THEN "Driver Unknown" ELSE CONCAT(users.first_name, " ", users.last_name) END as user'), 'alerts.name', DB::raw('CONCAT(UPPER(SUBSTRING(alerts.severity,1,1)),LOWER(SUBSTRING(alerts.severity,2))) AS severity'), DB::raw('CONCAT(UPPER(SUBSTRING(alerts.source,1,1)),LOWER(SUBSTRING(alerts.source,2))) AS source'), DB::raw('CASE WHEN alerts.type = "dtc" THEN "DTC" WHEN alerts.type = "fnol" THEN "FNOL" ELSE CONCAT(UPPER(SUBSTRING(alerts.type,1,1)),LOWER(SUBSTRING(alerts.type,2))) END AS type'), 'vehicles.registration', DB::raw('CASE WHEN alert_notifications.is_open = 1 THEN "Resolved" ELSE "Open" END as status'), DB::raw('DATE_FORMAT(CONVERT_TZ(alert_notifications.alert_date_time, "UTC", "'.config("config-variables.format.displayTimezone").'"),"%H:%i:%s %d %b %Y") as alert_date_time')
        ];
        
        $this->orderBy = [['alert_notifications.alert_date_time', 'DESC']];
    }

    public function store($data)
    {
        $notification = new AlertNotifications();
        $notification->alerts_id = $data['alerts_id'];
        $notification->user_id = $data['user_id'];
        $notification->vehicle_id = $data['vehicle_id'];
        $notification->journey_id = $data['journey_id'];
        $notification->alert_date_time = Carbon::now();
        $notification->save();
    }
} 
