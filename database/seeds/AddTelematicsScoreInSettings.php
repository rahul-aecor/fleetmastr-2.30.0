<?php

use Illuminate\Database\Seeder;

class AddTelematicsScoreInSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([
            'safety_score_percentage' => 67,
            'efficiency_score_percentage' => 33,
            'acceleration_score_percentage' => 25,
            'braking_score_percentage' => 25,
            'cornering_score_percentage' => 25,
            'speeding_score_percentage' => 25,
            'rpm_score_percentage' => 67,
            'idle_time_score_percentage' => 33,
            'distance_factor_in_miles' => 100,
        ])->save();
    }
}
