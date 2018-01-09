<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWatchLocalServiceTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watch_local_service', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('service_id')->index('ix_watch_local_service_service_id');
			$table->bigInteger('agent_id')->index('ix_watch_local_service_agent_id');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watch_local_service');
	}

}
