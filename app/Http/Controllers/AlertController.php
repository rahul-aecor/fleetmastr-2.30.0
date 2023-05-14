<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;
use Input;
use JavaScript;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Custom\Facades\GridEncoder;
use App\Repositories\AlertRepository;
use App\Repositories\AlertNotificationsRepository;
use App\Models\Alerts;
use App\Models\Vehicle;
use App\Models\AlertNotificationDays;
use App\Models\AlertNotifications;
use App\Models\AlertNotificationDaySlots;
use App\Services\AlertService;


class AlertController extends Controller
{
    public $title= 'Alert Centre';

    public function __construct(AlertService $alertService)
    {
        View::share('title', $this->title);
        $this->alertService = $alertService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $alertType = config('config-variables.alert_type');
        $alertSource = config('config-variables.alert_source');
        $alertSeverity = config('config-variables.alert_severity');
        $alertStatus = config('config-variables.alert_status');
        $alertNotification = config('config-variables.alert_notification');
        $selectedTab = isset($_COOKIE['alertShowRefTab']) ? str_replace("#", "", $_COOKIE['alertShowRefTab']) : 'alert_centres';

        $systemUserId = env('SYSTEM_USER_ID');

        $formattedStartDate = Carbon::today()->format('d/m/Y');
        $formattedEndDate = Carbon::today()->format('d/m/Y');
        $defaultDateRange = $formattedStartDate.' - '.$formattedEndDate;


        $alertData = $this->alertService->alertData($request);

        $registration=['' => '']+$alertData['vehicleRegistrationArray'];

        $vehicleRegistrationArray = collect($registration)->lists('text','id');
        $userRegions = Auth::user()->regions->lists('id')->toArray();
        $vehicleRegistration = Vehicle::select('id as id', 'registration as text')->whereIn('vehicle_region_id', $userRegions)->get();

        $alert = Alerts::get();
        $alertUser = [];
        foreach ($alert as $key => $value) {
            $userData = User::where('id', $value->created_by)->get();
            foreach ($userData as $key => $user) {
                $userRecord = [];
                $userRecord['id'] = $user->id;
                $userRecord['text'] = $user->first_name[0].' '. $user->last_name . '( ' .$user->email. ')' ;
                array_push($alertUser, $userRecord);
            }   
        }

        $userData = User::whereHas('roles', function ($query) {
                                    $query->where('name', '!=', 'Workshops');
                                    })->orderBy('email', 'asc')->get();

        $userDataArray = array();
        foreach ($userData as $key => $user) {
            if ($user->email) {
                $customString = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->email . ')';
            } else {
                $customString = substr($user->first_name, 0, 1).' '.$user->last_name . ' (' .$user->username . ')';
            }
            array_push($userDataArray, ['id'=>$user->id, 'text'=>$customString]);
        }

        JavaScript::put([
            'vehicleRegistration' => $vehicleRegistration,
            'alertUser' => $alertUser,
            'userDataArray' => $userDataArray,
            'systemUserId' => $systemUserId,
        ]);    
        return view('alertCentres.index', compact('alertType','alertSource','alertSeverity','alertStatus','selectedTab','defaultDateRange','alertNotification','vehicleRegistrationArray'));
    }

