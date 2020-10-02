<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdOnLedgerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ledger', 'admin_id')) {

            Schema::table('ledger', function (Blueprint $table) {
                $table->integer('admin_id')->nullable()->unsigned()->after('parent_id');
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
        if (Schema::hasColumn('ledger', 'admin_id')) {
            
            Schema::table('ledger', function (Blueprint $table) {
                $table->dropForeign('ledger_admin_id_foreign');
                $table->dropColumn('admin_id');
            });
        }
    }
}
