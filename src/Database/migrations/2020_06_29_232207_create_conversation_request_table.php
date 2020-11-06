<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('conversation_request')){
            Schema::create('conversation_request', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('conversation_id')->unsigned();
                $table->integer('request_id')->unsigned();
                $table->float('last_bid', 8, 2)->nullable();
                $table->boolean('user_accepted')->default(false);
                $table->boolean('provider_accepted')->default(false);
                $table->integer('proposed_by_id')
                    ->unsigned()
                    ->nullable()
                    ->index('conversation_request_proposed_by_foreign');
                $table->foreign('proposed_by_id')
                    ->references('id')
                    ->on('ledger')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
                $table->timestamps();
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
        Schema::dropIfExists('conversation_request');
    }
}
