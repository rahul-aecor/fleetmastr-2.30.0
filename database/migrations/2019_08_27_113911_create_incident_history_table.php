<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncidentHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incident_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->index();
            $table->foreign('incident_id')->references('id')->on('incidents');//->onDelete('cascade');
            $table->enum('type', ['system','user'])->default('user');
            $table->text('comments');
            $table->string('incident_status_comment')->nullable();
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
        Schema::drop('incident_history');
    }
}
