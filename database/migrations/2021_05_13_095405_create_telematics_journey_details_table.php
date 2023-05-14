<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelematicsJourneyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telematics_journey_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('telematics_journey_id')->unsigned()->nullable();//as heartbeat ns will also be stored in this table but it wont be related to any journey

            /*$table->integer('telematics_journey_id')->unsigned()->index();
            $table->foreign('telematics_journey_id')->references('id')->on('telematics_journeys');*/
            /*$table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');*/
            $table->string('vrn', 50)->index();
            $table->string('dallas_key', 50);
            $table->string('ns');
            $table->integer('odometer')->nullable();
            $table->string('lat', 50);
            $table->string('lon', 50);
            $table->dateTime('time');
            $table->integer('speed')->nullable();
            $table->integer('gps_odo')->nullable();
            $table->string('street', 255)->nullable();
            $table->string('town', 255)->nullable();
            $table->string('post_code', 10)->nullable();
            $table->integer('idle_duration')->nullable();
            
            $table->integer('gps_distance')->nullable();
            $table->string('heading', 255)->nullable();
            $table->string('mile', 255)->nullable();
            $table->string('vin', 50)->nullable();
            
            $table->integer('ex_idle_threshold')->nullable();
            $table->integer('street_speed')->nullable();
            $table->integer('idle_threshold')->nullable();
            $table->integer('num_stats')->nullable();
            $table->longText('raw_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('telematics_journey_details');
    }
}
