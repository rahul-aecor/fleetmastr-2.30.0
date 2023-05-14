<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehicleTypeOfSurveyMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE survey_master CHANGE COLUMN `vehicle_type` `vehicle_type` VARCHAR(255) NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE survey_master CHANGE COLUMN `vehicle_type` `vehicle_type` VARCHAR(40) NOT NULL;");
    }
}
