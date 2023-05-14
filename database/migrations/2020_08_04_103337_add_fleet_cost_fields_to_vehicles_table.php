<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFleetCostFieldsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->longtext('lease_cost')->nullable()->after('staus_owned_leased');
            $table->longtext('maintenance_cost')->nullable()->after('lease_cost');
            $table->longtext('monthly_depreciation_cost')->nullable()->after('maintenance_cost');
            $table->longtext('insurance_cost')->nullable()->after('monthly_depreciation_cost');
            $table->boolean('is_insurance_cost_override')->after('insurance_cost')->default(0);
            $table->longtext('telematics_cost')->nullable()->after('is_insurance_cost_override');
            $table->boolean('is_telematics_cost_override')->after('telematics_cost')->default(0);
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
            $table->dropColumn('lease_cost');
            $table->dropColumn('maintenance_cost');
            $table->dropColumn('monthly_depreciation_cost');
            $table->dropColumn('insurance_cost');
            $table->dropColumn('is_insurance_cost_override');
            $table->dropColumn('telematics_cost');
            $table->dropColumn('is_telematics_cost_override');
        });
    }
}
