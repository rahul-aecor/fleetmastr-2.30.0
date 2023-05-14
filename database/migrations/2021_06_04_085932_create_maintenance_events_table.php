<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaintenanceEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_events', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('slug');
            $table->tinyInteger('is_standard_event')->default(0)->comment('0=Custom Events; 1 = Predefined Events; 2 = Planner events');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('maintenance_events');
    }
}
