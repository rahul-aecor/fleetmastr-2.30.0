<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeletrackJourneyMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teletrack_journey_details_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('telematics_journey_details_id')->unsigned();
            $table->string('vrn', 50)->index();
            $table->integer('teletrack_journey_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teletrack_journey_details_mapping');
    }
}
