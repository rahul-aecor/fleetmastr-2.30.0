<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkshopCompanyIdToDefectHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_history', function (Blueprint $table) {
            $table->integer('workshop_company_id')->unsigned()->after('defect_status_comment')->nullable();
            $table->foreign('workshop_company_id')->references('id')->on('companies')->onDelete('cascade');//->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defect_history', function (Blueprint $table) {
            $table->dropForeign('defect_history_workshop_company_id_foreign');
            $table->dropColumn('workshop_company_id');
        });
    }
}
