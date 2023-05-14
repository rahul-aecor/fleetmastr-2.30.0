<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanningFieldsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('dt_tax_expiry')->nullable()->after('dt_tacograch_calibration_due');
            $table->date('dt_annual_service_inspection')->nullable()->after('dt_tax_expiry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('dt_tacograch_calibration_due');
            $table->dropColumn('dt_tax_expiry');
        });
    }
}
