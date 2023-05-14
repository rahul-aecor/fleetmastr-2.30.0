<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDivisionRegionLocationIdsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('vehicle_division_id')->unsigned()->after('vehicle_division')->nullable();
            $table->foreign('vehicle_division_id')->references('id')->on('vehicle_divisions')->onDelete('set NULL');
            $table->integer('vehicle_region_id')->unsigned()->after('vehicle_region')->nullable();
            $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions')->onDelete('set NULL');
        });
         DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign('vehicles_vehicle_division_id_foreign');
            $table->dropColumn('vehicle_division_id');

            $table->dropForeign('vehicles_vehicle_region_id_foreign');
            $table->dropColumn('vehicle_region_id');
        });
    }
}
