<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexInConversationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_request', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('conversation_request');

            if (!$doctrineTable->hasIndex('conversation_id'))
                $table->index('conversation_id');

            if (!$doctrineTable->hasIndex('request_id'))
                $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversation_request', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('conversation_request');

            if ($doctrineTable->hasIndex('conversation_request_conversation_id_index'))
                $table->dropIndex('conversation_request_conversation_id_index');

            if ($doctrineTable->hasIndex('conversation_request_request_id_index'))
                $table->dropIndex('conversation_request_request_id_index');
        });
    }
}
