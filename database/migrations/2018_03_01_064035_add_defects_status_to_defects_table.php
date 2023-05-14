<?php

use App\Models\Defect;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefectsStatusToDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE defects CHANGE status status ENUM('Reported','Acknowledged','Resolved',
            'Allocated','Under repair','Discharged','Repair complete','Repair rejected') NOT NULL");   

        Defect::where('status', 'Repair complete')->update(['status'=>'Discharged']);

        DB::statement("ALTER TABLE defects CHANGE status status ENUM('Reported','Acknowledged','Resolved',
            'Allocated','Under repair','Discharged','Repair rejected') NOT NULL"); 

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE defects CHANGE status status ENUM('Reported','Acknowledged','Resolved','Allocated','Under repair','Repair complete','Repair rejected') NOT NULL");
    }
}
