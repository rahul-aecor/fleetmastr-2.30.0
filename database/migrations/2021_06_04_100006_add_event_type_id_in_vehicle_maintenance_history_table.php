<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventTypeIdInVehicleMaintenanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->unsignedInteger('event_type_id')->after('vehicle_id');
            $table->foreign('event_type_id')->references('id')->on('maintenance_events');
        });
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->dropColumn('event_type_id');
            $table->dropForeign(['event_type_id']);
        });
    }
}
