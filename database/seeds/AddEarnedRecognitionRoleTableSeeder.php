<?php

use Illuminate\Database\Seeder;

class AddEarnedRecognitionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleCount = DB::connection('mysql_new')->table('roles')->where('name', 'Earned recognition')->count();
        if($roleCount > 0) {
            return "Role already exists";
        }

        // Create new role
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Earned recognition', 'description' => '', 'display_order' => '14']
        ]);

        $roleid = DB::connection('mysql_new')->getPdo()->lastInsertId();

        DB::connection('mysql_new')->table('permissions')->insert([
            [ 'module' => 'Earned recognition', 'name' => 'Earned recognition', 'slug' => 'eanrnedrecognition.manage', 'description' => '', 'display' => 1 ]
        ]);
        $permissionid = DB::connection('mysql_new')->getPdo()->lastInsertId();
        DB::connection('mysql_new')->table('permission_role')->insert([
            [ 'role_id' => $roleid, 'permission_id' => $permissionid ],
        ]);
    }
}
