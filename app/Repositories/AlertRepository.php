<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use App\Models\Alerts;
use App\Models\Vehicle;
use Carbon\Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class AlertRepository extends EloquentRepositoryAbstract {

    public function __construct($request)
    {   
        $this->Database = DB::table('alerts')->select('alerts.id', 'alerts.name', 'alerts.description', 'alerts.severity', 'alerts.type', 'alerts.source','alerts.created_at','alerts.is_active','alert_notifications.user_id',
        DB::raw("DATE_FORMAT(CONVERT_TZ(max(alert_notifications.alert_date_time), 'UTC', '".config('config-variables.format.displayTimezone')."'),'%H:%i:%s %d %b %Y') as 'alert_date_time'"),
         //DB::raw("max(alert_notifications.alert_date_time) as alert_date_time"),
            DB::raw("DATE_FORMAT(CONVERT_TZ(alerts.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),'%H:%i:%s %d %b %Y') as 'createdat'"),
            DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy"),
            DB::raw('count(alert_notifications.alerts_id) as alert_count'))
            ->leftjoin('users as createdUser', 'alerts.created_by', '=', 'createdUser.id')
            ->leftjoin('alert_notifications', 'alerts.id', '=', 'alert_notifications.alerts_id');

        $decodedRequestFilters = json_decode($request['filters']);
        if(isset($decodedRequestFilters->startDate) && isset($decodedRequestFilters->endDate)) {
            $startDate = json_decode($request['filters'])->startDate;
            $endDate = json_decode($request['filters'])->endDate;

            $this->Database = $this->Database->whereDate('alert_notifications.alert_date_time','>=', Carbon::parse($startDate)->format('Y-m-d H:i:s'))
            ->whereDate('alert_notifications.alert_date_time','<=', Carbon::parse($endDate)->format('Y-m-d H:i:s'));
        }

        $this->visibleColumns = [
            'alerts.id', 'alerts.name', 'alerts.description', 'alerts.severity', 'alerts.type', 'alerts.source','alerts.is_active','alerts.created_by','createdBy','alert_count','alert_notifications.user_id','alert_notifications.alert_date_time as alert_date_time',
        ];

        $this->Database->groupBy('alerts.id');
        
        $this->orderBy = [['alert_notifications.alert_date_time', 'DESC']];
    }

    public function storeTestAlert($data)
    {
        $testAlert = new Alerts();
        $testAlert->name = $data['testAlertName'];
        $testAlert->type = $data['testAlertType'];
        $testAlert->source = $data['testAlertSource'];
        $testAlert->code = $data['testAlertCode'];
        $testAlert->is_active = 1;
        $testAlert->vehicle_id = $data['testAlertRegistration'];
        $testAlert->user_id = Auth::id();
        $testAlert->created_by = Auth::id();
        $testAlert->updated_by = Auth::id();
        $testAlert->save();

        return $testAlert;

    }

    public function alertData()
    {
        $alert = Alerts::get();
        $vehicleRegistration = [];
        $alertUser = [];
        $data = [];
        $vehicleRegistrationArray = [];
        foreach ($alert as $key => $value) {
            $vehicle = Vehicle::where('id',$value->vehicle_id)->get();            
            foreach ($vehicle as $key => $data) {
                $vehicleRecord = [];
                $vehicleRecord['id'] = $data->id;
                $vehicleRecord['text'] = $data->registration;
                array_push($vehicleRegistration, $vehicleRecord);
            }

            $userData = User::where('id', $value->created_by)->get();
            foreach ($userData as $key => $user) {
                $userRecord = [];
                $userRecord['id'] = $user->id;
                $userRecord['text'] = $user->first_name[0].' '. $user->last_name . '( ' .$user->email. ')' ;
                array_push($alertUser, $userRecord);
            }   
        }

        $vehicle = Vehicle::get();            
        foreach ($vehicle as $key => $data) {
            $vehicleRecordArray = [];
            $vehicleRecordArray['id'] = $data->id;
            $vehicleRecordArray['text'] = $data->registration;
            array_push($vehicleRegistrationArray, $vehicleRecordArray);
        }

        return [
            'vehicleRegistration' => $vehicleRegistration,
            'alertUser' => $alertUser,
            'vehicleRegistrationArray' => $vehicleRegistrationArray,
        ];
    }
} 
