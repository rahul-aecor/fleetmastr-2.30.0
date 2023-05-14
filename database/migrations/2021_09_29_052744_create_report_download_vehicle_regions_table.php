<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportDownloadVehicleRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_download_vehicle_regions', function (Blueprint $table) {
            $table->integer('report_download_id')->unsigned()->index();
            $table->foreign('report_download_id')->references('id')->on('report_downloads');
            $table->integer('vehicle_region_id')->unsigned()->index();
            $table->foreign('vehicle_region_id')->references('id')->on('vehicle_regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_download_regions');
    }
}
