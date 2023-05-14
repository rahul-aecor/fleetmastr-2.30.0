<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDriverTagFieldInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users CHANGE dallas_key driver_tag_key VARCHAR(255);");
        Schema::table('users', function (Blueprint $table) {
            $table->enum('driver_tag',['none','dallas_key','rfid_card'])->after('driver_tag_key');
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
            DB::statement("ALTER TABLE users CHANGE driver_tag_key dallas_key VARCHAR(255);");
            $table->dropColumn('driver_tag');
        });
    }
}
