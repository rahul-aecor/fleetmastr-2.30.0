<?php

use Illuminate\Database\Seeder;

class SettingsFleetCostAreaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([            
            'fleet_cost_area_detail' => '{"annual_insurance_cost":null,"telematics_insurance_cost":null,"forecast_cost_per_month":null,"forecast_fixed_cost_per_month":null,"fleet_miles_per_month":null,"fleet_damage_cost_per_month":null,"vor_opportunity_cost_per_day":null,"manual_cost_adjustment":null}}'
        ])->save();
    }
}
