<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPmiServiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->string('pmi_interval', 20)->nullable()->after('invertor_service_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('next_pmi_date')->nullable()->after('last_annual_service_date');
            $table->date('last_pmi_date')->nullable()->after('next_pmi_date');
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
            $table->dropColumn('pmi_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('next_pmi_date');
            $table->dropColumn('last_pmi_date');
        });
    }
}
