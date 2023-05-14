<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class DropColumnVehileUser extends Command 
{
    
    protected $signature = 'drop:columnvehileuser';
    protected $description = 'Drop column from vehicle and user table ';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE `vehicles` DROP COLUMN `vehicle_region`, DROP COLUMN `vehicle_division`');
        DB::statement('ALTER TABLE `users` DROP COLUMN `division`, DROP COLUMN `region`, DROP COLUMN `base_location`, DROP COLUMN `accessible_regions`');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
