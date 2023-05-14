<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstimatedAndActualDefectCostToDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->string('estimated_defect_cost')->after('invoice_number')->nullable();
            $table->string('estimated_defect_cost_value')->after('estimated_defect_cost')->nullable();
            $table->string('actual_defect_cost')->after('estimated_defect_cost_value')->nullable();
            $table->string('actual_defect_cost_value')->after('actual_defect_cost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->dropColumn('estimated_defect_cost');
            $table->dropColumn('estimated_defect_cost_value');
            $table->dropColumn('actual_defect_cost');
            $table->dropColumn('actual_defect_cost_value');
        });
    }
}
