<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByFieldInZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->integer('created_by')->unsigned()->after('bounds')->nullable();
        });
        DB::statement("ALTER TABLE zones CHANGE alert_type alert_type ENUM('one_off','regular') NULL");
        DB::statement("ALTER TABLE zones CHANGE alert_interval alert_interval ENUM('1min','5min','30min') NULL");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
        DB::statement("ALTER TABLE zones CHANGE alert_type alert_type ENUM('one_off','regular') NOT NULL");
        DB::statement("ALTER TABLE zones CHANGE alert_interval alert_interval ENUM('1min','5min','30min') NOT NULL");
    }
}
