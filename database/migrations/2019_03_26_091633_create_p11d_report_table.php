<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateP11dReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //id
        Schema::create('p11d_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tax_year',9);
            // $table->boolean('freeze_flag')->default(false);
            $table->string('url');
            $table->dateTime('freezed_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void,
     */
    public function down()
    {
        Schema::drop('p11d_report');
    }
}
