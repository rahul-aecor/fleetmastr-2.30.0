<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasNotApplicableOptionToDefectMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defect_master', function (Blueprint $table) {
            $table->boolean('has_not_applicable_option')->after('defect')->default(0);
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
            $table->dropColumn('has_not_applicable_option');
        });
    }
}
