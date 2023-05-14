<?php
namespace App\Repositories;

use Auth;
use Carbon\Carbon;
use App\Custom\Helper\Common;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\ZoneAlerts;
use App\Models\ZoneVehicle;
use App\Repositories\ZoneVehiclesRepository;

class ZoneAlertsRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null)
    {
        $commonHelper = new Common();
        $this->Database = DB::table('zone_alerts')->select('zone_alerts.id', 'zones.name', 'zone_alerts.vrn',DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name"), DB::raw("CASE WHEN zone_alerts.is_alert = '1' THEN 'On entry' ELSE 'On exit' END as alert_type"), DB::raw("DATE_FORMAT(CONVERT_TZ(zone_alerts.start_time, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%Y-%m-%d %H:%i:%s') as 'alert_start_time'"),
            'tjd.telematics_journey_id as journeyId')
            ->leftJoin('telematics_journey_details as tjd','zone_alerts.journey_details_id','=','tjd.id')
        	->join('vehicles', 'vehicles.id', '=', 'zone_alerts.vehicle_id')
            ->join('zones', function($join){
                $join->on('zone_alerts.zone_id', '=', 'zones.id')->whereNull('zones.deleted_at');
            })
            ->leftJoin('users', function($join){
                $join->on('zone_alerts.user_id', '=', 'users.id')->whereNull('users.deleted_at');
            });
            $filters = '';
        if(isset($request->filters)) {
            $filters = json_decode($request->filters, true);
            if(!isset($filters['zoneFilter'])) {
                $filters = '';
            }
        }
        if($filters != '') {
            $zoneFilter = isset($filters['zoneFilter']) ? $filters['zoneFilter'] : '';
            $alertTypeFilter = isset($filters['alertTypeFilter']) ? $filters['alertTypeFilter'] : '';
            $regionFilterForZone = isset($filters['regionFilterForZone']) ? $filters['regionFilterForZone'] : '';
            $searchClick = isset($filters['searchClick']) ? $filters['searchClick'] : '';
            $startDate = isset($filters['startDate']) ? $filters['startDate'] : '';
            $endDate = isset($filters['endDate']) ? $filters['endDate'] : '';
        } else {
            $data = $request ? $request->all() : null;
            $zoneFilter = isset($data['zoneFilter']) ? $data['zoneFilter'] : '';
            $alertTypeFilter = isset($data['alertTypeFilter']) ? $data['alertTypeFilter'] : '';
            $regionFilterForZone = isset($data['regionFilterForZone']) ? $data['regionFilterForZone'] : '';
            $searchClick = isset($data['searchClick']) ? $data['searchClick'] : '';
            $startDate = isset($data['startDate']) ? $data['startDate'] : '';
            $endDate = isset($data['endDate']) ? $data['endDate'] : '';
        }


        if ($zoneFilter != '') {
            $this->Database->where("zones.name",$zoneFilter);
        }

        if ($alertTypeFilter != '') {
            $this->Database->where("zone_alerts.is_alert",$alertTypeFilter);
        }
        
        if ($regionFilterForZone != '') {
           $regionFilterForZone = array($regionFilterForZone);
        }else{
            if(Auth::check()) {
                $regionFilterForZone = Auth::user()->regions->lists('id')->toArray();
            }
        }

        if(!empty($regionFilterForZone)){
            $this->Database->whereIn('vehicles.vehicle_region_id',$regionFilterForZone);
        }

        if ($startDate != '' && $endDate != '') {
            //$startDate = request()->get('startDate');
            //$endDate = request()->get('endDate');
            $startDate = $commonHelper->convertBstToUtc($startDate);
            $endDate = $commonHelper->convertBstToUtc($endDate);
        } else {
            $startDate = Carbon::now()->toDateString().' 00:00:00';
            $endDate = Carbon::now()->toDateString().' 23:59:59';
        }

        //$this->Database->whereDate("zone_alerts.start_time",">=",$startDate)->whereDate("zone_alerts.start_time","<=",$endDate);
        $this->Database->whereBetween("zone_alerts.start_time",[$startDate,$endDate]);

        $this->Database->groupBy('zone_alerts.id');
      
        $this->visibleColumns = [
            'zone_alerts.id', 'zones.name', 'zones.vrn', 'users.first_name', 'users.last_name', 'alert_start_time'
        ];
        $this->orderBy = [['zone_alerts.start_time', 'desc']];
    }

    public function createAlertSession($data) {
        $zoneAlert = new ZoneAlerts();
        $zoneAlert->zone_alert_session_id = $data['zone_alert_session_id'];
        $zoneAlert->speed = $data['speed'];
        $zoneAlert->max_acceleration = $data['max_acceleration'];
        $zoneAlert->direction = $data['direction'];
        $zoneAlert->address = @$data['address'];
        $zoneAlert->latitude = $data['latitude'];
        $zoneAlert->longitude = $data['longitude'];
        $zoneAlert->save();
        return $zoneAlert;
    }

    public function getAlertCountBySessionId($sessionId) {
        return ZoneAlerts::where('zone_alert_session_id', $sessionId)->count();
    }

    public function getLatestAlertOfSessionId($sessionId) {
        return ZoneAlerts::where('zone_alert_session_id', $sessionId)->orderBy('created_at', 'desc')->select('created_at')->first();
    }

    public function createZoneAlert($data) {
        $zoneAlert = new ZoneAlerts();

        $zoneAlert->zone_id = $data['zone_id'];
        $zoneAlert->vehicle_id = $data['vehicle_id'];
        $zoneAlert->vrn = $data['vrn'];
        $zoneAlert->user_id = $data['user_id'];
        $zoneAlert->journey_details_id = $data['journey_details_id'];
        $zoneAlert->is_alert = $data['is_alert'];
        $zoneAlert->ns = $data['ns'];
        $zoneAlert->speed = $data['speed'];
        $zoneAlert->max_acceleration = $data['max_acceleration'];
        $zoneAlert->direction = $data['direction'];
        $zoneAlert->address = @$data['address'];
        $zoneAlert->latitude = $data['latitude'];
        $zoneAlert->longitude = $data['longitude'];
        $zoneAlert->start_time = $data['start_time'];
        $zoneAlert->created_at = $data['created_at'];
        $zoneAlert->updated_at = $data['updated_at'];
        $zoneAlert->save();
        return $zoneAlert;
    }

    public function createZoneVehicle($data) {
        $zoneVehiclesRepository = new ZoneVehiclesRepository();
        return $zoneVehiclesRepository->createZoneVehicle();
        /*$zoneVehicle = new ZoneVehicle();

        $zoneVehicle->zone_id = $data['zone_id'];
        $zoneVehicle->vrn = $data['vrn'];
        $zoneVehicle->save();
        return $zoneVehicle;*/
    }
}
