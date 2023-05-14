<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeAndOutcomeFieldInVehicleMaintenanceHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->enum('mot_outcome',['Fail','Pass','PRS'])->after('event_date')->nullable();
            $table->enum('mot_type',['Initial','Re-test'])->after('event_date')->nullable();
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
            $table->dropColumn('mot_type');
            $table->dropColumn('mot_outcome');
        });
    }
}
