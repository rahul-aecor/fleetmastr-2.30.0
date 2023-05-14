<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelematicsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telematics_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->string('hardware_id',10);
            $table->string('latitude',10)->nullable();
            $table->string('longitude',10)->nullable();            
            $table->string('driver_id',10)->nullable();            
            $table->integer('fuel_used')->nullable();
            $table->float('distance_covered')->nullable();
            $table->string('journey_id',5)->nullable();
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
        Schema::drop('telematics_data');
    }

}
