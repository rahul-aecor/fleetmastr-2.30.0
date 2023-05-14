<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleAssignmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_assignment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->integer('vehicle_division_id')->unsigned()->index();
            $table->foreign('vehicle_division_id')->references('id')->on('vehicle_divisions');
            $table->integer('vehicle_region_id')->unsigned()->index();
            $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions');
            $table->integer('vehicle_location_id')->unsigned()->nullable()->index();
            $table->foreign('vehicle_location_id')->references('id')->on('vehicle_locations');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
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
        Schema::drop('vehicle_assignment');
    }
}
