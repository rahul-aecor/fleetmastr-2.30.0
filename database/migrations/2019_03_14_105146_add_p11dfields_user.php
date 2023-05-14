<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddP11dfieldsUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('private_use_show')->after('field_manager_phone')->default(false);
            $table->boolean('fuel_card_issued')->after('field_manager_phone')->default(false);
            $table->boolean('fuel_card_personal_use')->after('field_manager_phone')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('private_use_show');
            $table->dropColumn('fuel_card_issued');
            $table->dropColumn('fuel_card_personal_use');
        });
    }
}
