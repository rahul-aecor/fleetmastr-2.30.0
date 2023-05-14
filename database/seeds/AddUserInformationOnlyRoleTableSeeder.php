<?php

use Illuminate\Database\Seeder;

class AddUserInformationOnlyRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'User Information Only', 'description' => '', 'display_order' => '7']
        ]);
        DB::connection('mysql_new')->table('permission_role')->insert([
			[ 'role_id' => 14, 'permission_id' => 1],
			[ 'role_id' => 14, 'permission_id' => 2],
			[ 'role_id' => 14, 'permission_id' => 3],
			[ 'role_id' => 14, 'permission_id' => 6],
		]);
		DB::statement("UPDATE roles SET display_order = 6 WHERE name = 'User Management'"); 
		DB::statement("UPDATE roles SET display_order = 8 WHERE name = 'Vehicle Profiles'"); 
		DB::statement("UPDATE roles SET display_order = 9 WHERE name = 'Reports'"); 
		DB::statement("UPDATE roles SET display_order = 10 WHERE name = 'Messaging'"); 
		DB::statement("UPDATE roles SET display_order = 11 WHERE name = 'Workshops'"); 
		DB::statement("UPDATE roles SET display_order = 12 WHERE name = 'App User'"); 
		//DB::statement("UPDATE roles SET display_order = 0 WHERE name = 'App User'"); 
		DB::statement("UPDATE roles SET name = 'App Access' WHERE name = 'App User'"); 
		
		
    }
}
