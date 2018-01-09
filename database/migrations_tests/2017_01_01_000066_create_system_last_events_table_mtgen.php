<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSystemLastEventsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_last_events', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('event_key', 191)->unique('uk_system_last_events_event_key');
			$table->dateTime('event_at')->nullable();
			$table->text('aux', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('system_last_events');
	}

}
