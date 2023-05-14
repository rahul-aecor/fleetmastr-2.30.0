<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelematicsFieldsInVehiclsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('supplier',['prolius','trakm8','webfleet','other'])->after('is_telematics_enabled')->nullable();
            $table->string('device')->after('supplier')->nullable();
            $table->string('serial_id')->after('device')->nullable();
            $table->date('installation_date')->after('serial_id')->nullable();
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
            $table->dropColumn('supplier');
            $table->dropColumn('device');
            $table->dropColumn('serial_id');
            $table->dropColumn('installation_date');
        });
    }
}
