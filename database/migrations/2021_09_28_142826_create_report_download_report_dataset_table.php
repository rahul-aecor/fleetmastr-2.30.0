<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportDownloadReportDatasetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_download_report_dataset', function (Blueprint $table) {
            $table->integer('report_download_id')->unsigned()->index();
            $table->foreign('report_download_id')->references('id')->on('report_downloads');
            $table->integer('report_dataset_id')->unsigned()->index();
            $table->foreign('report_dataset_id')->references('id')->on('report_dataset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_download_report_dataset');
    }
}
