<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePermissionNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("UPDATE `roles` SET `name` = 'Vehicle Search' WHERE `id` = '4';");
        DB::statement("UPDATE `roles` SET `name` = 'Vehicle Planning' WHERE `id` = '16';");
        DB::statement("UPDATE `permissions` SET `name` = 'Vehicle Search' WHERE `slug` = 'search.manage'; ");
        DB::statement("UPDATE `permissions` SET `name` = 'Vehicle Planning' WHERE `slug` = 'planner.manage'; ");
         

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE `roles` SET `name` = 'Vehicle Planning & Search' WHERE `id` = '4';");
        DB::statement("UPDATE `roles` SET `name` = 'Planner' WHERE `id` = '16';");
        DB::statement("UPDATE `permissions` SET `name` = 'Vehicle Planning & Search' WHERE `slug` = 'search.manage'; ");
        DB::statement("UPDATE `permissions` SET `name` = 'Planner' WHERE `slug` = 'planner.manage'; ");

    }
}
