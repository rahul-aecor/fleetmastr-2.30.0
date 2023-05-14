<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use DB;

class UserRecordInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:userRecordInsert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UserRecordInsert';

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
        DB::connection('mysql_new')->table('users')->insert([
            ['id'=> '2', 'email' => 'rstenson@imastr.com', 'username' => 'rstenson@imastr.com', 'password' => bcrypt('aecor2019'), 'first_name' => 'Richard', 'last_name' => 'Stenson', 'company_id' => '3', 'job_title' => 'Software Developer', 'mobile' => '07966801049', 'is_active' => '1', 'enable_login' => '1', 'is_verified' => '1', 'is_lanes_account' => '0', 'imei' => '355922071064717', 'user_agent' => 'fleetmastr/2.0.0 (iPhone; iOS 13.3.1; Scale/2.00)', 'remember_token' => 'RVtLVcSeCdcgfSn6ssLFJfzRcJApbrZ3kZL7zHrEWOPNecmdToGVBQYydd94', 'is_app_installed' => '1', 'last_login' => Carbon::now()->toDateTimeString(), 'created_at' => Carbon::now()->toDateTimeString(), 'updated_at' => Carbon::now()->toDateTimeString()],
        ]);

        DB::connection('mysql_new')->table('role_user')->insert([
            [ 'role_id' => 1, 'user_id' => 1],
        ]);

        DB::connection('mysql_new')->table('permission_role')->insert([
            [ 'permission_id' => 1, 'role_id' => 1],
        ]);
    }
}
