<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftdeletesToJourneysAndJourneyDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->softDeletes();
        });
        Schema::table('telematics_journey_details',function ($table){
            $table->softDeletes();
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
            $table->dropSoftDeletes();
        });
        Schema::table('telematics_journey_details',function ($table){
            $table->dropSoftDeletes();
        });
    }
}
