<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefectCostFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->integer('cost')->nullable()->after("duplicate_flag");
            $table->date('invoice_date')->nullable()->after("cost");
            $table->string('invoice_number',20)->nullable()->after("invoice_date");
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
            $table->dropColumn('cost');
            $table->dropColumn('invoice_date');
            $table->dropColumn('invoice_number');
        });
    }
}
