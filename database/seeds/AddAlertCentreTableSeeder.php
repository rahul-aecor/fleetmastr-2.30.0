<?php

use Illuminate\Database\Seeder;

class AddAlertCentreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("UPDATE roles SET display_order = 15 WHERE id = 17");
        DB::table('roles')->insert([
            [ 'name' => 'Alert Centre', 'description' => '', 'display_order' => '13'],
        ]);

        $roleId;
        $roles = DB::connection('mysql_new')->table('roles')->get();
        foreach ($roles as $key => $value) {
            if($value->name == 'Alert Centre') {
                $roleId = $value->id;
            }
        }
        
        DB::connection('mysql_new')->table('permissions')->insert([
            [ 'module' => 'Alert Centre', 'name' => 'Alert Centre', 'slug' => 'alertcentre.manage', 'description' => '', 'display' => 1 ]
        ]);

        // Assign permission
        $permissions = DB::connection('mysql_new')->table('permissions')->get();
        if(!empty($permissions)) {
            foreach ($permissions as $key => $permission) {
                $alertCentrePermission[$key]['role_id'] = $roleId;
                $alertCentrePermission[$key]['permission_id'] = $permission->id;
            }
            DB::connection('mysql_new')->table('permission_role')->insert($alertCentrePermission);
        }


    }
}
