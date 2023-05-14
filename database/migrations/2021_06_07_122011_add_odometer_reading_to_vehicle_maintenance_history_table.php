<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOdometerReadingToVehicleMaintenanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->integer('event_planned_distance')->after('is_safety_inspection_in_accordance_with_dvsa')->nullable();
            $table->integer('odomerter_reading')->after('event_planned_distance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->dropColumn(['event_planned_distance', 'odomerter_reading']);
        });
    }
}
