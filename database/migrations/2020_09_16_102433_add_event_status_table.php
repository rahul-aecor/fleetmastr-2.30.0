<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->date('event_plan_date')->nullable()->after('vehicle_id');
            $table->enum('event_status', ['Incomplete','Complete'])->after('comment');

            DB::statement("ALTER TABLE vehicle_maintenance_history MODIFY `event_date` Date NULL;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_maintenance_history', function (Blueprint $table) {
            $table->dropColumn('plann_date');
            $table->dropColumn('event_status');
            DB::statement("ALTER TABLE vehicle_maintenance_history MODIFY `event_date` Date NOT NULL;");
        });
    }
}
