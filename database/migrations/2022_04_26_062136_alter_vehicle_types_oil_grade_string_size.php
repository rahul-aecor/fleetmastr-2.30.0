<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehicleTypesOilGradeStringSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement("ALTER TABLE vehicle_types CHANGE oil_grade oil_grade VARCHAR(255) NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('vehicle_types', function (Blueprint $table) {
          DB::statement("ALTER TABLE vehicle_types MODIFY `oil_grade` VARCHAR(20) NULL;");
      });
    }
}
