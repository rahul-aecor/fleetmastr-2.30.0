<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('alerts_id')->unsigned()->nullable();
            $table->foreign('alerts_id')->references('id')->on('alerts');
            $table->integer('vehicle_id')->unsigned()->index()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->dateTime('alert_date_time');
            $table->integer('journey_id')->unsigned()->index()->nullable();
            $table->foreign('journey_id')->references('id')->on('telematics_journeys');
            $table->boolean('is_open')->default(0);
            $table->string('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_notifications');
    }
}
