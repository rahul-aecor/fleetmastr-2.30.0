<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleJourneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trakm8_vehicle_journey', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vrn');
            $table->integer('journey_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('trakm8_vehicle_journey');
    }
}
