<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdOnMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('messages', 'admin_id')) {

            Schema::table('messages', function (Blueprint $table) {
                $table->integer('admin_id')->nullable()->unsigned()->after('user_id');
                $table->foreign('admin_id')->references('id')->on('admin')->onDelete('cascade');
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
        if (Schema::hasColumn('messages', 'admin_id')) {
            
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign('messages_admin_id_foreign');
                $table->dropColumn('admin_id');
            });
        }
    }
}
