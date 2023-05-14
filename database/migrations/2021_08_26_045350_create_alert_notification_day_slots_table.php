<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertNotificationDaySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_notification_day_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('alert_notification_days_id')->unsigned()->nullable();
            $table->foreign('alert_notification_days_id')->references('id')->on('alert_notification_days');
            $table->time('from_time');
            $table->time('to_time');
            $table->boolean('is_on')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_centre_notification_slots');
    }
}
