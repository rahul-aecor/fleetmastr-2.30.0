<?php

use Illuminate\Database\Seeder;

class AddIncidentReportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Incident Reports', 'description' => '', 'display_order' => '5']
        ]);
        DB::connection('mysql_new')->table('permissions')->insert([
        	[ 'module' => 'Incident Reports', 'name' => 'Incident Reports', 'slug' => 'incident.manage', 'description' => '', 'display' => 1 ]
        ]);
        DB::connection('mysql_new')->table('permission_role')->insert([
			[ 'role_id' => 18, 'permission_id' => 13],
		]);
    }
}
