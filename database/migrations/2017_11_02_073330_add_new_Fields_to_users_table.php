<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {           
            $table->text('postcode')->default(NULL)->nullable()->after('mobile');
            $table->text('town_city')->default(NULL)->nullable()->after('mobile');
            $table->text('address2', 255)->default(NULL)->nullable()->after('mobile');
            $table->text('address1', 255)->default(NULL)->nullable()->after('mobile');
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
            $table->dropColumn(['postcode', 'town_city', 'address2', 'address1']);
        });
    }
}
