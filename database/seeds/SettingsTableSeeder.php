<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([
            'primary_colour' => ltrim(get_brand_setting('primary_colour'), '#'),
            'logo' => get_brand_setting('logo.transparent'),
            'defect_email_notification' => 0,
        ])->save();
    }
}
