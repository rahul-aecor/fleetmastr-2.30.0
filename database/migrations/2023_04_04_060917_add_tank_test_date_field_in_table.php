<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTankTestDateFieldInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('vehicles', 'tank_test_date')) {
            Schema::table('vehicle_types', function($table) {
                $table->string('tank_test_interval', 20)->after('adr_test_date')->nullable();
            });

            Schema::table('vehicles', function (Blueprint $table) {
                $table->date('tank_test_date')->nullable()->after('next_pmi_date');
            });

            \App\Models\MaintenanceEvents::create([
                'name' => 'Tank test',
                'slug' => 'tank_test',
                'is_standard_event' => 1
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_types', function($table) {
            $table->dropColumn('tank_test_interval');
        });

        Schema::table('vehicles', function($table) {
            $table->dropColumn('tank_test_date');
        });
    }
}
