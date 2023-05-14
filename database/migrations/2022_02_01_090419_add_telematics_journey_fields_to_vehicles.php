<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelematicsJourneyFieldsToVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles',function ($table){
            $table->dateTime('telematics_latest_location_time')->default(NULL)->nullable()->after('vehicledistancesum');
            $table->integer('telematics_latest_journey_id')->unsigned()->default(NULL)->nullable()->after('vehicledistancesum');
            $table->dateTime('telematics_latest_journey_time')->default(NULL)->nullable()->after('vehicledistancesum');
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
            $table->dropColumn('telematics_latest_location_time');
            $table->dropColumn('telematics_latest_journey_id');
            $table->dropColumn('telematics_latest_journey_time');
        });
    }
}
