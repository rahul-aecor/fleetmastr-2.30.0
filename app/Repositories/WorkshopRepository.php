<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\User;

class WorkshopRepository extends EloquentRepositoryAbstract {
	public function __construct() {
		$this->Database = DB::table('users')
            ->select('users.id', 'users.email', 'users.first_name', 'users.last_name', 'users.company_id', 
                'users.landline', 'users.mobile', 'users.is_disabled',
                // 'users.is_verified',
                DB::raw('CASE WHEN users.is_verified = 1 THEN "Activated" ELSE "Re-send invite" END AS is_verified'),
                'users.address1',
                'users.address2','users.town_city','users.postcode',
                // 'users.enable_login',
                DB::raw('CASE WHEN users.enable_login = 1 THEN "Yes" ELSE "No" END AS enable_login'),
                'companies.name',
                DB::raw('(SELECT GROUP_CONCAT(r.name) FROM users u, roles r, role_user ur WHERE u.id=ur.user_id AND r.id = ur.role_id AND u.id=users.id) as userroles'))
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')            
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')            
            ->where('roles.name', '=', 'Workshop manager');

        $this->visibleColumns = [
            'users.id', 'users.email', 'users.first_name', 'users.last_name', 'users.company_id', 
            'users.landline', 'users.mobile', 'users.is_disabled', 'users.is_verified', 'companies.name',
            'users.address1','users.address2','users.town_city','users.postcode','users.enable_login',
            'userroles'
        ];

        $this->orderBy = [['users.company_id'], ['users.last_name'], ['users.created_at', 'DESC']];
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
}