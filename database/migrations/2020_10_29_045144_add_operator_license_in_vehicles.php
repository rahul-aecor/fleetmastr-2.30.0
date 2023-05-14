<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOperatorLicenseInVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('vehicles', function (Blueprint $table) {
          $table->string('operator_license')->after('dt_registration')->nullable();
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
          $table->dropColumn('operator_license');
      });
    }
}
