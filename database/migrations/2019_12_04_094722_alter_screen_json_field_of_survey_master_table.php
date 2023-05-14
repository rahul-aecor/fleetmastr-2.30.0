<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScreenJsonFieldOfSurveyMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE survey_master CHANGE COLUMN `screen_json` `screen_json` LONGTEXT NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE survey_master CHANGE COLUMN `screen_json` `screen_json` TEXT NOT NULL;");
    }
}
