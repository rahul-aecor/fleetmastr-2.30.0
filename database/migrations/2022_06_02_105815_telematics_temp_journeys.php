<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TelematicsTempJourneys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telematics_temp_journeys', function (Blueprint $table) {
            // $table->increments('id');
            $table->integer('journey_id');
            $table->string('vrn', 50)->index();
            $table->string('uid', 50)->nullable();
            $table->longText('raw_json')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('telematics_temp_journeys');
    }
}
