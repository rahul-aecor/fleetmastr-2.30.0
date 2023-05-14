<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvertorServiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->string('invertor_service_interval', 20)->nullable()->after('pto_service_interval');
        });
        
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('next_invertor_service_date')->nullable()->after('last_pto_service_date');
            $table->date('last_invertor_service_date')->nullable()->after('next_invertor_service_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('invertor_service_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('next_invertor_service_date');
            $table->dropColumn('last_invertor_service_date');
        });
    }
}
