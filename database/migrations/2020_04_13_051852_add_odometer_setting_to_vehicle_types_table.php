<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOdometerSettingToVehicleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->enum('odometer_setting', ['km','miles'])->after('vehicle_category')->default('km');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('odometer_reading_unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('odometer_setting');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('odometer_reading_unit', ['km','miles'])->after('last_odometer_reading')->default('km');
        });
    }
}
