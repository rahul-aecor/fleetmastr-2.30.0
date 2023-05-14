<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDefectMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE defect_master CHANGE COLUMN `order` `order` INT SIGNED NOT NULL;");
        DB::statement('ALTER TABLE defect_master MODIFY defect VARCHAR(255) NULL DEFAULT NULL;');
        DB::statement('ALTER TABLE defect_master MODIFY safety_notes VARCHAR(255) NULL DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE defect_master CHANGE COLUMN `order` `order` TINYINT SIGNED NOT NULL;");
        DB::statement('ALTER TABLE defect_master MODIFY defect VARCHAR(255) NOT NULL;');
        DB::statement('ALTER TABLE defect_master MODIFY safety_notes VARCHAR(255) NOT NULL;');
    }
}
