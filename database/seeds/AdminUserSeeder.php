<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
			['email' => 'admin@imastr.com', 'username' => 'admin@imastr.com', 'password' => bcrypt('aecor2021'), 'first_name' => 'Admin', 'last_name' => 'Admin', 'company_id' => 1, 'mobile' => '07966801049', 'is_active' => true, 'enable_login' => true, 'is_verified' => true, 'is_lanes_account' => false, 'imei' => '356100061633713', 'job_title' => 'Software Developer', 'created_at' => Carbon::now()->toDateTimeString() ],
		]);

        $user = User::where('email', '=', 'admin@imastr.com')->first();

        DB::connection('mysql_new')->table('role_user')->insert([
            [ 'role_id' => 1, 'user_id' => $user->id],
        ]);

        DB::connection('mysql_new')->table('permission_role')->insert([
            [ 'permission_id' => 1, 'role_id' => 1],
        ]);
    }
}
