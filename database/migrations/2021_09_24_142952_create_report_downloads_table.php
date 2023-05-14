<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_downloads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('report_id')->unsigned()->index();
            $table->foreign('report_id')->references('id')->on('reports');
            $table->datetime('date_from')->nullable();
            $table->datetime('date_to')->nullable();
            $table->string('filename')->nullable();
            $table->boolean('is_auto_download')->default(false);
            $table->datetime('created_at');
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
        Schema::drop('report_downloads');
    }
}
