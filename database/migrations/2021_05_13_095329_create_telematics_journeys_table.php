<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelematicsJourneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('telematics_journeys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('journey_id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->string('vrn', 50)->index();
            $table->string('dallas_key', 50);
            $table->integer('efficiency_score')->nullable();
            $table->integer('rpm_score')->nullable();
            $table->integer('idle_score')->nullable();
            $table->integer('safety_score')->nullable();
            $table->integer('speeding_score')->nullable();
            $table->integer('cornering_score')->nullable();
            $table->integer('braking_score')->nullable();
            $table->integer('acceleration_score')->nullable();
            $table->dateTime('start_time');
            $table->string('start_lat', 50);
            $table->string('start_lon', 50);
            $table->string('end_lat', 50)->nullable();
            $table->string('end_lon', 50)->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('odometer')->nullable();
            $table->integer('engine_duration')->nullable();
            $table->decimal('fuel',10,2)->nullable();
            $table->decimal('co2',10,2)->nullable();
            $table->integer('gps_idle_duration')->nullable();
            $table->integer('gps_distance')->nullable();
            $table->integer('gps_odo')->nullable();
            $table->string('end_street', 255)->nullable();
            $table->string('end_town', 255)->nullable();
            $table->string('end_post_code', 10)->nullable();
            $table->string('uid', 50)->nullable();
            $table->string('make', 255)->nullable();
            $table->string('model', 255)->nullable();
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
        Schema::drop('telematics_journeys');
    }
}
