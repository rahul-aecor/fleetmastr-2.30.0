<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned()->index();
            $table->foreign('message_id')->references('id')->on('messages');
            $table->string('name',100)->default(NULL)->nullable();
            $table->integer('user_id')->unsigned()->index()->nullable()->default(null);
            $table->integer('sent_to_user')->unsigned()->index()->nullable();
            $table->integer('sent_to_group')->unsigned()->index()->nullable();            
            $table->enum('sent_via',['push','sms'])->default('sms');            
            $table->string('mobile',12)->default(NULL)->nullable();
            $table->enum('status',['sent','delivered','read'])->default('sent');
            $table->text('response')->nullable();
            $table->datetime('response_received_at')->default(NULL)->nullable();
            $table->text('error_json')->nullable();
            $table->string('sid',40)->default(NULL)->nullable();
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
        Schema::drop('message_recipients');
    }
}
