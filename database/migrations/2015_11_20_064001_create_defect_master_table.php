<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defect_master', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('order');
            $table->string('page_title',100);
            $table->string('app_question',255);
            $table->string('app_question_with_defect',255);
            $table->string('defect',255);
            $table->tinyInteger('defect_order')->unsigned()->default('0');
            $table->boolean('has_image');
            $table->boolean('has_text');
            $table->boolean('is_prohibitional');
            $table->string('safety_notes',255);
            $table->boolean('for_hgv');
            $table->boolean('for_non-hgv');
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
        Schema::drop('defect_master');
    }
}
