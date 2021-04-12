<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameAndPictureInAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumns('admin', ['name', 'picture'])) {

            Schema::table('admin', function (Blueprint $table) {
                $table->string('name')->nullable();
                $table->string('picture')->nullable();
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
        if (Schema::hasColumns('admin', ['name', 'picture'])) {

            Schema::table('admin', function (Blueprint $table) {
                $table->dropColumn('name');
                $table->dropColumn('picture');
            });
        }
    }
}
