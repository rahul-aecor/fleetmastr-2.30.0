<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS=0');
    	DB::table('permissions')->truncate();
    	DB::table('roles')->truncate();
    	DB::table('permission_role')->truncate();
    	// DB::connection('mysql_new')->table('role_user')->truncate();

		DB::table('permissions')->insert([
			[ 'module' => 'Check', 'name' => 'Vehicle Checks', 'slug' => 'check.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Defect', 'name' => 'Vehicle Defects', 'slug' => 'defect.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Search', 'name' => 'Vehicle Planning & Search', 'slug' => 'search.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Report', 'name' => 'Report', 'slug' => 'report.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'User', 'name' => 'User Management', 'slug' => 'user.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'App User', 'name' => 'App User', 'slug' => 'appuser.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Workshop User', 'name' => 'Workshop User', 'slug' => 'workshopuser.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Messaging', 'name' => 'Messaging', 'slug' => 'messaging.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Vehicle profiles', 'name' => 'Vehicle profiles', 'slug' => 'profiles.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Dashboard', 'name' => 'Dashboard', 'slug' => 'dashboard.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Planner', 'name' => 'Planner', 'slug' => 'planner.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Settings', 'name' => 'Settings', 'slug' => 'settings.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Incident Reports', 'name' => 'Incident Reports', 'slug' => 'incident.manage', 'description' => '', 'display' => 1 ],

			[ 'module' => 'Dashboard', 'name' => 'Dashboard (Costs)', 'slug' => 'dashboard.cost.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Alert Centre', 'name' => 'Alert Centre', 'slug' => 'alertcentre.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Earned recognition', 'name' => 'Earned recognition', 'slug' => 'eanrnedrecognition.manage', 'description' => '', 'display' => 1 ],
			[ 'module' => 'Telematics', 'name' => 'Telematics', 'slug' => 'telematics.manage', 'description' => '', 'display' => 1 ],
		]);

		DB::table('roles')->insert([
			[ 'name' => 'Super admin', 'description' => '', 'display_order' => 0],
			[ 'name' => 'Vehicle checks', 'description' => '', 'display_order' => 4],
			[ 'name' => 'Vehicle defects', 'description' => '', 'display_order' => 5],
			[ 'name' => 'Vehicle search', 'description' => '', 'display_order' => 8],
			[ 'name' => 'Reports', 'description' => '', 'display_order' => 12],
			[ 'name' => 'User management', 'description' => '', 'display_order' => 14],
			[ 'name' => 'Backend manager', 'description' => '', 'display_order' => 0],
			[ 'name' => 'App access', 'description' => '', 'display_order' => 0],
			[ 'name' => 'Workshops', 'description' => '', 'display_order' => 9],
			[ 'name' => 'Messaging', 'description' => '', 'display_order' => 10],
			[ 'name' => 'Vehicle profiles', 'description' => '', 'display_order' => 7],
			[ 'name' => 'Workshop manager', 'description' => '', 'display_order' => 0],
			[ 'name' => 'Defect email notifications', 'description' => '', 'display_order' => 0],
			[ 'name' => 'User information only', 'description' => '', 'display_order' => 0],
			[ 'name' => 'Dashboard (statistics)', 'description' => '', 'display_order' => 1],
			[ 'name' => 'Fleet planning', 'description' => '', 'display_order' => 3],
			[ 'name' => 'Settings', 'description' => '', 'display_order' => 15],
			[ 'name' => 'Incident reports', 'description' => '', 'display_order' => 6],
			[ 'name' => 'Dashboard (costs)', 'description' => '', 'display_order' => 2],
			[ 'name' => 'App Version Handling', 'description' => '', 'display_order' => 0],
			[ 'name' => 'Alert Centre', 'description' => '', 'display_order' => 13],
			[ 'name' => 'Earned recognition', 'description' => '', 'display_order' => 11],
			[ 'name' => 'Telematics', 'description' => '', 'display_order' => 16],
			[ 'name' => 'Manage DVSA configurations', 'description' => '', 'display_order' => 14],
		]);

		DB::table('permission_role')->insert([
			[ 'role_id' => 2, 'permission_id' => 1],
			[ 'role_id' => 3, 'permission_id' => 2],
			[ 'role_id' => 4, 'permission_id' => 3],
			[ 'role_id' => 5, 'permission_id' => 4],
			[ 'role_id' => 6, 'permission_id' => 5],
			[ 'role_id' => 8, 'permission_id' => 6],
			[ 'role_id' => 9, 'permission_id' => 7],
			[ 'role_id' => 10, 'permission_id' => 8],
			[ 'role_id' => 11, 'permission_id' => 9],
			[ 'role_id' => 12, 'permission_id' => 2],
			[ 'role_id' => 14, 'permission_id' => 1],
			[ 'role_id' => 14, 'permission_id' => 2],
			[ 'role_id' => 14, 'permission_id' => 3],
			[ 'role_id' => 14, 'permission_id' => 6],
			[ 'role_id' => 15, 'permission_id' => 10],
			[ 'role_id' => 16, 'permission_id' => 11],
			[ 'role_id' => 17, 'permission_id' => 12],
			[ 'role_id' => 18, 'permission_id' => 13],
			[ 'role_id' => 19, 'permission_id' => 14],
			[ 'role_id' => 22, 'permission_id' => 15],
			[ 'role_id' => 23, 'permission_id' => 16],
			[ 'role_id' => 24, 'permission_id' => 17],
		]);

		// DB::connection('mysql_new')->table('role_user')->insert([
		// 	[ 'user_id' => 1, 'role_id' => 1],
		// 	[ 'user_id' => 2, 'role_id' => 1],
		// 	[ 'user_id' => 3, 'role_id' => 1],
		// 	[ 'user_id' => 4, 'role_id' => 1],
		// 	[ 'user_id' => 5, 'role_id' => 8],
		// 	[ 'user_id' => 6, 'role_id' => 8],
		// ]);	

		// 	// // Beta Users
		// 	// [ 'user_id' => 1, 'role_id' => 1],
		// 	// [ 'user_id' => 2, 'role_id' => 1],
		// 	// [ 'user_id' => 3, 'role_id' => 1],
		// 	// [ 'user_id' => 4, 'role_id' => 1],
		// 	// [ 'user_id' => 5, 'role_id' => 1],
		// 	// [ 'user_id' => 6, 'role_id' => 1],
		// 	// [ 'user_id' => 7, 'role_id' => 1],
		// 	// [ 'user_id' => 8, 'role_id' => 1],
		// 	// [ 'user_id' => 9, 'role_id' => 1],
		// 	// [ 'user_id' => 10, 'role_id' => 1],
		// 	// [ 'user_id' => 11, 'role_id' => 1],
		// 	// [ 'user_id' => 12, 'role_id' => 1],
		// 	// [ 'user_id' => 13, 'role_id' => 1],
		// 	// [ 'user_id' => 14, 'role_id' => 1]
		// ]);

		// DB::connection('mysql_new')->table('role_user')->insert([
		// 	[ 'user_id' => 1, 'role_id' => 1],
		// 	[ 'user_id' => 2, 'role_id' => 2],
		// 	[ 'user_id' => 3, 'role_id' => 3],
		// 	[ 'user_id' => 4, 'role_id' => 4],
		// 	[ 'user_id' => 5, 'role_id' => 5],
		// 	[ 'user_id' => 6, 'role_id' => 6],
		// 	[ 'user_id' => 7, 'role_id' => 7],
		// 	[ 'user_id' => 8, 'role_id' => 1],
		// 	[ 'user_id' => 9, 'role_id' => 2],
		// 	[ 'user_id' => 10, 'role_id' => 3],
		// 	[ 'user_id' => 11, 'role_id' => 4],
		// 	[ 'user_id' => 12, 'role_id' => 5],
		// 	[ 'user_id' => 13, 'role_id' => 6],
		// 	[ 'user_id' => 14, 'role_id' => 7],
		// 	[ 'user_id' => 15, 'role_id' => 1],
		// 	[ 'user_id' => 16, 'role_id' => 2],
		// 	[ 'user_id' => 17, 'role_id' => 3],
		// 	[ 'user_id' => 18, 'role_id' => 4],
		// 	[ 'user_id' => 19, 'role_id' => 5],
		// 	[ 'user_id' => 20, 'role_id' => 6],
		// 	[ 'user_id' => 21, 'role_id' => 1],
		// 	[ 'user_id' => 22, 'role_id' => 1],
		// 	[ 'user_id' => 23, 'role_id' => 8],
		// 	[ 'user_id' => 24, 'role_id' => 1],
		// 	[ 'user_id' => 25, 'role_id' => 1]
		// ]);

		// DB::table('survey_json_version')->insert([
		//     ['version' => 0]
		// ]);	
		DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
