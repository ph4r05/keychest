<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToWatchLocalServiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watch_local_service', function(Blueprint $table)
		{
			$table->foreign('agent_id', 'watch_local_service_keychest_agent_id')->references('id')->on('keychest_agent')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('service_id', 'watch_local_service_watch_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watch_local_service', function(Blueprint $table)
		{
			$table->dropForeign('watch_local_service_keychest_agent_id');
			$table->dropForeign('watch_local_service_watch_service_id');
		});
	}

}
