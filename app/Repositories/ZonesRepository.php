<?php
namespace App\Repositories;

use Auth;
use Carbon\Carbon;
use App\Custom\Helper\Common;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\ZoneAlerts;
use App\Models\ZoneVehicle;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class ZonesRepository extends EloquentRepositoryAbstract {

    public function __construct($request = null)
    {
        $commonHelper = new Common();

        if(isset($request->filters)) {
            $filters = json_decode($request->filters, true);
            $regionFilterForZone = isset($filters['regionFilterForZone']) ? $filters['regionFilterForZone'] : '';
            $zoneFilter = isset($filters['zoneFilter']) ? $filters['zoneFilter'] : '';
            $statusFilter = isset($filters['statusFilter']) ? $filters['statusFilter'] : '';
            $alertSettingFilter = isset($filters['alertSettingFilter']) ? $filters['alertSettingFilter'] : '';
            $searchClick = isset($filters['searchClick']) ? $filters['searchClick'] : '';
            $startDate = isset($filters['startDate']) ? $filters['startDate'] : '';
            $endDate = isset($filters['endDate']) ? $filters['endDate'] : '';
        } else {
            $data = $request ? $request->all() : null;
            $regionFilterForZone = isset($data['regionFilterForZone']) ? $data['regionFilterForZone'] : '';
            $zoneFilter = isset($data['zoneFilter']) ? $data['zoneFilter'] : '';
            $statusFilter = isset($data['statusFilter']) ? $data['statusFilter'] : '';
            $alertSettingFilter = isset($data['alertSettingFilter']) ? $data['alertSettingFilter'] : '';
            $searchClick = isset($data['searchClick']) ? $data['searchClick'] : '';
            $startDate = isset($data['startDate']) ? $data['startDate'] : '';
            $endDate = isset($data['endDate']) ? $data['endDate'] : '';
        }

        if ($startDate != '' && $endDate != '') {
            //$startDate = request()->get('startDate');
            //$endDate = request()->get('endDate');
            $startDate = $commonHelper->convertBstToUtc($startDate);
            $endDate = $commonHelper->convertBstToUtc($endDate); 
        } else {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        }

        if ($regionFilterForZone != '') {
           $regionFilterForZone = array($regionFilterForZone);
        }else{
            if(Auth::check()) {
                $regionFilterForZone = Auth::user()->regions->lists('id')->toArray();
            }
        }
        //DB::raw('(SELECT MAX(zone_alerts.start_time) FROM zone_alerts where zones.id=zone_alerts.zone_id) AS lastAlertTime')
        
        //'vehicle_regions.name AS region_name'
        $this->Database = DB::table('zones')
        ->select('zones.id','zones.name', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as createdBy"), DB::raw("CASE WHEN zone_status = '1' THEN 'Active' ELSE 'In-active' END as zone_status_label"), DB::raw("CASE WHEN zones.alert_setting = '1' THEN 'On entry' WHEN zones.alert_setting = '0' THEN 'On exit' ELSE 'On entry and exit' END as alert_setting_label"),DB::raw('(CASE WHEN (COUNT(za.zone_id)>0) THEN COUNT(za.zone_id) ELSE 0 END) as alertCount'),DB::raw("DATE_FORMAT(CONVERT_TZ((SELECT MAX(zone_alerts.start_time) FROM zone_alerts where zones.id=zone_alerts.zone_id), 'UTC', '".config('config-variables.format.displayTimezone')."'),'%Y-%m-%d %H:%i:%s') as 'lastAlertTime'"))
        ->leftJoin('users', 'zones.created_by', '=', 'users.id')
        ->leftJoin(DB::raw('(SELECT zone_alerts.*,vehicles.vehicle_region_id FROM zone_alerts,vehicles WHERE vehicles.id = zone_alerts.vehicle_id) AS za'),function($zoneAlertJoin) use($startDate,$endDate,$regionFilterForZone){
            $zoneAlertJoin->on('zones.id', '=', 'za.zone_id');
            //$zoneAlertJoin->where(DB::raw('DATE(za.start_time)'), '>=',$startDate);
            //$zoneAlertJoin->where(DB::raw('DATE(za.start_time)'), '<=',$endDate);
            //$zoneAlertJoin->whereBetween('za.start_time',[$startDate,$endDate]);
          $zoneAlertJoin->where('za.start_time', '>=',$startDate);
              $zoneAlertJoin->where('za.start_time', '<=',$endDate);
            if ($regionFilterForZone != '') {
                $zoneAlertJoin->whereIn('za.vehicle_region_id',$regionFilterForZone); 
            }
        });
        
        if ($zoneFilter != '') {
            $this->Database->where("zones.name",$zoneFilter);
        }

        if ($statusFilter != '') {
            $this->Database->where("zones.zone_status",$statusFilter);
        }

        if ($alertSettingFilter != '') {
            $this->Database->where("zones.alert_setting",$alertSettingFilter);
        }
        $this->Database->whereNull('zones.deleted_at');
        $this->Database->groupBy('zones.id');
        $this->visibleColumns = [
            'zones.id','zones.name','zone_status', 'alert_setting_label','zone_status_label','lastAlertTime','alertCount','createdBy',
            //,'vehicle_regions.name as region_name'
        ];
        $this->orderBy = [['name', 'ASC']];
    }

    public function getAllZones() {
        return Zone::groupBy('name')->lists('name', 'id')->unique()->toArray();
    }

    public function store($data){
        $zone = new Zone();
        $zone->created_by = Auth::user()->id;
        $zone->name = $data['name'];
        $zone->zone_status = isset($data['status'])?'1':'0';
        $zone->alert_setting = $data['alert_setting'];
        $zone->bounds = $data['zone_bounds'];
        $zone->save();
        //_token=sS4ST6eUUs7RR57zdzPeckjyuU6EWJe4pBigtGB7&apply_to_select=division&accessible_divisions%5B%5D=24&accessible_regions%5B%5D=62&accessible_divisions%5B%5D=1&accessible_regions%5B%5D=1&zoneRegistration=

        //$details = explode('&', $data['zoneApplyToDetails']);
        //$outout = [];
        parse_str($data['zoneApplyToDetails'], $details);
        //print_r($details);exit;
        if (isset($details['amp;apply_to_select']) && $details['amp;apply_to_select'] == 'registration') {
            $registrations = $details['amp;zoneRegList'];
            $regArray = explode(",", $registrations);
            foreach($regArray as $reg){
                $vehicleTemp = Vehicle::withTrashed()->where('registration',$reg)->first();
            // print_r($vehicleTemp);exit;
                //$dataToSave = ['zone_id'=>$zone->id,'vehicle_id'=>$vid];
                /*$zone_vehicle = new ZoneVehicle();
                $zone_vehicle->zone_id = $zone->id;
                $zone_vehicle->vehicle_id = $vehicleTemp->id;
                $zone_vehicle->save();*/
            }
        }
        if (isset($details['amp;apply_to_select']) && $details['amp;apply_to_select'] == 'division') {
            $regions = $details['amp;accessible_regions'];
            foreach($regions as $region){
                $zone_regions = new ZoneVehicleRegion();
                $zone_regions->zone_id = $zone->id;
                $zone_regions->vehicle_region_id = $region;
                //print_r($dataToSave);exit;
                $zone_regions->save();
            }
        }
        if (isset($details['amp;apply_to_select']) && $details['amp;apply_to_select'] == 'vehicle_type') {
            $types = $details['amp;vehicle_types'];
            foreach($types as $type){
                $zone_regions = new ZoneVehicleType();
                $zone_regions->zone_id = $zone->id;
                $zone_regions->vehicle_type_id = $type;
                $zone_regions->save();
            }
        }
        return $zone;
    }
    public function update($data, $id){
        $zone = Zone::findOrFail($id);
        $zone->name = $data['name'];
        $zone->zone_status = isset($data['status'])?'1':'0';
        $zone->alert_setting = $data['alert_setting'];
        $str = str_replace('{"lat":', '"', $data['zone_bounds']);
        $str = str_replace(',"lng":', ' ', $str);
        $str = str_replace('},', '", ', $str);
        $str = str_replace('}', '" ', $str);
        $data['zone_bounds'] = $str;
        $zone->bounds = $data['zone_bounds'];
        // $zone->alert_setting = $data['alert_setting'];
        $zone->save();
        return $zone;
    }

    // old data for zones checking (polygon)
    public function getActiveZones() {
        return Zone::where('zone_status', 'active')->where('alert_status', 'active')->get();
    }

    // old data for zones checking (polygon)
    public function checkJourneyExistInAlertSession($journeyid) {
        return ZoneAlertSession::with('zone')->where('journey_id', $journeyid)->where('status', 'incomplete')->whereNull('end_time')->first();
    }

    // old data for zones checking (polygon)
    public function createAlertSession($data) {
        $zoneAlertSession = new ZoneAlertSession();
        $zoneAlertSession->zone_id = $data['zone_id'];
        $zoneAlertSession->vehicle_id = $data['vehicle_id'];
        $zoneAlertSession->user_id = $data['user_id'];
        $zoneAlertSession->journey_id = $data['journey_id'];
        $zoneAlertSession->status = $data['status'];
        $zoneAlertSession->start_time = $data['start_time'];
        $zoneAlertSession->end_time = $data['end_time'];
        $zoneAlertSession->save();
        return $zoneAlertSession;
    }

    // old data for zones checking (polygon)
    public function updateEndTimeAlertSession($id, $endtime) {
        $zoneAlertSession = ZoneAlertSession::findOrFail($id);
        $zoneAlertSession->end_time = $endtime;
        $zoneAlertSession->status = 'complete';
        $zoneAlertSession->save();
        return $zoneAlertSession;
    }

    public function getAllZonesData() {
        return Zone::whereNotNull('bounds')->get();
    }

    public function checkZoneExistInZoneVehicle($zoneId, $registration) {
        return ZoneVehicle::where('zone_id', $zoneId)->where('vrn', $registration)->first();
    }
}
