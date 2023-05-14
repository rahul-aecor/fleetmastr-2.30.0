<?php

use Illuminate\Database\Seeder;

class AddTelematicsRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleCount = DB::connection('mysql_new')->table('roles')->where('name', 'Telematics')->count();
        if($roleCount > 0) {
            return "Role already exists";
        }
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Telematics', 'description' => '', 'display_order' => '15']
        ]);
        $roleid = DB::connection('mysql_new')->getPdo()->lastInsertId();
        DB::connection('mysql_new')->table('permissions')->insert([
        	[ 'module' => 'Telematics', 'name' => 'Telematics', 'slug' => 'telematics.manage', 'description' => '', 'display' => 1 ]
        ]);
        $permissionid = DB::connection('mysql_new')->getPdo()->lastInsertId();
    
        DB::connection('mysql_new')->table('permission_role')->insert([
            [ 'role_id' => $roleid, 'permission_id' => $permissionid ],
        ]);
		

    }
}
