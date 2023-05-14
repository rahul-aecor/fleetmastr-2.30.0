<?php

use Illuminate\Database\Seeder;

class UpdateUserPermissionRoleOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("update roles set display_order=11 where name='Earned recognition'");
        DB::statement("update roles set display_order=12 where name='Reports'"); 
        DB::statement("update roles set display_order=14 where name='User management'"); 
    }
}
