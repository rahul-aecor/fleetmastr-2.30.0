<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeclarationInDefectMasterTableTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE defect_master CHANGE type type ENUM('yesno','media','media_based_on_selection','multiinput','dropdown','declaration') NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE defect_master CHANGE type type ENUM('yesno','media','media_based_on_selection','multiinput','dropdown') NULL");
    }
}