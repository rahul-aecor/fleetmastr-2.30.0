<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZoneAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zone_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zone_alert_session_id')->unsigned()->index();
            $table->foreign('zone_alert_session_id')->references('id')->on('zone_alerts_sessions')->onDelete('cascade');
            $table->float('speed',4,2)->nullable();
            $table->string('max_acceleration', 4)->nullable();
            $table->string('direction', 15)->nullable();;
            $table->text('address')->nullable();
            $table->string('latitude',10)->nullable();
            $table->string('longitude',10)->nullable();
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
        Schema::drop('zone_alerts');
    }
}
