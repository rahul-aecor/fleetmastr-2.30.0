<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportForInReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports',function ($table){
            $table->enum('report_for',['all', 'user', 'vehicle'])->after('report_category_id')->default('all');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports',function ($table){
            $table->dropColumn('report_for');
        });
    }
}
