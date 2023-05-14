<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('registration',15)->unique();
            $table->integer('vehicle_type_id')->unsigned()->index();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types');//->onDelete('cascade');
            $table->enum('status',['Archive','Archived - De-commissioned','Archived - Written off','Awaiting kit','Re-positioning','Roadworthy','Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop','VOR - MOT','VOR - Service','VOR - Quarantined','Other']);
            $table->date('dt_added_to_fleet');
            $table->integer('last_odometer_reading');
            $table->enum('odometer_reading_unit', ['km','miles'])->default('km');
            $table->date('dt_registration')->nullable();
            $table->string('chassis_number',50)->unique()->nullable();
            $table->string('contract_id',50)->nullable();
            $table->integer('vehicle_location_id')->unsigned()->nullable()->index();
            $table->foreign('vehicle_location_id')->references('id')->on('vehicle_locations');//->onDelete('cascade');
            $table->integer('vehicle_repair_location_id')->unsigned()->nullable()->index();
            $table->foreign('vehicle_repair_location_id')->references('id')->on('vehicle_repair_locations');//->onDelete('cascade');
            $table->enum('vehicle_region',['East','Head Office','North','Scotland','South','West']);
            $table->date('dt_repair_expiry')->nullable();
            $table->date('dt_mot_expiry')->nullable();
            $table->date('dt_next_service_inspection')->nullable();
            $table->date('dt_tacograch_calibration_due')->nullable();
            $table->string('service_inspection_interval_hgv',50)->nullable();
            $table->string('service_inspection_interval_non_hgv',50)->nullable();
            $table->boolean('on_road')->default(false);
            $table->boolean('masternaut')->nullable();
            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')->references('id')->on('users');//->onDelete('cascade');
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')->references('id')->on('users');//->onDelete('cascade');
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
        Schema::drop('vehicles');
    }
}
