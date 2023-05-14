<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use DB;

class UpdateCreatedByUpdatedByIdForAllTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:UpdateCreatedByUpdatedByIdForAllTables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement("UPDATE checks SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE defects SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE defect_history SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE incidents SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE incident_history SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");  
        DB::statement("UPDATE vehicles SET created_by=2, updated_by=2, nominated_driver=2 WHERE created_by=1 OR updated_by=1 OR nominated_driver=1");
        DB::statement("UPDATE vehicle_vor_logs SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE vehicle_maintenance_history SET created_by=2, updated_by=2 WHERE created_by=1 OR updated_by=1");
        DB::statement("UPDATE vehicle_usage_history SET user_id=2 WHERE user_id=1");
        DB::statement("UPDATE column_management SET user_id=2 WHERE user_id=1");
        DB::statement("UPDATE messages SET sent_by=2 WHERE sent_by=1");
        DB::statement("UPDATE role_user SET user_id=2 WHERE user_id=1");
        DB::statement("UPDATE user_accessible_regions SET user_id=2 WHERE user_id=1");
        DB::statement("UPDATE user_accessible_divisions SET user_id=2 WHERE user_id=1");

        $user = User::find(1);
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement("DELETE FROM users WHERE id=1");
        DB::statement("DELETE FROM users WHERE id=2");
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::connection('mysql_new')->table('users')->insert([
            ['id'=> '1', 'email' => 'system@aecordigital.com', 'password' => bcrypt('aecor2019'), 'first_name' => 'System', 'last_name' => 'System', 'company_id' => 1, 'mobile' => '07966801049', 'is_active' => true, 'enable_login' => true, 'is_verified' => true, 'is_lanes_account' => false, 'imei' => '355922071064717', 'job_title' => 'Software Developer', 'created_at' => Carbon::now()->toDateTimeString()],
        ]);

        DB::connection('mysql_new')->table('role_user')->insert([
            [ 'role_id' => 1, 'user_id' => 1],
        ]);

        DB::connection('mysql_new')->table('permission_role')->insert([
            [ 'permission_id' => 1, 'role_id' => 1],
        ]);

        DB::connection('mysql_new')->table('users')->insert([
            ['id'=> '2', 'email' => $user->email, 'username' => $user->username, 'password' => $user->password, 'first_name' => $user->first_name, 'last_name' => $user->last_name, 'company_id' => $user->company_id, 'job_title' => $user->job_title, 'mobile' => $user->mobile, 'is_active' => $user->is_active, 'enable_login' => $user->enable_login, 'is_verified' => $user->is_verified, 'is_lanes_account' => $user->is_lanes_account, 'imei' => $user->imei, 'user_agent' => $user->user_agent, 'push_registration_id'=> $user->push_registration_id, 'is_app_installed' => $user->is_app_installed, 
                'last_login' => $user->last_login, 'created_at' => $user->created_at, 
                'updated_at' => $user->updated_at],
        ]);
    }
}
