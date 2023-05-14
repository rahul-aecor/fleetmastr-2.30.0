<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefectStatusCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_history', function (Blueprint $table) {
            $table->string('defect_status_comment')->after('comments')->nullable();
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
            $table->dropColumn('defect_status_comment');
        });
    }
}
