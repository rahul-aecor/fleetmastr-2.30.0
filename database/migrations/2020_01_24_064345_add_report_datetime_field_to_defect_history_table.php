<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportDatetimeFieldToDefectHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_history', function (Blueprint $table) {
            $table->timestamp('report_datetime')->after('updated_by')->nullable()->useCurrent();
        });
        DB::statement("UPDATE defect_history set report_datetime=created_at");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defect_history', function (Blueprint $table) {
            $table->dropColumn('report_datetime');
        });
    }
}
