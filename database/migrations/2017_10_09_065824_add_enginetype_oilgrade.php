<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnginetypeOilgrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->string('engine_type',60)->nullable()->after("fuel_type");
            $table->string('oil_grade',20)->nullable()->after("engine_type");
            $table->integer('length')->nullable()->after("oil_grade");
            $table->integer('width')->nullable()->after("length");
            $table->integer('height')->nullable()->after("width");
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
            $table->dropColumn(['engine_type']);
            $table->dropColumn(['oil_grade']);
            $table->dropColumn(['length']);
            $table->dropColumn(['width']);
            $table->dropColumn(['height']);
        });
    }
}
