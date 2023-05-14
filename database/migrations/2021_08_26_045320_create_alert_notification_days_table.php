<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertNotificationDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_notification_days', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('alerts_id')->unsigned()->nullable();
            $table->foreign('alerts_id')->references('id')->on('alerts');
            $table->string('day');
            $table->boolean('is_all_day')->default(0);
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
        Schema::dropIfExists('alert_notification_days');
    }
}
