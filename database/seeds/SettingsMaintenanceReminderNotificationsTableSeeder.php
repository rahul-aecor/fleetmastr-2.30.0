<?php

use Illuminate\Database\Seeder;

class SettingsMaintenanceReminderNotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([            
            'maintenance_reminder_notification' => 0
        ])->save();
    }
}