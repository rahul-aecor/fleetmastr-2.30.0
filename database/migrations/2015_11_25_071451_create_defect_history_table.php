<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defect_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('defect_id')->unsigned()->index();
            $table->foreign('defect_id')->references('id')->on('defects');//->onDelete('cascade');
            $table->enum('type', ['system','user'])->default('user');
            $table->text('comments');
            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')->references('id')->on('users');//->onDelete('cascade');
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')->references('id')->on('users');//->onDelete('cascade');
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
        Schema::drop('defect_history');
    }
}
