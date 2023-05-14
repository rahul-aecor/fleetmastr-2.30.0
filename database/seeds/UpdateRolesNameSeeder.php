<?php

use Illuminate\Database\Seeder;

class UpdateRolesNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("UPDATE roles SET name = 'Super admin' WHERE name = 'Super Admin'");
        DB::statement("UPDATE roles SET name = 'Vehicle checks' WHERE name = 'Vehicle Checks'"); 
        DB::statement("UPDATE roles SET name = 'Vehicle defects' WHERE name = 'Vehicle Defects'");
        DB::statement("UPDATE roles SET name = 'Vehicle search' WHERE name = 'Vehicle Search'");
        DB::statement("UPDATE roles SET name = 'User management' WHERE name = 'User Management'");
        DB::statement("UPDATE roles SET name = 'Backend manager' WHERE name = 'Backend Manager'");
        DB::statement("UPDATE roles SET name = 'App access' WHERE name = 'App Access'");
        DB::statement("UPDATE roles SET name = 'Vehicle profiles' WHERE name = 'Vehicle Profiles'");
        DB::statement("UPDATE roles SET name = 'Workshop manager' WHERE name = 'Workshop Manager'");
        DB::statement("UPDATE roles SET name = 'Defect email notifications' WHERE name = 'Defect email notifications'");
        DB::statement("UPDATE roles SET name = 'User information only' WHERE name = 'User Information Only'");
        DB::statement("UPDATE roles SET name = 'Dashboard (statistics)' WHERE name = 'Dashboard (statistics)'");
        DB::statement("UPDATE roles SET name = 'Fleet planning' WHERE name = 'Fleet Planning'");
        DB::statement("UPDATE roles SET name = 'Incident reports' WHERE name = 'Incident Reports'");
        DB::statement("UPDATE roles SET name = 'App version handling' WHERE name = 'App Version Handling'");
        DB::statement("UPDATE roles SET name = 'Dashboard (costs)' WHERE name = 'Dashboard (Costs)'");



    }
}
