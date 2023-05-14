<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompressorServiceIntervalFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->string('compressor_service_interval', 20)->nullable()->after('invertor_service_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('next_compressor_service')->nullable()->after('service_inspection_interval_non_hgv');
            $table->date('last_compressor_service')->nullable()->after('next_compressor_service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('compressor_service_interval');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('next_compressor_service');
            $table->dropColumn('last_compressor_service');
        });
    }
}
