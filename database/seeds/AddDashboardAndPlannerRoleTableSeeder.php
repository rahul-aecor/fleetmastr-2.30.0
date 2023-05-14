<?php

use Illuminate\Database\Seeder;

class AddDashboardAndPlannerRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Dashboard', 'description' => '', 'display_order' => '1'],
        	[ 'name' => 'Planner', 'description' => '', 'display_order' => '6']
        ]);
        DB::connection('mysql_new')->table('permissions')->insert([
        	[ 'module' => 'Dashboard', 'name' => 'Dashboard', 'slug' => 'dashboard.manage', 'description' => '', 'display' => 1 ],
        	[ 'module' => 'Planner', 'name' => 'Planner', 'slug' => 'planner.manage', 'description' => '', 'display' => 1 ],
        ]);
        DB::connection('mysql_new')->table('permission_role')->insert([
			[ 'role_id' => 15, 'permission_id' => 10 ],
			[ 'role_id' => 16, 'permission_id' => 11 ],
		]);
		DB::statement("UPDATE roles SET display_order = 0 WHERE id IN ('1','7','8','12','13','14')"); 
		DB::statement("UPDATE roles SET display_order = 2 WHERE id = 2"); 
		DB::statement("UPDATE roles SET display_order = 3 WHERE id = 3"); 
		DB::statement("UPDATE roles SET display_order = 4 WHERE id = 4"); 
		DB::statement("UPDATE roles SET display_order = 5 WHERE id = 6"); 
		DB::statement("UPDATE roles SET display_order = 7 WHERE id = 11"); 
		DB::statement("UPDATE roles SET display_order = 8 WHERE id = 5"); 
		DB::statement("UPDATE roles SET display_order = 9 WHERE id = 10"); 
		DB::statement("UPDATE roles SET display_order = 10 WHERE id = 9"); 

    }
}
