<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreexistingDefectAcknowledgement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preexisting_defect_acknowledgement', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('check_id')->unsigned();
            $table->foreign('check_id')->references('id')->on('checks')->onDelete('cascade');
            $table->integer('defect_id')->unsigned();
            $table->foreign('defect_id')->references('id')->on('defects')->onDelete('cascade');
            //status : 1 - Keep 0 - Discard
            $table->tinyInteger('status')->unsigned()->default('1');
	    // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('preexisting_defect_acknowledgement');
    }
}
