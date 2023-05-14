<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInspectionDisposedFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('dt_first_use_inspection')->nullable()->after("vehicle_region");            
            $table->date('dt_vehicle_disposed')->nullable()->after("dt_first_use_inspection");            
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
            $table->dropColumn('dt_first_use_inspection');
            $table->dropColumn('dt_vehicle_disposed');
        });
    }
}
