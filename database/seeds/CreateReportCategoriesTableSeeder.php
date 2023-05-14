<?php

use Illuminate\Database\Seeder;

class CreateReportCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('report_categories')->truncate();
        DB::table('report_categories')->insert(
        [
            [
                'name' => 'Standard',
                'slug' => 'standard'
            ],
            [
                'name' => 'Vehicle profile',
                'slug' => 'vehicle_profile'
            ],
            [
                'name' => 'Vehicle journey',
                'slug' => 'vehicle_journey',
            ],
            [
                'name' => 'Vehicle incident',
                'slug' => 'vehicle_incident',
            ],
            [
                'name' => 'Vehicle behaviour',
                'slug' => 'vehicle_behaviour',
            ],
            [
                'name' => 'Vehicle defects',
                'slug' => 'vehicle_defects',
            ],
            [
                'name' => 'Vehicle checks',
                'slug' => 'vehicle_checks',
            ],
            [
                'name' => 'Vehicle planning',
                'slug' => 'vehicle_planning',
            ],
            [
                'name' => 'User details',
                'slug' => 'user_details',
            ],
            [
                'name' => 'User journey',
                'slug' => 'user_journey',
            ],
            [
                'name' => 'User incident',
                'slug' => 'user_incident',
            ],
            [
                'name' => 'Driver behaviour',
                'slug' => 'driver_behaviour',
            ],
            [
                'name' => 'User defects',
                'slug' => 'user_defects',
            ],
            [
                'name' => 'User checks',
                'slug' => 'user_checks',
            ],
            [
                'name' => 'Weekly maintenance',
                'slug' => 'weekly_maintenance',
            ],
            [
                'name' => 'Last login',
                'slug' => 'last_login',
            ],
            [
                'name' => 'Fleet cost',
                'slug' => 'fleet_cost',
            ],
            [
                'name' => 'Activity report',
                'slug' => 'activity_report',
            ],
            [
                'name' => 'Vor report',
                'slug' => 'vor_report',
            ],
            [
                'name' => 'Vor defect',
                'slug' => 'vor_defect',
            ],
            [
                'name' => 'Defect report',
                'slug' => 'defect_report',
            ],
            [
                'name' => 'Driving events',
                'slug' => 'driving_events',
            ],
            [
                'name' => 'Speeding report',
                'slug' => 'speeding_report',
            ],
            [
                'name' => 'Journey report',
                'slug' => 'journey_report',
            ],
            [
                'name' => 'Fuel emission',
                'slug' => 'fuel_emission',
            ],
            [
                'name' => 'Vehicle location',
                'slug' => 'vehicle_location',
            ],
            [
                'name' => 'PMI performance',
                'slug' => 'pmi_performance',
            ],
            
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
