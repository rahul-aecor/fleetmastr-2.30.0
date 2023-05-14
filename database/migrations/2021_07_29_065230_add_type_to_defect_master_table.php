<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToDefectMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_master', function (Blueprint $table) {
            $table->enum('type', ['yesno','media','media_based_on_selection','dropdown'])->nullable()->after('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defect_master', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
