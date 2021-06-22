<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexInConversationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('conversations');

            if (!$doctrineTable->hasIndex('user_one'))
                $table->index('user_one');

            if (!$doctrineTable->hasIndex('user_two'))
                $table->index('user_two');

            if (!$doctrineTable->hasIndex('request_id'))
                $table->index('request_id');

            if (!$doctrineTable->hasIndex('help_id'))
                $table->index('help_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function(Blueprint $table)
        {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('conversations');

            if ($doctrineTable->hasIndex('conversations_user_one_index'))
                $table->dropIndex('conversations_user_one_index');

            if ($doctrineTable->hasIndex('conversations_user_two_index'))
                $table->dropIndex('conversations_user_two_index');

            if ($doctrineTable->hasIndex('conversations_request_id_index'))
                $table->dropIndex('conversations_request_id_index');

            if ($doctrineTable->hasIndex('conversations_help_id_index'))
                $table->dropIndex('conversations_help_id_index');
        });
    }
}
