<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdditionalPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defectEmailNotification = DB::connection('mysql_new')->table('roles')->insert([
		[ 'name' => 'Defect Email Notifications', 'description' => '', 'display_order' => ''],
	]);
    }
}
