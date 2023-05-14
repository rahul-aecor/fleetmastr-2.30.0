<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_master', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('vehicle_category',['hgv','non-hgv']);
            $table->enum('action',['checkin','checkout','defect','on-call']);
            $table->string('vehicle_type',40)->default('all');
            $table->string('desc',255);
            $table->text('screen_json');
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
        Schema::drop('survey_master');
    }
}
