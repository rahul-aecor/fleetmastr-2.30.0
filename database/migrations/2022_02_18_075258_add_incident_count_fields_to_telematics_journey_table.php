<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncidentCountFieldsToTelematicsJourneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->smallInteger('idling_count')->unsigned()->nullable()->after('incident_count');
            $table->smallInteger('rpm_count')->unsigned()->nullable()->after('incident_count');
            $table->smallInteger('speeding_count')->unsigned()->nullable()->after('incident_count');
            $table->smallInteger('harsh_cornering_count')->unsigned()->nullable()->after('incident_count');
            $table->smallInteger('harsh_acceleration_count')->unsigned()->nullable()->after('incident_count');
            $table->smallInteger('harsh_breaking_count')->unsigned()->nullable()->after('incident_count');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->dropColumn('idling_count');
            $table->dropColumn('rpm_count');
            $table->dropColumn('speeding_count');
            $table->dropColumn('harsh_cornering_count');
            $table->dropColumn('harsh_acceleration_count');
            $table->dropColumn('harsh_breaking_count');
        });
    }
}
