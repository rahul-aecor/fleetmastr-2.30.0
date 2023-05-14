<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies');//->onDelete('cascade');
            $table->string('division', 50)->nullable();
            $table->string('job_title', 50)->nullable();
            $table->enum('region', ['East','Head Office','North','Scotland','South','West'])->nullable();
            $table->string('base_location', 50)->nullable();
            $table->string('mobile', 12)->nullable();
            $table->string('landline', 12)->nullable();
            $table->string('engineer_id', 12)->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_lanes_account')->default(false);
            $table->string('imei', 15)->nullable();
            $table->string('field_manager_phone', 12)->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
