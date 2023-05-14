<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE vehicles CHANGE COLUMN `dt_added_to_fleet` `dt_added_to_fleet` DATE NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE vehicles CHANGE COLUMN `dt_added_to_fleet` `dt_added_to_fleet` DATE NOT NULL;");
    }
}
