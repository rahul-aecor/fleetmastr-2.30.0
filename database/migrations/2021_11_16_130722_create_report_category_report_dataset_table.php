<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportCategoryReportDatasetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_category_report_dataset', function (Blueprint $table) {
            $table->integer('report_category_id')->unsigned()->index();
            $table->foreign('report_category_id')->references('id')->on('report_categories');
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
        Schema::drop('report_categories_report_dataset');
    }
}
