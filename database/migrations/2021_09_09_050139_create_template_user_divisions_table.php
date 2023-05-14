<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateUserDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_user_divisions', function (Blueprint $table) {
            $table->integer('template_id')->unsigned()->index();
            $table->foreign('template_id')->references('id')->on('templates');
            $table->integer('user_division_id')->unsigned()->index();
            $table->foreign('user_division_id')->references('id')->on('user_divisions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template_user_divisions');
    }
}
