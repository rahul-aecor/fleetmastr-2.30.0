<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\User;
use App\Models\UserDivision;
use App\Models\VehicleDivisions;
use App\Models\UserRegion;
use App\Models\VehicleRegions;
use App\Models\UserLocation;

class UsersRepository extends EloquentRepositoryAbstract {

    public function __construct($data = null)
    {
        $brand = env('BRAND_NAME');
        $hiddenUser = config('config-variables.hiddenUser');
        $this->Database = DB::table('users')

            ->select('users.id', 

                // DB::raw("(CASE WHEN users.email IS NULL THEN '' ELSE users.email END) as email"),
                DB::raw("CASE WHEN users.email IS NULL THEN '' ELSE (CASE WHEN users.email LIKE '%-imastr.com%' THEN REPLACE(users.email, '@".$brand."-imastr.com', '') ELSE users.email END) END AS email"),

                'users.first_name', 'users.last_name',

                // 'users.username',
                DB::raw("CASE WHEN users.username LIKE '%-imastr.com%' THEN REPLACE(users.username, '@".$brand."-imastr.com', '') ELSE users.username END AS username"),

                'users.company_id','users.driver_tag_key',
                'users.job_title', 'users.mobile','users.landline','users.is_disabled',
                // 'users.enable_login',
                DB::raw('CASE WHEN users.enable_login = 1 THEN "Yes" ELSE "No" END AS enable_login'),
                // 'users.is_verified',
                DB::raw(" (CASE
                        WHEN users.is_verified = 0 && users.email IS NOT NULL && users.is_disabled = 0 THEN 'Resend invite'
                        WHEN users.is_disabled <> 0 THEN 'Inactive'
                        WHEN users.is_verified = 1 THEN 'Active'
                        ELSE '' END) as is_verified"),

                'users.engineer_id','users.imei','users.field_manager_phone','companies.name','users.created_at','users.updated_at','user_divisions.name as division_name', 'user_regions.name as region_name', 'user_locations.name as location_name', 'users.fuel_card_number as fuel_card_number',
                    DB::raw(" (CASE 
                        WHEN LOCATE(';',users.user_agent) = 0 OR LOCATE(';',users.user_agent) IS NULL THEN 'N/A' 
                        WHEN users.user_agent LIKE '%/%' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(users.user_agent, ' ', 1), '/', -1)
                        ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(users.user_agent, ';', 3), ';', -1) 
                        END)
                        as app"),
                    DB::raw(" (CASE 
                        WHEN LOCATE(';',users.user_agent) = 0 OR LOCATE(';',users.user_agent) IS NULL THEN 'N/A' 
                        ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(users.user_agent, ';', 2), ';', -1) 
                        END)
                        as device"),

                DB::raw("CONCAT(parent.first_name, ' ', parent.last_name) as line_manager_name"),
                DB::raw('(SELECT GROUP_CONCAT(r.name) FROM users u, roles r, role_user ur WHERE u.id=ur.user_id AND r.id = ur.role_id AND u.id=users.id) as userroles'),
                // DB::raw("DATE_FORMAT(CONVERT_TZ(users.last_login, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%H:%i:%s %d %b %Y') as 'last_login'"))
                DB::raw("CASE WHEN users.last_login IS NULL OR users.last_login = '' THEN 'No login data recorded' ELSE DATE_FORMAT(CONVERT_TZ(users.last_login, 'UTC', '".config('config-variables.format.displayTimezone')."'), '%H:%i:%s %d %b %Y') END AS last_login")
            )

            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->leftJoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
            ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
            ->leftJoin('user_locations', 'users.user_locations_id', '=', 'user_locations.id')
            ->leftJoin('users as parent', 'users.line_manager', '=', 'parent.id')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('role_user')
                    ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                    ->whereRaw('role_user.user_id = users.id')
                    ->where('roles.name', '=', 'App version handling');
            })
            ->where(function($query) {
                $query->where('users.workshops_user_flag', '=', '0')
                ->orWhere('users.workshops_user_flag', '=', '2');
            })
            ->whereNotIn('users.email', $hiddenUser);

        if(isset($data['searchLastNameStr'])) {
            $searchStr = trim($data['searchLastNameStr']);
            $this->Database = $this->Database->where(function($query) use ($searchStr) {
                    $query->where('users.email', 'like', '%' . addslashes($searchStr) . '%');
                    $query->orWhere('users.first_name', 'like', '%' . addslashes($searchStr) . '%');
                    $query->orWhere('users.last_name', 'like', '%' . addslashes($searchStr) . '%');
            });
        }

        if(isset($data['userDivisionId']) && $data['userDivisionId']) {
            $searchStr = trim($data['userDivisionId']);
            $this->Database = $this->Database->where(function($query) use ($searchStr) {
                $query->where('users.user_division_id', $searchStr);
            });
        }

        if(isset($data['driverTag']) && !empty($data['driverTag'])) {
            $driverTag = trim($data['driverTag']);
            $this->Database=$this->Database->where(function($query) use ($driverTag) {
                $query->where('users.driver_tag_key', 'like', '%'.$driverTag.'%');
            });
        }

        $this->visibleColumns = [
            'users.id', 'users.email', 'users.first_name', 'users.last_name', 'users.username', 'users.company_id',
            'users.job_title', 'users.mobile','users.landline','users.is_disabled', 'is_verified','companies.name','users.enable_login','users.imei', 'line_manager_name','users.engineer_id','users.field_manager_phone',
            'userroles','last_login','division_name','region_name','location_name','app','device','driver_tag_key','fuel_card_number'
        ];
        //$this->orderBy = [['users.company_id'], ['users.last_name'], ['users.created_at', 'DESC']];
        $this->orderBy = [['first_name', 'ASC']];
    }

    public function findByUserNameOrCreate($userData) {
        $user = User::where('email', '=', $userData->email)
                      ->whereNull('deleted_at')->first();
        if (!$user) {
            return false;
        }
        return $user;
    }
    public function findByEmail($userData) {
        $user = User::where('email', '=', $userData->email)
                    ->where('is_lanes_account', true)
                    ->first();        
        if (!$user) {
            return false;
        }
        return $user;
    }

    public function checkIfUserNeedsUpdating($userData, $user) {

        $socialData = [
            'email' => $userData->email,
        ];
        $dbData = [
            'email' => $user->email,
        ];

        if (!empty(array_diff($socialData, $dbData))) {
            $user->email = $userData->email;
            $user->save();
        }
    }

    public function getAllLinkedData() {
        $userDivisions = [];
        $userRegion = [];
        $userBaseLocation = [];
        $userOnlyRegions = [];
        if(env('IS_DIVISION_REGION_LINKED_IN_USER') && env('IS_REGION_LOCATION_LINKED_IN_USER')) {
            $allDivisions = UserDivision::with(['regions', 'regions.locations'])->orderBy('name', 'asc')->get()->toArray();
            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    if(isset($divisions['name']) && $divisions['id']) {
                        $userDivisions[$divisions['id']] = $divisions['name'];
                    }

                    if(isset($divisions['regions']) && is_array($divisions['regions']) && !empty($divisions['regions'])) {
                        // create division wise regions lists
                        foreach ($divisions['regions'] as $regions) {
                            if(isset($regions['name']) && $regions['id']) {
                                $userRegion[$divisions['id']][$regions['id']] = $regions['name'];
                                $userOnlyRegions[$regions['id']] = $regions['name'];
                            }

                            if(isset($regions['locations']) && is_array($regions['locations']) && !empty($regions['locations'])) {
                                // create region wise locations lists
                                foreach ($regions['locations'] as $locations) {
                                    if(isset($locations['name']) && $locations['id']) {
                                        $userBaseLocation[$regions['id']][$locations['id']] = $locations['name'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif (env('IS_DIVISION_REGION_LINKED_IN_USER')) {
            $allDivisions = UserDivision::with(['regions'])->orderBy('name', 'asc')->get()->toArray();
            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    if(isset($divisions['name']) && $divisions['id']) {
                        $userDivisions[$divisions['id']] = $divisions['name'];
                    }

                    if(isset($divisions['regions']) && is_array($divisions['regions']) && !empty($divisions['regions'])) {
                        // create division wise regions lists
                        foreach ($divisions['regions'] as $regions) {
                            if(isset($regions['name']) && $regions['id']) {
                                $userRegion[$divisions['id']][$regions['id']] = $regions['name'];
                                $userOnlyRegions[$regions['id']] = $regions['name'];
                            }
                        }
                    }
                }
            }
            $userBaseLocation = UserLocation::lists('name', 'id')->toArray();
        } elseif (env('IS_REGION_LOCATION_LINKED_IN_USER')) {
            $allDivisions = UserDivision::with(['regions'])->orderBy('name', 'asc')->get()->toArray();
            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    if(isset($divisions['name']) && $divisions['id']) {
                        $userDivisions[$divisions['id']] = $divisions['name'];
                    }

                    $allRegions = UserRegion::with(['locations'])->orderBy('name', 'asc')->get()->toArray();

                    // if(isset($divisions['regions']) && is_array($divisions['regions']) && !empty($divisions['regions'])) {
                    if(!empty($allRegions)) {
                        // create division wise regions lists
                        foreach ($allRegions as $regions) {
                            if(isset($regions['locations']) && is_array($regions['locations']) && !empty($regions['locations'])) {
                                // create region wise locations lists
                                foreach ($regions['locations'] as $locations) {
                                    if(isset($locations['name']) && $locations['id']) {
                                        $userBaseLocation[$regions['id']][$locations['id']] = $locations['name'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $userOnlyRegions = $userRegion = UserRegion::lists('name', 'id')->toArray();

        } else {
            $userDivisions = UserDivision::orderBy('name', 'asc')->lists('name', 'id')->toArray();
            $userOnlyRegions = $userRegion = UserRegion::lists('name', 'id')->toArray();
            $userBaseLocation = UserLocation::lists('name', 'id')->toArray();
        }

        return [
            'userDivisions' => $userDivisions,
            'userRegion' => $userRegion,
            'userOnlyRegions' => $userOnlyRegions,
            'userBaseLocation' => $userBaseLocation
        ];
    }

    public function retriveLiveTabUserList($request){
        $cl=20;
        //fetch users who have dallas key or rfid card & base on their recent journey
        $query=User::select('users.id','users.first_name','users.last_name','tj.id as journeyId','tj.vehicle_id','tj.vrn','tj.make','tj.model','v.telematics_ns',DB::raw("CASE WHEN v.telematics_ns IN ('".implode("','",config('config-variables.moving_events'))."') THEN 'driving' WHEN v.telematics_ns IN ('".implode("','",config('config-variables.start_events'))."') THEN 'driving' WHEN v.telematics_ns IN ('".implode("','",config('config-variables.stopped_events'))."') THEN 'stopped' WHEN v.telematics_ns IN ('".implode("','",config('config-variables.idling_events'))."') THEN 'idling' ELSE '' END as telematics_ns_label"));
        //$query->leftjoin('telematics_journeys as tj','users.id','=','tj.user_id');
        $query->leftjoin('telematics_journeys as tj',function($q){
            $q->on('tj.user_id','=','users.id')->on('tj.id','=',DB::raw("(SELECT max(id) from telematics_journeys as tjtable where tjtable.user_id=users.id)"));
        });
        //$query->whereRaw("tj.id IN ".DB::raw('(select MAX(tjTable.id) from telematics_journeys as tjTable join users as uTable on uTable.id = tjTable.user_id group by uTable.id)'));
        $query->leftjoin('vehicles as v','tj.vehicle_id','=','v.id');
        
        if(isset($request->uId) && !empty($request->uId)){
            $uId=$request->uId;
            $query->where('users.id','=',$uId);
        }
        $query->where('users.driver_tag_key','!=','');
        $query->groupBy('tj.vehicle_id');
        $query->orderBy('tj.id','desc');
        $query->latest('tj.id');
        if(isset($request->contentLimit)){
            $cl=$request->contentLimit;
        }
        $query->limit($cl);
        $basic=$query->get();
        $liveUserDetails = [];
        $i = 0;
        $newArray = [];
        $newCollection = collect();
        foreach ($basic as $key => $value) {
                /* if ($value->user->id == env('SYSTEM_USER_ID')) {
                  $driverName = 'Driver Unknown';
                }
                else{
                    $driverName = substr($value->user->first_name, 0, 1).' '.$value->user->last_name;
                } */
                $driverName = substr($value->first_name, 0, 1).' '.$value->last_name;
            	$newArray= array(
                    'user_id'=>$value->user_id,
                    'vehicle_id'=>$value->vehicle_id,
                    'vehicle_registration'=>$value->vrn,
                    'vehicle_make'=>$value->make,
                    'vehicle_model'=>$value->model,
                    'driver_name'=>$driverName,
                    'telematics_ns_label'=>$value->telematics_ns_label
                );
                $newCollection->push($newArray);
            	//$livelastnames[$i]['text'] = $userTextVisibility;
            	$i++;
        }
        return $newCollection;
    }
}
