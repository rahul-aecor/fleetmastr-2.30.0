<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefectStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // Schema::table('defects', function (Blueprint $table) {
          DB::statement("ALTER TABLE defects CHANGE status status ENUM('Reported','Acknowledged','Resolved','Allocated','Under repair','Repair complete','Repair rejected') NOT NULL");           
       // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE defects CHANGE status status ENUM('Reported','Acknowledged','Resolved') NOT NULL");
    }
}
