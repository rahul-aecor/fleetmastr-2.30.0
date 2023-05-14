<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;

class AddAdminManagementUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_new')->table('users')->insert([
            // Live Users
            ['email' => 'admin@imastr.com', 'username' => 'admin@imastr.com', 'password' => bcrypt('aecor2021'), 'first_name' => 'Admin', 'last_name' => 'Support', 'company_id' => 1, 'mobile' => null, 'is_active' => true, 'enable_login' => true, 'is_verified' => true, 'is_lanes_account' => false, 'imei' => null, 'job_title' => 'Software Developer', 'created_at' => Carbon::now()->toDateTimeString() ],
        ]);
        
        $user = User::where('email', 'admin@imastr.com')->first();
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

        // Assign Super Admin Role
        $superAdminRole =  DB::connection('mysql_new')->table('roles')->where('name','=','Super Admin')->first();
        //print_r($superAdminRole);die;
        DB::connection('mysql_new')->table('role_user')->insert( ['user_id' => $user->id, 'role_id' => $superAdminRole->id] );
    }
}
