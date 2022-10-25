<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCustomerChatConversationRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('conversation_request', 'is_customer_chat')) {    
            Schema::table('conversation_request', function (Blueprint $table) {
                $table->boolean('is_customer_chat')->default(false)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversation_request', function (Blueprint $table) {
            $table->dropColumn('is_customer_chat');
        });
    }
}
