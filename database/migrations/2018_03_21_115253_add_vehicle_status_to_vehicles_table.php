<?php

use App\Models\Vehicle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVehicleStatusToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE vehicles CHANGE status status ENUM('Archive','Archived',
            'Archived - De-commissioned','Archived - Written off','Awaiting kit','Re-positioning','Roadworthy',
            'Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop',
            'VOR - MOT','VOR - Service','VOR - Quarantined','Other') NOT NULL");  

        Vehicle::where('status', 'Archive')->update(['status'=>'Archived']);

        DB::statement("ALTER TABLE vehicles CHANGE status status ENUM('Archived','Archived - De-commissioned','Archived - Written off','Awaiting kit','Re-positioning','Roadworthy','Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop','VOR - MOT','VOR - Service','VOR - Quarantined','Other') NOT NULL"); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE vehicles CHANGE status status ENUM('Archive','Archived - De-commissioned','Archived - Written off','Awaiting kit','Re-positioning','Roadworthy','Roadworthy (with defects)','VOR','VOR - Accident damage','VOR - Bodybuilder','VOR - Bodyshop','VOR - MOT','VOR - Service','VOR - Quarantined','Other') NOT NULL");
    }
}
