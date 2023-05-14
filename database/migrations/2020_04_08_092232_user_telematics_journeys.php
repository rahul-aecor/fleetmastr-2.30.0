<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserTelematicsJourneys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_telematics_journeys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->integer('journey_id');
            $table->string('registration',15);
            $table->string('start_lat', 60)->nullable();
            $table->string('start_lon', 60)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->string('start_street', 255)->nullable();
            $table->string('start_town', 255)->nullable();
            $table->string('start_post_code', 255)->nullable();
            $table->string('end_lat', 60)->nullable();
            $table->string('end_lon', 60)->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('end_street', 255)->nullable();
            $table->string('end_town', 255)->nullable();
            $table->string('end_post_code', 255)->nullable();
            $table->softDeletes();
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
        Schema::drop('user_telematics_journeys');
    }
}
