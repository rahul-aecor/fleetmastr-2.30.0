<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateScoreFieldTypesInTelematicsJourneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `efficiency_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `rpm_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `idle_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `safety_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `speeding_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `cornering_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `braking_score` double(16,2) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `acceleration_score` double(16,2) NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `efficiency_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `rpm_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `idle_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `safety_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `speeding_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `cornering_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `braking_score` int(11) NULL;');
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `acceleration_score` int(11) NULL;');
    }
}
