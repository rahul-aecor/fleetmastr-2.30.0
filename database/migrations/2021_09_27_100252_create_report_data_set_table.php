<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportDataSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_dataset', function (Blueprint $table) {
            $table->increments('id');
            $table->string('field_name', 50);
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->string('model_type', 100);
            $table->boolean('is_active')->default(true);
            $table->datetime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_dataset');
    }
}
