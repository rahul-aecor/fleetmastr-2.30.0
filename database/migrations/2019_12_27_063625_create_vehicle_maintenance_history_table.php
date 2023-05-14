<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleMaintenanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_maintenance_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id')->unsigned()->index();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->enum('event_type', ['annual_service_inspection', 'mot', 'preventative_maintenance_inspection', 'vehicle_tax', 'next_service_inspection', 'pto_service_inspection', 'invertor_inspection', 'compressor_inspection', 'loler_test', 'tachograph_calibration']);
            $table->date('event_date');
            $table->text('comment');
            $table->integer('created_by')->nullable()->unsigned();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('updated_by')->nullable()->unsigned();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
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
        Schema::drop('vehicle_maintenance_history');
    }
}
