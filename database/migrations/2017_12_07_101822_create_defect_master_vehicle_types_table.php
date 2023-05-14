<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectMasterVehicleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defect_master_vehicle_types', function (Blueprint $table) {
            $table->integer('vehicle_type_id')->unsigned()->index();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types');
            $table->string('vehicle_type_name');
            $table->string('defect_list');
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
        Schema::drop('defect_master_vehicle_types');
    }
}
