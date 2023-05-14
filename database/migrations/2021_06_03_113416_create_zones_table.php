<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            //$table->string('region',50);
            $table->integer('region_id')->unsigned()->nullable();
            $table->foreign('region_id')->references('id')->on('vehicle_regions')->onDelete('cascade');
            $table->enum('zone_status',['active','inactive'])->default('active');
            $table->enum('alert_status',['active','inactive'])->default('active');
            $table->enum('alert_type',['one_off','regular'])->default('regular');
            $table->enum('alert_interval',['1min','5min','30min'])->default('5min');
            $table->text('bounds');
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
        Schema::drop('zones');
    }
}