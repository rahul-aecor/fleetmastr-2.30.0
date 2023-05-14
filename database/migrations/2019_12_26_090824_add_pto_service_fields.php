<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPtoServiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->string('pto_service_interval', 20)->nullable()->after('service_inspection_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('next_pto_service_date')->nullable()->after('dt_annual_service_inspection');
            $table->date('last_pto_service_date')->nullable()->after('next_pto_service_date');
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
            $table->dropColumn('pto_service_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('next_pto_service_date');
            $table->dropColumn('last_pto_service_date');
        });
    }
}
