<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteAllZonesRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::drop('zones');
        Schema::drop('zone_alerts');
        Schema::drop('zone_alerts_sessions');
        Schema::drop('zone_vehicle_region');
        Schema::drop('zone_vehicle_type');
        Schema::drop('zone_vehicle');
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            //$table->string('region',50);
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')->on('vehicle_regions')->onDelete('cascade');
            $table->enum('zone_status',['active','inactive'])->default('active');
            $table->enum('alert_status',['active','inactive'])->default('active');
            $table->boolean('is_tracking_inside')->default(1)->nullable();
            $table->enum('alert_type',['one_off','regular'])->default('regular')->nullable();
            $table->enum('alert_interval',['1min','5min','30min'])->default('5min')->nullable();
            $table->string('alert_setting', 25)->nullable();
            $table->text('bounds');
            $table->integer('created_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

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

        Schema::create('zone_alerts_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('journey_id')->nullable();
            $table->enum('status',['complete','incomplete'])->default('complete');
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->timestamps();
        });

        Schema::create('zone_vehicle_region', function (Blueprint $table) {
            $table->integer('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->integer('vehicle_region_id')->unsigned()->index();
            $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions')->onDelete('cascade');
        });

        Schema::create('zone_vehicle', function (Blueprint $table) {
            $table->integer('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::create('zone_vehicle_type', function (Blueprint $table) {
            $table->integer('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->integer('vehicle_type_id')->unsigned()->index();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
        });
    }
}
