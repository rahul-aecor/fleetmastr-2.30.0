<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVehicleTableUnwantedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('dt_last_mot');
            $table->dropColumn('dt_last_tachograph_service');
            $table->dropColumn('dt_last_loler_annual_check');
            $table->dropColumn('last_pto_service_date');
            $table->dropColumn('last_invertor_service_date');
            $table->dropColumn('last_pmi_date');
            $table->dropColumn('last_compressor_service');
            $table->dropColumn('last_annual_service_date');
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
            $table->date('dt_last_mot')->nullable()->after('dt_mot_expiry');
            $table->date('dt_last_tachograph_service')->nullable()->after('dt_tacograch_calibration_due');
            $table->date('dt_last_loler_annual_check')->nullable()->after('dt_loler_test_due');
            $table->date('last_pto_service_date')->nullable()->after('next_pto_service_date');
            $table->date('last_invertor_service_date')->nullable()->after('next_invertor_service_date');
            $table->date('last_pmi_date')->nullable()->after('next_pmi_date');
            $table->date('last_compressor_service')->nullable()->after('next_compressor_service');
            $table->date('last_annual_service_date')->nullable()->after('dt_annual_service_inspection');
        });
    }
}
