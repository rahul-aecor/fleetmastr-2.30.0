<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByInReportDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_downloads', function (Blueprint $table) {
            $table->integer('created_by')->unsigned()->after('report_id')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_downloads', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
}
