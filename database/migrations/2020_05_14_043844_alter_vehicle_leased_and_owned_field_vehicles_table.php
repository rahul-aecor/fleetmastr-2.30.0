<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehicleLeasedAndOwnedFieldVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            DB::statement("ALTER TABLE vehicles MODIFY `annual_maintenance_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_insurance` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_telematice_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `monthly_lease_cost` float(8,2);");
            DB::statement("ALTER TABLE vehicles MODIFY `excess_cost_per_mile` float(8,2);");
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
            DB::statement("ALTER TABLE vehicles MODIFY `annual_maintenance_cost` INT NULL;");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_insurance` INT NULL;");
            DB::statement("ALTER TABLE vehicles MODIFY `annual_telematice_cost` INT NULL;");
            DB::statement("ALTER TABLE vehicles MODIFY `monthly_lease_cost` INT NULL;");
            DB::statement("ALTER TABLE vehicles MODIFY `excess_cost_per_mile` INT NULL;");
        });
    }
}
