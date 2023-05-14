<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelematicsPostcodeToVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles',function ($table){
            //MAX(jd.speed) AS mxmph,AVG(jd.speed) AS avgmph,MIN(jd.odometer) AS journeyStart,MAX(jd.odometer) AS journeyEnd
            $table->string('telematics_postcode',10)->default(NULL)->nullable()->after('vehicledistancesum');
            $table->string('telematics_street',255)->default(NULL)->nullable()->after('vehicledistancesum');
            $table->string('telematics_town',255)->default(NULL)->nullable()->after('vehicledistancesum');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles',function ($table){
            $table->dropColumn('telematics_postcode');
            $table->dropColumn('telematics_street');
            $table->dropColumn('telematics_town');
        });
    }
}
