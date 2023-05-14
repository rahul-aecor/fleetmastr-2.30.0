<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDivisionRegionLocationIdsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table('users', function (Blueprint $table) {
            $table->integer('user_division_id')->unsigned()->after('division')->nullable();
            $table->foreign('user_division_id')->references('id')->on('user_divisions')->onDelete('set NULL');
            
            $table->integer('user_region_id')->unsigned()->after('region')->nullable();
            $table->foreign('user_region_id')->references('id')->on('user_regions')->onDelete('set NULL');
            
            $table->integer('user_locations_id')->unsigned()->after('base_location')->nullable();
            $table->foreign('user_locations_id')->references('id')->on('user_locations')->onDelete('set NULL');
            
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // $table->dropForeign('users_user_division_id_foreign');
            $table->dropColumn('user_division_id');

            // $table->dropForeign('users_user_region_id_foreign');
            $table->dropColumn('user_region_id');

            // $table->dropForeign('users_user_locations_id_foreign');
            $table->dropColumn('user_locations_id');
        });
    }
}
