<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
Use Illuminate\Database\Schema\Blueprint;

class AddRequestIdToConversationsTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('conversations', 'request_id')) {

            Schema::table('conversations', function (Blueprint $table) {
                $table->integer('request_id')->unsigned();
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
        if (Schema::hasColumn('conversations', 'request_id')) {

            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('request_id');
            });
        }
    }
}
