<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vehicle_type',60);
            $table->enum('vehicle_category',['hgv','non-hgv']);
            $table->string('manufacturer',60);
            $table->string('model',40);
            $table->string('model_picture',512);
            $table->string('tyre_size_drive',20);
            $table->string('tyre_size_steer',20);
            $table->string('tyre_pressure_drive',10);
            $table->string('tyre_pressure_steer',10);
            $table->string('nut_size',10);
            $table->string('re_torque',40);
            $table->string('body_builder',30);
            $table->string('fuel_type',20);
            $table->string('gross_vehicle_weight',40);
            $table->string('service_inspection_interval',100);
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
        Schema::drop('vehicle_types');
    }
}
