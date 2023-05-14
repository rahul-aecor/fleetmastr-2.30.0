<?php

use Illuminate\Database\Seeder;

class RemoveExpiryFromMaintenanceEventName extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\MaintenanceEvents::where('slug','maintenance_expiry')->update([
            'name' => 'Maintenance'
        ]);
    }
}
