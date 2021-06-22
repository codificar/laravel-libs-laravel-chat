<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexInMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('messages');

            if (!$doctrineTable->hasIndex('is_seen'))
                $table->index('is_seen');

            if (!$doctrineTable->hasIndex('user_id'))
                $table->index('user_id');

            if (!$doctrineTable->hasIndex('admin_id'))
                $table->index('admin_id');

            if (!$doctrineTable->hasIndex('conversation_id'))
                $table->index('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('messages');

            if ($doctrineTable->hasIndex('messages_is_seen_index'))
                $table->dropIndex('messages_is_seen_index');

            if ($doctrineTable->hasIndex('messages_user_id_index'))
                $table->dropIndex('messages_user_id_index');

            if ($doctrineTable->hasIndex('messages_admin_id_index'))
                $table->dropIndex('messages_admin_id_index');

            if ($doctrineTable->hasIndex('messages_conversation_id_index'))
                $table->dropIndex('messages_conversation_id_index');
        });
    }
}
