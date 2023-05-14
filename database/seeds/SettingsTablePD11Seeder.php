<?php

use Illuminate\Database\Seeder;

class SettingsTablePD11Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([            
            'cash_equivalent' => '0',
            'fuel_benefit_noncommercial' => '0',
            'fuel_benefit_commercial' => '0',
            'hmrc_co2_2018_2019' => '{"year":"2018-2019","co2_values":[],"edited_by":"System","edited_at":"2019-05-08 00:00:00","comments":""}'
        ])->save();
    }
}
