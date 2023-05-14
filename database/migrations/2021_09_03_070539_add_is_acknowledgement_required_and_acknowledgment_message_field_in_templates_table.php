<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAcknowledgementRequiredAndAcknowledgmentMessageFieldInTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->boolean('is_acknowledgement_required')->after('standard_message')->default(false);
            $table->string('acknowledgement_message')->after('is_acknowledgement_required')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('is_acknowledgement_required');
            $table->dropColumn('acknowledgement_message');
        });
    }
}
