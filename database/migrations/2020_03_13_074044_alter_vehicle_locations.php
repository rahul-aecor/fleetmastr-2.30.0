<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehicleLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // Schema::table('vehicle_locations', function (Blueprint $table) {
        //     $table->integer('vehicle_region_id')->unsigned()->nullable()->after('id');
        //     $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions')->onDelete('cascade');
        // });
        Schema::rename('vehicle_locations', 'vehicle_locations_old');
        Schema::create('vehicle_locations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('vehicle_region_id')->unsigned()->nullable();
            $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions')->onDelete('cascade');
           $table->string('name');
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign('vehicles_vehicle_location_id_foreign');
            $table->foreign('vehicle_location_id')->references('id')->on('vehicle_locations');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vehicle_locations');
        Schema::rename('vehicle_locations_old', 'vehicle_locations');
    }
}
