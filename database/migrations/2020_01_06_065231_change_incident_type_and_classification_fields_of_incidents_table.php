<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIncidentTypeAndClassificationFieldsOfIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE incidents MODIFY incident_type VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE incidents MODIFY classification VARCHAR(255) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE incidents MODIFY incident_type ENUM('Glass damage','Pedestrian incident','Stolen vehicle','Traffic incident','Other') NOT NULL");
        DB::statement("ALTER TABLE incidents MODIFY classification ENUM('Option 1','Option 2','Option 3') NOT NULL");
    }
}
