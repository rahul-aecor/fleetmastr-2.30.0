<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use View;
use JavaScript;
use App\Models\Check;
use App\Http\Requests;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\Role;
use App\Models\Permission;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\ColumnManagements;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\VehicleService;

class DashboardController extends Controller
{
    public $title= 'Dashboard';

    public function __construct(VehicleService $vehicleService) {
        View::share ( 'title', $this->title );
        $this->vehicleService = $vehicleService;
    }
    /**
     * Display the telematics summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function telematicstry(Request $request)
    {
        //View::share ( 'title', $this->title );
        return view('dashboard.telematicstry', compact('vehicleRegions', 'periods', 'inspection_fields', 'expiry_fields', 'loggedInUser'));
    }
    /**
     * Display the dashboard summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        if($authUser->isWorkshopManager()){
            return redirect('/defects');
        }
        if($authUser->isUserInformationOnly()){
            return redirect('/checks');
        }

        // select period for dashboard fleet costs
        $columnManagementData = ColumnManagements::where('user_id', $authUser->id)->where('section','dashboardFleetCost')->first();
        if(!empty($columnManagementData)) {
            $selectPeriod = $columnManagementData['data'];
        } else {
            $selectPeriod = '';
        }

        $userRoles = array();
        $role_id = Role::where('name','Dashboard (costs)')->first();
        $fleetCost_id = $role_id['id'];

        $periods = config('config-variables.dashboard.periods');
        $inspection_fields = config('config-variables.dashboard.inspection_fields');
        $expiry_fields = config('config-variables.dashboard.expiry_fields');
        $primary_colour = get_brand_setting('primary_colour');
        $vehicleListing = ['all'=>'All regions']+array_filter((new UserService())->getAllVehicleDashboardData());
        $isFleetcostTabEnabled = 0;

        $fleetCostSetting = Settings::where('key', 'is_fleetcost_enabled')->first();
        if ($fleetCostSetting && $fleetCostSetting->value == 1) {
            $isFleetcostTabEnabled = 1;
        }

        if($authUser->isHavingBespokeAccess()) {
            $userRoles = $authUser->roles()->get()->pluck('id')->toArray();
    		if (!in_array(15,$userRoles) && !in_array($fleetCost_id,$userRoles)) {
    		    $sidebarOrderedRoleids = [16,2,3,4,11,5,10,9,6];
    	            $roleRoute = array(16=>'/fleet_planning',2=>'/checks',3=>'/defects',4=>'vehicles/planning',11=>'/profiles',5=>'/reports',10=>'/messages',9=>'/workshops',6=>'/users');
            	    foreach ($sidebarOrderedRoleids as $key => $sidebarRoleid) {
    	                if (in_array($sidebarRoleid, $userRoles)){
                    	    return redirect($roleRoute[$sidebarRoleid]);
            	        }
    	            }
    		}

            if (in_array(15, $userRoles) && in_array($fleetCost_id, $userRoles)) {
                $vehicleRegions = config('config-variables.vehicleRegionsForSelect');
                JavaScript::put([
                    'loggedInUser' => $authUser,
                    'userRoles' => array(),
                    'selectPeriod' => $selectPeriod,
                ]);

               return view('dashboard.index',compact('vehicleRegions','periods','inspection_fields','expiry_fields','vehicleListing','primary_colour','userRoles','fleetCost_id','isFleetcostTabEnabled'));
            } else{
                JavaScript::put([
                    'loggedInUser' => $authUser,
                    'userRoles' => $userRoles,
                    'selectPeriod' => $selectPeriod,
                ]);
            }
        }

        $data = $this->vehicleService->getDataDivRegLoc();
        $vehicleRegions=['all' => 'All']+$data['vehicleRegions'];

        if (env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $vehicleRegions = ['all'=>'All']+array_filter($this->vehicleService->regionForSelect($data));
        }

        JavaScript::put([
            'loggedInUser' => $authUser,
            'userRoles' => $userRoles,
            'fleetCost_id' => $fleetCost_id,
            'selectPeriod' => $selectPeriod,
        ]);

        return view('dashboard.index', compact('vehicleRegions', 'periods', 'inspection_fields', 'expiry_fields', 'loggedInUser', 'primary_colour','vehicleListing','userRoles','fleetCost_id','isFleetcostTabEnabled'));
    }

    public function checkUA(Request $request)
    {
        if (env('SHOW_APP_DOWNLOAD_PAGE') == 1) {
            $agent = new Agent();
            if ($agent->isMobile() || $agent->isTablet()) {
                return redirect('/apps');
            } else {
                return redirect('/home');
            }
        } else {
            return redirect('/home');
        }
    }

    public function apps()
    {
        if (env('SHOW_APP_DOWNLOAD_PAGE') == 1) {
            $agent = new Agent();
            $apk_version_url = "";
            $ios_version_url = "";
            if (env('APK_DOWNLOAD_URL') != null) {
                $apk_version_url = env('APK_DOWNLOAD_URL')."/".setting('android_version')."/fleetmastr.apk";
                $ios_version_url = env('IOS_DOWNLOAD_URL')."/".setting('ios_version')."/manifest.plist";
            }
            return view('dashboard.apps', compact('agent','apk_version_url','ios_version_url'));
        } else {
            return redirect('/home');
        }
    }

    public function changeNotificationStatus(Request $request)
    {
        $loggedInUser = Auth::user();
        $userNotification = UserNotification::where('user_id', $loggedInUser->id)
                                    ->where('id', $request->notificationId)
                                    ->first();

        if($userNotification) {
            if($request->status ==  'read') {
                $userNotification->is_read = true;
            } else {
                $userNotification->is_read = false;
            }
            $userNotification->save();
        }

        $notificationCount = UserNotification::where('is_read', 0)->where('is_deleted', 0)
                  ->where('user_id', Auth::user()->id)->count();

        return response([
            'userNotification' => $userNotification,
            'notificationCount' => $notificationCount
        ], 200);
    }

    public function deleteUserNotification(Request $request)
    {
        $userNotification = UserNotification::where(['user_id' => Auth::user()->id])
                                    ->where('id', $request->notificationId)
                                    ->first();
        if($userNotification) {
            $userNotification->is_deleted = true;
            $userNotification->save();
        }

        $notificationCount = UserNotification::where('is_read', 0)->where('is_deleted', 0)
                  ->where('user_id', Auth::user()->id)->count();

        return response([
            'userNotification' => $userNotification,
            'notificationCount' => $notificationCount
        ], 200);
    }
}
