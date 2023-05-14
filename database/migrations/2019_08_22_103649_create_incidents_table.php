<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->enum('status', ['Reported','Under investigation','Allocated', 'Closed'])->default('Reported');
            $table->dateTime('incident_date_time');
            $table->enum('incident_type', ['Glass damage','Pedestrian incident','Stolen vehicle', 'Traffic incident', 'Other']);
            $table->enum('classification', ['Option 1','Option 2','Option 3']);
            $table->enum('allocated_to', ['Company','Insurance company','Insurance broker'])->nullable();
            $table->enum('is_reported_to_insurance', ['Yes', 'No']);
            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')->references('id')->on('users');
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')->references('id')->on('users');            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('incidents');
    }
}
