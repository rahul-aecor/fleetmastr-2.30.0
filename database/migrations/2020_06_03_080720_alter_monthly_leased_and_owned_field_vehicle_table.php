<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMonthlyLeasedAndOwnedFieldVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            DB::statement("ALTER TABLE vehicles MODIFY `annual_maintenance_cost` double(16,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_insurance` double(16,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_telematice_cost` double(16,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `monthly_lease_cost` double(16,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `excess_cost_per_mile` double(16,2);");
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
            DB::statement("ALTER TABLE vehicles MODIFY `annual_maintenance_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_insurance` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_telematice_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `monthly_lease_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `excess_cost_per_mile` float(8,2);");
        });
    }
}