    /**
     * Return the alert centres data for the grid
     *
     * @return [type] [description]
    */
    public function anyData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new AlertRepository($request), $request->all());
    }

    /**
     * Return the alert centres data for the grid
     *
     * @return [type] [description]
    */
    public function alertNotificationData(Request $request)
    {
        return GridEncoder::encodeRequestedData(new AlertNotificationsRepository($request), $request->all());
        
    }


    public function storeAlertCenterDetail(Request $request)
    {
        $alertCenter = new Alerts();
        $alertCenter->name = $request['edit_alert_name'];
        $alertCenter->description = $request['edit_alert_description'];
        $alertCenter->severity = $request['edit_alert_severity'];
        $alertCenter->type = $request['edit_alert_type'];
        $alertCenter->source = $request['edit_alert_source'];
        $alertCenter->is_active = $request['edit_alert_status_value'] == on ? 1 : 0;
        $alertCenter->created_by = Auth::id();
        $alertCenter->updated_by = Auth::id();
        $alertCenter->save();
    }

    public function getAlertCentersData(Request $request)
    {
        $id = $request->id;
        $editAlertCentersData = Alerts::find($id);
        $editAlertNotification = AlertNotificationDays::where('alerts_id',$id)->get()->keyBy('day');
        $alertType = config('config-variables.alert_type');
        $alertSource = config('config-variables.alert_source');
        $alertSeverity = config('config-variables.alert_severity');
        return view('_partials.alertCenters.edit_alert_centers', compact('editAlertCentersData','alertType','alertSource','alertSeverity','editAlertNotification'))->render();
    }  

    public function editAlertCenterInfo(Request $request)
    {
        $daysData = $request->all();
        
        $editAlertCenter = Alerts::findOrFail($request->id);
        $editAlertCenter->description = $request['edit_alert_description'];  
        $editAlertCenter->severity = $request['edit_alert_severity'];  
        $editAlertCenter->is_active = $request['edit_alert_status_value'] == 'on' ? 1 : 0;
        // $editAlertCenter->is_notification_enabled = $request['alert_notifications'] == 'on' ? 1 : 0;
        $editAlertCenter->is_notification_enabled = 1;
        $editAlertCenter->created_by = Auth::id();
        $editAlertCenter->updated_by = Auth::id();
        $editAlertCenter->save();

        for($i = 1; $i <= 7; $i++) {
            $weekDay = $i;
            if(isset($daysData['days']['edit_alert_'.$weekDay])) {
                $isChecked = 1;
            } else {
                $isChecked = 0;
            }

            // if(isset($daysData['days_switch']['edit_alert_status_'.$weekDay])) {
            //     $switchValue = 1;
            // } else {
            //     $switchValue = 0;
            // }
            $switchValue = 1;
            $editAlertCenterArray = array(
                    'is_all_day' => $switchValue,
                    'is_on' => $isChecked
            );
            
            $alertNotification = AlertNotificationDays::updateOrCreate(
                [
                    'alerts_id' => $daysData['edit_alert_centers_id'],
                    'day' => strtolower(date('l', strtotime("Sunday +$weekDay days"))),
                ],
                $editAlertCenterArray
            );
            if(isset($daysData['timeData'][$i]) && $isChecked == 1) {
                foreach ($daysData['timeData'][$i] as $key => $data) {
                    //foreach ($value as $key => $data) {
                        AlertNotificationDaySlots::updateOrCreate(
                            [
                                'alert_notification_days_id' => $alertNotification->id,
                                'from_time' => $data['time'],
                                'is_on' => isset($data['checkboxToggle']) ? 1 : 0,
                            ]
                        );
                    //}
                }    
            }
        }   
    } 

    public function bulkAlertSetting(Request $request)
    {
        $status = $request['bulk_assign_to'];
        $severity = $request['bulk_severity'];
        $valueUpdate = [];
        if($status != '' && $severity != ''){
            $status = $request['bulk_assign_to'] == 'active' ? 1 : 0; 
            $valueUpdate = ['is_active' => $status, 'severity' => $request['bulk_severity']];
        } else if($status != '') {
            $status = $request['bulk_assign_to'] == 'active' ? 1 : 0;
            $valueUpdate = ['is_active' => $status];
        } else if($severity != '') {
            $valueUpdate = ['severity' => $request['bulk_severity']];
        }
        if(Alerts::whereIn('id',$request['bulk_array'])->update($valueUpdate)) {
                return Response('true');
        }
    }

    public function bulkAlertStatus(Request $request)
    {
        $status = $request['bulk_assign_to'] == 'resolved' ? 1 : 0;
        if(AlertNotifications::whereIn('id',$request['bulk_array'])->update(['is_open' => $status])) {
                return Response('true');
        }
    }

    public function alertNotificationShow(Request $request)
    {   
        $alertsId = $request['alertCenterId'];
        $alertData = AlertNotifications::with('alerts')->first();
        return view('_partials.alertCenters.alert_notification_show', compact('alertData'))->render();
    }

    public function storeTestAlert(Request $request)
    {
        $testAlert = $this->alertService->storeTestAlert($request->all());
        return [
            'status' => true,
            'data' => $testAlert,
        ];
    }

    public function getAlertCentreData(Request $request)
    {
        $telematicsCentreData = [];
        $payload = [];

        $alertRegistration = '';
        if (request()->has('alertRegistration')) {
            $alertRegistration = request()->get('alertRegistration');
        }
        $alertUser = '';
        if (request()->has('alertUser')) {
            $alertUser = request()->get('alertUser');
        }
        $alertType = '';
        if (request()->has('alertType')) {
            $alertType = request()->get('alertType');
        }
        $alertSource = '';
        if (request()->has('alertSource')) {
            $alertSource = request()->get('alertSource');
        }
        $alertStatus = '';
        if (request()->has('alertStatus')) {
            $alertStatus = request()->get('alertStatus');
        }
        if (request()->has('startDate') && request()->has('endDate')) {
             $startDate = Carbon::createFromFormat('Y-m-d',  request()->get('startDate')); 
             $endDate = Carbon::createFromFormat('Y-m-d',  request()->get('endDate')); 
        } else {
             $startDate = Carbon::now()->subYear(1)->firstOfMonth()->format('Y-m-d');
             $endDate = Carbon::now()->subMonth(1)->endOfMonth()->format('Y-m-d');
        }
    }
}
