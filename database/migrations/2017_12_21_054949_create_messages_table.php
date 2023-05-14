<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sent_by')->unsigned()->index();
            $table->foreign('sent_by')->references('id')->on('users');
            $table->text('content',100);
            $table->json('surveys')->nullable();
            $table->json('questions')->nullable();            
            $table->string('priority')->nullable();
            $table->string('template_name')->nullable();
            $table->datetime('sent_at')->default(NULL)->nullable();
            $table->enum('type', ['standard', 'multiple_choice', 'survey'])->default('standard');
            $table->string('department')->nullable();
            $table->integer('credits_used')->unsigned()->default(0);
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
        Schema::drop('messages');
    }
}
