<?php

use Illuminate\Database\Seeder;

class UpdateRolesOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("update roles set display_order=9 where name='Workshops'");
        DB::statement("update roles set display_order=10 where name='Messaging'");
        DB::statement("update roles set display_order=11 where name='Reports'");
        DB::statement("update roles set display_order=8 where name='Vehicle search'");
        DB::statement("update roles set display_order=7 where name='Vehicle profiles'");
    }
}
