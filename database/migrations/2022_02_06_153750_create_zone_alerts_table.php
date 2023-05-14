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
            $table->integer('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->string('vrn', 25);
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('journey_details_id')->nullable();
            // $table->integer('journey_id')->nullable();
            $table->tinyInteger('is_alert')->nullable(); // 0 - On Exit, 1 - On Entry, 2 - On Entry and Exit
            $table->string('ns', 25)->nullable();
            $table->float('speed',4,2)->nullable();
            $table->string('max_acceleration', 4)->nullable();
            $table->string('direction', 15)->nullable();;
            $table->text('address')->nullable();
            $table->string('latitude',10)->nullable();
            $table->string('longitude',10)->nullable();
            $table->datetime('start_time');
            // $table->datetime('end_time')->nullable();
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
