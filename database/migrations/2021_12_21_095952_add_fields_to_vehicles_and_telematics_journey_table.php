<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToVehiclesAndTelematicsJourneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles',function ($table){
            $table->float('vehiclefuelsum')->nullable()->after('last_service_distance_notification_odometer');
            $table->integer('vehicledistancesum')->unsigned()->nullable()->after('vehiclefuelsum');
        });

        Schema::table('telematics_journeys',function ($table){
            $table->smallInteger('incident_count')->unsigned()->nullable()->after('raw_json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles',function ($table){
            $table->dropColumn('vehiclefuelsum');
            $table->dropColumn('vehicledistancesum');
        });

        Schema::table('telematics_journeys',function ($table){
            $table->dropColumn('incident_count');
        });
    }
}
