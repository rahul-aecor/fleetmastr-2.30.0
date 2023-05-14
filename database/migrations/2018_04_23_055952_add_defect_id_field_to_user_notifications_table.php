<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefectIdFieldToUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->integer('defect_id')->unsigned()->index()->nullable()->after('user_id');
            $table->foreign('defect_id')->references('id')->on('defects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_notifications', function($table) {
            $table->dropForeign('user_notifications_defect_id_foreign');
            $table->dropIndex('user_notifications_defect_id_index');
            $table->dropColumn('defect_id');
        });
    }
}
