<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOwnerWatchTargetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('owner_watch_target', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'fk_owner_watch_target_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('watch_id', 'fk_owner_watch_target_watch_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('owner_watch_target', function(Blueprint $table)
		{
			$table->dropForeign('fk_owner_watch_target_owner_id');
			$table->dropForeign('fk_owner_watch_target_watch_id');
		});
	}

}
