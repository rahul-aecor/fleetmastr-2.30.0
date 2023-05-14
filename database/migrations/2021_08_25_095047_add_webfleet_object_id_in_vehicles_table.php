<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebfleetObjectIdInVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles',function ($table){
            $table->text('webfleet_object_id')->after('is_telematics_enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles',function ($table){
            $table->dropColumn('webfleet_object_id');
        });
    }
}
