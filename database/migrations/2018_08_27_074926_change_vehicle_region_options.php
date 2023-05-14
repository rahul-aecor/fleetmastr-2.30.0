<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVehicleRegionOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE vehicles CHANGE vehicle_region vehicle_region ENUM('North','South','Central','Scotland','Head Office') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE vehicles CHANGE vehicle_region vehicle_region ENUM('East','Head Office','North','Scotland','South','West') NOT NULL");
    }
}
