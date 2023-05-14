<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexTelematicsJourneysDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journey_details',function ($table){
	        $table->index(['telematics_journey_id']);
            $table->index(['time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telematics_journey_details',function ($table){
            $table->dropIndex(['telematics_journey_id']);
            $table->dropIndex(['time']);
        });
    }
}
