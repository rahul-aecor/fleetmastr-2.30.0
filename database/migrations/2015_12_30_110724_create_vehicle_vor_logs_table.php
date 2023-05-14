<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleVorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_vor_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');//->onDelete('cascade');
            $table->datetime('dt_off_road');
            $table->datetime('dt_back_on_road')->nullable();
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
        Schema::drop('vehicle_vor_logs');
    }
}
