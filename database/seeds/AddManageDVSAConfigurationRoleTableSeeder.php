<?php

use Illuminate\Database\Seeder;

class AddManageDVSAConfigurationRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleCount = DB::connection('mysql_new')->table('roles')->where('name', 'Manage DVSA configurations')->count();
        if($roleCount > 0) {
            return "Role already exists";
        }

        // Create new role
        DB::connection('mysql_new')->table('roles')->insert([
        	[ 'name' => 'Manage DVSA configurations', 'description' => 'Manage DVSA configurations', 'display_order' => '14']
        ]);

        $roleid = DB::connection('mysql_new')->getPdo()->lastInsertId();

        // Assign Role
        $supportUser = DB::connection('mysql_new')->table('users')->where('email', 'support@imastr.com')->first();
        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $supportUser->id, 'role_id' => $roleid]);

        // Assign permission
        $permissions = DB::connection('mysql_new')->table('permissions')->get();
        if(!empty($permissions)) {
            foreach ($permissions as $key => $permission) {
                $insertPermissions[$key]['role_id'] = $roleid;
                $insertPermissions[$key]['permission_id'] = $permission->id;
            }
            DB::connection('mysql_new')->table('permission_role')->insert($insertPermissions);
        }
    }
}
