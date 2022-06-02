<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCanonicalMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('canonical_messages')) {
			Schema::create('canonical_messages', function (Blueprint $table) {
				$table->increments('id');
				$table->timestamps();
				$table->string('shortcode');
				$table->string('message');
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
        Schema::dropIfExists('canonical_messages');
    }
}
