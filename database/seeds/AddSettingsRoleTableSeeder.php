<?php

use Illuminate\Database\Seeder;

class AddSettingsRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Settings', 'description' => '', 'display_order' => '11']
        ]);
        DB::connection('mysql_new')->table('permissions')->insert([
        	[ 'module' => 'Settings', 'name' => 'Settings', 'slug' => 'settings.manage', 'description' => '', 'display' => 1 ]
        ]);
        DB::connection('mysql_new')->table('permission_role')->insert([
			[ 'role_id' => 17, 'permission_id' => 12 ],
		]);
		

    }
}
