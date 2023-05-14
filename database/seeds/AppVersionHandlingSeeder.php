<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;

class AppVersionHandlingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Role
        $role = DB::connection('mysql_new')->table('roles')->where('name', 'App Version Handling');
        // if($roleCount > 0) {
        //     return "Role already exists";
        // }
        if($role->count() === 0) {
	        DB::connection('mysql_new')->table('roles')->insert([
	        	[ 'name' => 'App Version Handling', 'description' => 'App Version Handling']
	        ]);
            $roleid = DB::connection('mysql_new')->getPdo()->lastInsertId();
        } else {
            $roleid = $role->first()->id;
        }

        // Create User
        DB::connection('mysql_new')->table('users')->insert([
        	['email' => 'support@imastr.com', 'username' => 'support@imastr.com', 'password' => bcrypt(env('CONFIGURATION_HANDLING_USER_PASSWORD', 'password')), 'first_name' => 'Customer', 'last_name' => 'Services', 'company_id' => 1, 'mobile' => '', 'is_active' => true, 'enable_login' => true, 'is_verified' => true, 'is_lanes_account' => false, 'imei' => null, 'job_title' => null ],
        ]);
        $userid = DB::connection('mysql_new')->getPdo()->lastInsertId();
        // Assign Role
        DB::connection('mysql_new')->table('role_user')->insert( ['user_id' => $userid, 'role_id' => $roleid] );

        // Assign Super Admin Role
        $superAdminRole =  DB::connection('mysql_new')->table('roles')->where('name','=','Super Admin')->first();
        //print_r($superAdminRole);die;
        DB::connection('mysql_new')->table('role_user')->insert( ['user_id' => $userid, 'role_id' => $superAdminRole->id] );

        $permissions = DB::connection('mysql_new')->table('permissions')->get();
        if(!empty($permissions)) {
            foreach ($permissions as $key => $permission) {
                $insertPermissions[$key]['role_id'] = $roleid;
                $insertPermissions[$key]['permission_id'] = $permission->id;
            }
            DB::connection('mysql_new')->table('permission_role')->insert($insertPermissions);
        }
        $user = User::find($userid);
        $divisions = VehicleDivisions::all()->lists('id')->toArray();
        $regions = VehicleRegions::all()->lists('id')->toArray();
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) 
        {
            $user->divisions()->sync($divisions);
        }

        if(!empty($regions)) 
        {
            $user->regions()->sync($regions);
        }
    }
}
