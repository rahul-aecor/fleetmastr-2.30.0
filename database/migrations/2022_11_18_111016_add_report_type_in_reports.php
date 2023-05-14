<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportTypeInReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports',function ($table){
            $table->enum('report_type', ['general','telematics'])->after('description')->default('general');
        });

        DB::statement("UPDATE reports SET report_type = 'telematics' WHERE slug IN ('standard_driver_behaviour_report', 'standard_fuel_usage_and_emission_report', 'standard_user_incident_report', 'standard_user_journey_report', 'standard_vehicle_behaviour_report', 'standard_vehicle_incident_report', 'standard_vehicle_journey_report');");
        DB::statement("UPDATE reports SET deleted_at = CURRENT_TIMESTAMP() WHERE slug = 'standard_vehicle_planning_report';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE reports SET deleted_at = NULL WHERE slug = 'standard_vehicle_planning_report';");

        Schema::table('reports',function ($table){
            $table->dropColumn('report_type');
        });
    }
}
