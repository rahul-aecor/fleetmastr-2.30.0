<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArchivedSoldInVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          DB::statement("ALTER TABLE vehicles CHANGE status status ENUM('Archived','Archived - De-commissioned','Archived - Written off','Archived - Sold','Awaiting kit','Re-positioning','Roadworthy','Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop','VOR - MOT','VOR - Quarantined','VOR - Service','Other') NOT NULL");

          Schema::table('vehicles', function (Blueprint $table) {
              $table->date('archived_date')->after('status')->nullable();
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE vehicles CHANGE status status ENUM('Archived','Archived - De-commissioned','Archived - Written off','Awaiting kit','Re-positioning','Roadworthy','Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop','VOR - MOT','VOR - Quarantined','VOR - Service','Other') NOT NULL");

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('archived_date');
        });
    }
}
