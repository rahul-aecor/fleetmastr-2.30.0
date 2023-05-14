<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVehicleCostToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('staus_owned_leased')->nullable()->after('usage_type');
            $table->integer('annual_maintenance_cost')->nullable()->after('staus_owned_leased');
            $table->integer('annual_vehicle_cost')->nullable()->after('annual_maintenance_cost');
            $table->integer('annual_insurance')->nullable()->after('annual_vehicle_cost');
            $table->integer('annual_telematice_cost')->nullable()->after('annual_insurance');
            $table->longtext('manual_cost_adjustment')->nullable()->after('annual_telematice_cost');
            $table->integer('miles_per_month')->nullable()->after('manual_cost_adjustment');
            $table->longtext('fuel_use')->nullable()->after('miles_per_month');
            $table->longtext('oil_use')->nullable()->after('fuel_use');
            $table->longtext('adblue_use')->nullable()->after('oil_use');
            $table->longtext('screen_wash_use')->nullable()->after('adblue_use');
            $table->longtext('fleet_livery_wash')->nullable()->after('screen_wash_use');
            $table->integer('monthly_lease_cost')->nullable()->after('fleet_livery_wash');
            $table->integer('permitted_annual_mileage')->nullable()->after('monthly_lease_cost');
            $table->integer('excess_cost_per_mile')->nullable()->after('permitted_annual_mileage');


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
            $table->dropColumn(['manual_cost_adjustment']);
        });
    }
}
