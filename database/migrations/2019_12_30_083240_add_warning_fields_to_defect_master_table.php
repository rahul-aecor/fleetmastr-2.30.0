<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarningFieldsToDefectMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_master', function (Blueprint $table) {
            $table->string('warning_text', 255)->after('is_prohibitional')->nullable();
            $table->boolean('show_warning')->after('is_prohibitional')->default(0);
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
            $table->dropColumn('show_warning');
            $table->dropColumn('warning_text');
        });
    }
}
