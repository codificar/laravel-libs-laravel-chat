<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestHelpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_help')) {

            Schema::create('request_help', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('request_id')->unsigned();
                $table->foreign('request_id')->references('id')->on('request')->onDelete('cascade');
    
                $table->integer('user_id')->unsigned()->nullable();
                $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
    
                $table->integer('provider_id')->unsigned()->nullable();
                $table->foreign('provider_id')->references('id')->on('provider')->onDelete('cascade');
    
                $table->enum('author', ['user', 'provider'])->nullable()->after('user_id');
    
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
        Schema::dropIfExists('request_help');
    }
}
