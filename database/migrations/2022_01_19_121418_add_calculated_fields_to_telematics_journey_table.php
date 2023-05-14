<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCalculatedFieldsToTelematicsJourneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telematics_journeys',function ($table){
            //MAX(jd.speed) AS mxmph,AVG(jd.speed) AS avgmph,MIN(jd.odometer) AS journeyStart,MAX(jd.odometer) AS journeyEnd
            $table->smallInteger('max_speed')->unsigned()->default(NULL)->nullable()->after('raw_json');
            $table->float('avg_speed')->unsigned()->default(NULL)->nullable()->after('raw_json');
            $table->integer('odometer_start')->unsigned()->default(NULL)->nullable()->after('raw_json');
            $table->integer('odometer_end')->unsigned()->default(NULL)->nullable()->after('raw_json');
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
            $table->dropColumn('max_speed');
            $table->dropColumn('avg_speed');
            $table->dropColumn('odometer_start');
            $table->dropColumn('odometer_end');
        });
    }
}
