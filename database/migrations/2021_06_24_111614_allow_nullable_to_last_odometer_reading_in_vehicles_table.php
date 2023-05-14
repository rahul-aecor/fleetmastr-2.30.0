<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullableToLastOdometerReadingInVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `vehicles` CHANGE `last_odometer_reading` `last_odometer_reading` INT NULL');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `vehicles` CHANGE `last_odometer_reading` `last_odometer_reading` INT NOT NULL');
    }
}
