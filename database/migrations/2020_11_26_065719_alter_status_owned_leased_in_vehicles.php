<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatusOwnedLeasedInVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement("ALTER TABLE vehicles CHANGE staus_owned_leased staus_owned_leased ENUM('Contract','Leased','Owned',
          'Hire purchase','Hired') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('vehicles', function (Blueprint $table) {
          DB::statement("ALTER TABLE vehicles MODIFY `staus_owned_leased` INT NULL;");
      });
    }
}
