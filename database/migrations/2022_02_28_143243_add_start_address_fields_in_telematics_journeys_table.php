<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartAddressFieldsInTelematicsJourneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journeys',function ($table){
            $table->string('start_street', 255)->nullable()->after('gps_odo');
            $table->string('start_town', 255)->nullable()->after('start_street');
            $table->string('start_post_code', 10)->nullable()->after('start_town');
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
            $table->dropColumn('start_street');
            $table->dropColumn('start_town');
            $table->dropColumn('start_post_code');
        });
    }
}
