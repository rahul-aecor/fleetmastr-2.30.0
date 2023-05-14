<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelematicsFieldsToVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {     
            $table->string('telematics_ns',150)->nullable()->after('vehicledistancesum');
            $table->integer('telematics_odometer')->unsigned()->nullable()->after('vehicledistancesum');
            $table->double('telematics_lat', 10,8)->nullable()->after('vehicledistancesum');
            $table->double('telematics_lon', 10,7)->nullable()->after('vehicledistancesum');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('telematics_ns');
            $table->dropColumn('telematics_odometer');
            $table->dropColumn('telematics_lat');
            $table->dropColumn('telematics_lon');
        });
    }
}
