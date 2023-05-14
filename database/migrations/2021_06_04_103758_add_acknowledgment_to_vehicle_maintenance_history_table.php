<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcknowledgmentToVehicleMaintenanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->boolean('is_safety_inspection_in_accordance_with_dvsa')->after('event_status')->nullable();
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
            $table->dropColumn('is_safety_inspection_in_accordance_with_dvsa');
        });
    }
}
