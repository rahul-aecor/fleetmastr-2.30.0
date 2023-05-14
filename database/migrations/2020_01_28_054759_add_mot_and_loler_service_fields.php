<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMotAndLolerServiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('dt_last_mot')->nullable()->after('dt_mot_expiry');
            $table->date('dt_last_loler_annual_check')->nullable()->after('dt_loler_test_due');
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
            $table->dropColumn('dt_last_mot');
            $table->dropColumn('dt_last_loler_annual_check');
        });
    }
}
