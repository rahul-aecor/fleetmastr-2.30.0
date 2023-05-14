<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOptionInSentViaFieldInMessageRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE message_recipients CHANGE sent_via sent_via ENUM('push','sms','message') DEFAULT 'sms' NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE message_recipients CHANGE sent_via sent_via ENUM('push','sms') DEFAULT 'sms' NOT NULL");
    }
}
