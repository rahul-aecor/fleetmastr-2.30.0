<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRegionDivisionEnumToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `vehicles` CHANGE `vehicle_region` `vehicle_region` VARCHAR(200) NOT NULL;');
        DB::statement('ALTER TABLE `vehicles` CHANGE `vehicle_division` `vehicle_division` VARCHAR(200) NOT NULL;');
        DB::statement('ALTER TABLE `users` CHANGE `region` `region` VARCHAR(200) NULL ;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `vehicles` CHANGE `vehicle_region` `vehicle_region` ENUM(\'North\',\'South\',\'Central\',\'Scotland\',\'Head Office\') NOT NULL;');
        DB::statement('ALTER TABLE `vehicles` CHANGE `vehicle_division` `vehicle_division` ENUM(\'Assurance\',\'Finance\',\'HR\',\'IT\',\'Maintenance\',\'Operations\',\'Pipeline\') NOT NULL;');
        DB::statement('ALTER TABLE `users` CHANGE `region` `region` ENUM(\'East\',\'Head Office\',\'North\',\'Scotland\',\'South\',\'West\') NULL;');
    }
}
