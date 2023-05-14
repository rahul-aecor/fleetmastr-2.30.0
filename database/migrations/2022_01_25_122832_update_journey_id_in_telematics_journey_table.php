<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateJourneyIdInTelematicsJourneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `journey_id` BIGINT UNSIGNED NOT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('ALTER TABLE `telematics_journeys` MODIFY `journey_id` int(11) NOT NULL;');
    }
}
