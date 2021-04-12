<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPictureInMessgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('messages', 'picture')) {

            Schema::table('messages', function (Blueprint $table) {
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
        if (Schema::hasColumn('messages', 'picture')) {

            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('picture');
            });
        }
    }
}
