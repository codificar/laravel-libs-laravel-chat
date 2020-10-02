<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHelpIdOnConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('conversations', 'help_id')) {

            Schema::table('conversations', function (Blueprint $table) {
                $table->integer('help_id')->nullable();
                $table->foreign('help_id')->references('id')->on('request_help')->onDelete('cascade');
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
        if (Schema::hasColumn('conversations', 'help_id')) {

            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('help_id');
            });
        }
    }
}
