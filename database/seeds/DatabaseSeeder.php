<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(CompaniesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(UserListSeeder::class);
        $this->call(DefectMasterTableSeeder::class);
        $this->call(VehicleLocationsTableSeeder::class);
        $this->call(VehicleRepairLocationsTableSeeder::class);
        $this->call(VehicleTypesTableSeeder::class);
        $this->call(VehiclesTableSeeder::class);
        $this->call(DefectMasterVehicleTypesTableSeeder::class);
        $this->call(SurveyMasterTableSeeder::class);
        $this->call(AdditionalPermissionTableSeeder::class);
        $this->call(AddUserInformationOnlyRoleTableSeeder::class);
        $this->call(AddDashboardAndPlannerRoleTableSeeder::class);
        $this->call(AddSettingsRoleTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(SettingsTablePD11Seeder::class);
        $this->call(AddRoleAndPermissionAndPermissionRoleSeeder::class);
        $this->call(AlertsSeeder::class);
        $this->call(AlertNotification::class);
        $this->call(CreateReportCategoriesTableSeeder::class);
        $this->call(CreateReportDataSetTableSeeder::class);
        $this->call(CreateReportCategoryReportDataSetTableSeeder::class);
        $this->call(StandardReportTableSeeder::class);
        $this->call(UpdateUserPermissionRoleOrderSeeder::class);
        //$this->call(UserSyncSeeder::class);
        Model::reguard();
    }
}
