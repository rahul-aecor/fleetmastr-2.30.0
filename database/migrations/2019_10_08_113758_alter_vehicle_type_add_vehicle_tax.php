<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehicleTypeAddVehicleTax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function ($table) {
            $table->longtext('vehicle_tax')->nullable()->after('engine_size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_types', function ($table) {
            $table->dropColumn(['vehicle_tax']);
        });
    }
}
