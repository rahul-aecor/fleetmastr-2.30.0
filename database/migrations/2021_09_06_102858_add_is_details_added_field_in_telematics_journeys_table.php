<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDetailsAddedFieldInTelematicsJourneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->tinyInteger('is_details_added')->after('model')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->dropColumn('is_details_added');
        });
    }
}
