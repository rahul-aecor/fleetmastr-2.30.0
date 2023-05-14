<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');//->onDelete('cascade');
            $table->integer('check_id')->unsigned()->index();
            $table->foreign('check_id')->references('id')->on('checks');//->onDelete('cascade');
            $table->integer('defect_master_id')->unsigned()->index();
            $table->foreign('defect_master_id')->references('id')->on('defect_master');//->onDelete('cascade');
            $table->string('description',255);
            $table->string('comments',255);
            $table->enum('status', ['Reported','Acknowledged','Resolved']);
            $table->date('est_completion_date')->nullable();
            // $table->integer('image_id')->unsigned()->unique()->index();
            // $table->foreign('image_id')->references('id')->on('images');//->onDelete('cascade');
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
        Schema::drop('defects');
    }
}
