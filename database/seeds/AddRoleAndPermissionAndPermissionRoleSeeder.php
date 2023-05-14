<?php

use Illuminate\Database\Seeder;

class AddRoleAndPermissionAndPermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement("UPDATE roles SET name='Dashboard (Statistics)', description='' WHERE id = 15"); 
        DB::table('roles')->insert([
        	[ 'name' => 'Dashboard (Costs)', 'description' => '', 'display_order' => '2'],
        ]);

        $role_id = DB::getPdo()->lastInsertId();
        
        DB::statement("UPDATE permissions SET name='Dashboard (Statistics)', module='Dashboard', slug='dashboard.manage' WHERE id = 10"); 
        DB::table('permissions')->insert([
        	[ 'module' => 'Dashboard', 'name' => 'Dashboard (Costs)', 'slug' => 'dashboard.cost.manage', 'description' => '', 'display' => 1 ],
        ]);

        $permission_id = DB::getPdo()->lastInsertId();

        DB::connection('mysql_new')->table('permission_role')->insert([
			[ 'role_id' => $role_id, 'permission_id' => $permission_id ],
		]);

		DB::statement("UPDATE roles SET display_order = 3 WHERE id = 16"); 
		DB::statement("UPDATE roles SET display_order = 4 WHERE id = 2"); 
		DB::statement("UPDATE roles SET display_order = 5 WHERE id = 3"); 
		DB::statement("UPDATE roles SET display_order = 6 WHERE id = 18"); 
		DB::statement("UPDATE roles SET display_order = 7 WHERE id = 4"); 
		DB::statement("UPDATE roles SET display_order = 8 WHERE id = 11"); 
		DB::statement("UPDATE roles SET display_order = 9 WHERE id = 5"); 
		DB::statement("UPDATE roles SET display_order = 10 WHERE id = 10"); 
		DB::statement("UPDATE roles SET display_order = 11 WHERE id = 9"); 
		DB::statement("UPDATE roles SET display_order = 12 WHERE id = 6"); 
		DB::statement("UPDATE roles SET display_order = 13 WHERE id = 17"); 

    }
}
