<?php

use Illuminate\Database\Seeder;
use App\Models\ReportCategory;
use App\Models\Report;
use Carbon\Carbon;

class StandardReportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('reports')->truncate();
        // $standardCategory = ReportCategory::where('name', 'Standard')->first();
        // if(isset($standardCategory)) {
        //     $standardCategoryId = $standardCategory->id;
        // } else {
        //     $category = new ReportCategory();
        //     $category->name = 'Standard';
        //     $category->save();
        //     $standardCategoryId = $category->id;
        // }

        // DB::table('reports')->insert(
        $reports = 
        [
            // [
            //     'name' => 'Defect Report',
            //     'slug' => 'standard_defect_report',
            //     'description' => 'This report keeps a track of all the defects recorded within a calendar month as they accumulate.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 21,
            //      'report_for' => 'all',
            //     'period' => 'Monthly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            // [
            //     'name' => 'Defect Summary Report',
            //     'slug' => 'standard_vor_defect_report',
            //     'description' => 'This report provides a summary of defects reported.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 20,
            //     'report_for' => 'all',
            //     'period' => 'Weekly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            [
                'name' => 'VOR Report',
                'slug' => 'standard_vor_report',
                'description' => 'This report keeps track of vehicles that have been VOR’d and the estimated time they will be back on the road.',
                'report_type' => 'general',
                'report_category_slug' => 'vor_report',
                'report_for' => 'all',
                'period' => 'Weekly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            // [
            //     'name' => 'Vehicle Check Activity Report',
            //     'slug' => 'standard_activity_report',
            //     'description' => 'This report keeps a track of all the vehicle checks recorded within a week by users as they accumulate.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 18,
            //     'period' => 'Weekly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            [
                'name' => 'Fleet Costs',
                'slug' => 'standard_fleet_cost_report',
                'description' => 'This report keeps track of vehicle costs.',
                'report_type' => 'general',
                'report_category_slug' => 'fleet_cost',
                'report_for' => 'all',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Last Login Report',
                'slug' => 'standard_last_login_report',
                'description' => 'This report keeps track of the last time users logged in.',
                'report_type' => 'general',
                'report_category_slug' => 'last_login',
                'report_for' => 'user',
                'period' => 'Daily',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            // [
            //     'name' => 'Driving Events Report',
            //     'slug' => 'standard_driving_events_report',
            //     'description' => 'This report keeps a track of all driving events (acceleration, braking, cornering, speeding, RPM and idling) recorded within a calendar month.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 22,
            //      'report_for' => 'all',
            //     'period' => 'Monthly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            // [
            //     'name' => 'Speeding Report',
            //     'slug' => 'standard_speeding_report',
            //     'description' => 'This report keeps a track of all the speeding events associated with drivers and vehicles recorded within a calendar month.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 23,
            //      'report_for' => 'all',
            //     'period' => 'Monthly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            // [
            //     'name' => 'Journey Report',
            //     'slug' => 'standard_journey_report',
            //     'description' => 'This report keeps a track of all the journeys recorded within a calendar month.',
            // 'report_type' => 'general',
            // 'report_category_slug' => 24,
            //      'report_for' => 'all',
            //     'period' => 'Monthly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            [
                'name' => 'Fuel Usage and Emissions Report',
                'slug' => 'standard_fuel_usage_and_emission_report',
                'description' => 'This report keeps track of fuel consumed and vehicle CO2 emissions.',
                'report_type' => 'telematics',
                'report_category_slug' => 'fuel_emission',
                'report_for' => 'all',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Driver Behaviour Report',
                'slug' => 'standard_driver_behaviour_report',
                'description' => 'This report keeps track of driver behaviour and recorded incidents.',
                'report_type' => 'telematics',
                'report_category_slug' => 'driver_behaviour',
                'report_for' => 'user',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Behaviour Report',
                'slug' => 'standard_vehicle_behaviour_report',
                'description' => 'This report keeps track of vehicle behaviour and incidents associated with vehicles.',
                'report_type' => 'telematics',
                'report_category_slug' => 'vehicle_behaviour',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Details Report',
                'slug' => 'standard_vehicle_profile_report',
                'description' => 'This report keeps track of vehicle details.',
                'report_type' => 'general',
                'report_category_slug' => 'vehicle_profile',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Journey Report',
                'slug' => 'standard_vehicle_journey_report',
                'description' => 'This report keeps track of journeys by vehicle registration number.',
                'report_type' => 'telematics',
                'report_category_slug' => 'vehicle_journey',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Incident Report',
                'slug' => 'standard_vehicle_incident_report',
                'description' => 'This report keeps track of vehicle behaviour and recorded incidents.',
                'report_type' => 'telematics',
                'report_category_slug' => 'vehicle_incident',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Defects Report',
                'slug' => 'standard_vehicle_defects_report',
                'description' => 'This report keeps track of vehicle defects by registration number.',
                'report_type' => 'general',
                'report_category_slug' => 'vehicle_defects',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Checks Report',
                'slug' => 'standard_vehicle_checks_report',
                'description' => 'This report keeps track of vehicle checks by registration number.',
                'report_type' => 'general',
                'report_category_slug' => 'vehicle_checks',
                'report_for' => 'vehicle',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            // [
            //     'name' => 'Vehicle Planning Report',
            //     'slug' => 'standard_vehicle_planning_report',
            //     'description' => 'This report keeps track of vehicle planning details.',
            //     'report_type' => 'general',
            //     'report_category_slug' => 'vehicle_planning',
            //     'report_for' => 'vehicle',
            //     'period' => 'Monthly',
            //     'is_custom_report' => 0,
            //     'created_by' => 1,
            //     'updated_by' => 1
            // ],
            [
                'name' => 'User Details Report',
                'slug' => 'standard_user_details_report',
                'description' => 'This report keeps track of user details.',
                'report_type' => 'general',
                'report_category_slug' => 'user_details',
                'report_for' => 'user',                
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'User Journey Report',
                'slug' => 'standard_user_journey_report',
                'description' => 'This report keeps track of user journeys.',
                'report_type' => 'telematics',
                'report_category_slug' => 'user_journey',
                'report_for' => 'user',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'User Incident Report',
                'slug' => 'standard_user_incident_report',
                'description' => 'This report keeps track of user incidents.',
                'report_type' => 'telematics',
                'report_category_slug' => 'user_incident',
                'report_for' => 'user',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'User Defects Report',
                'slug' => 'standard_user_defects_report',
                'description' => 'This report keeps track of user vehicle defects.',
                'report_type' => 'general',
                'report_category_slug' => 'user_defects',
                'report_for' => 'user',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'User Checks Report',
                'slug' => 'standard_user_checks_report',
                'description' => 'This report keeps track of user vehicle checks.',
                'report_type' => 'general',
                'report_category_slug' => 'user_checks',
                'report_for' => 'user',
                'period' => 'Monthly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Maintenance Report',
                'slug' => 'standard_weekly_maintanance_report',
                'description' => 'This report keeps track of vehicle maintenance details.',
                'report_type' => 'general',
                'report_category_slug' => 'weekly_maintenance',
                'report_for' => 'all',
                'period' => 'Weekly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'Vehicle Location Report',
                'slug' => 'standard_vehicle_location_report',
                'description' => 'This report confirms the vehicle location at a specific date and time.',
                'report_type' => 'telematics',
                'report_category_slug' => 'vehicle_location',
                'report_for' => 'vehicle',
                'period' => 'Weekly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
            [
                'name' => 'PMI Performance Report',
                'slug' => 'standard_pmi_performance_report',
                'description' => 'This report tracks the performance of the completion of PMI maintenance events.',
                'report_type' => 'general',
                'report_category_slug' => 'pmi_performance',
                'report_for' => 'all',
                'period' => 'Weekly',
                'is_custom_report' => 0,
                'created_by' => 1,
                'updated_by' => 1
            ],
        ];

        $allReports = Report::whereNotNull('slug')->get()->pluck('slug')->toArray();
        $standardReports = [];

        foreach($reports as $data) {
            $report = Report::where('slug', $data['slug'])->first();
            if(!isset($report)) {
                $report = new Report();
                $report->created_by = 1;
                $report->updated_by = 1;
                $report->created_at = 1;
                $report->created_at = Carbon::now()->toDateTimeString();
            }

            $report->updated_at = Carbon::now()->toDateTimeString();
            $report->name = $data['name'];
            $report->slug = $data['slug'];
            $report->description = $data['description'];
            $report->report_type = $data['report_type'];
            $reportCategory = ReportCategory::where('slug', $data['report_category_slug'])->first();
            $report->report_category_id = $reportCategory->id;
            $report->report_for = $data['report_for'];
            $report->period = $data['period'];
            $report->is_custom_report = $data['is_custom_report'];
            $report->save();

            $standardReports[] = $data['slug'];
        }

        $reportDiff = array_diff($allReports, $standardReports);
        if(!empty($reportDiff)) {
            foreach($reportDiff as $slug) {
                $report = Report::where('slug', $slug)->first();
                $report->delete();
            }
        }
    }
}
