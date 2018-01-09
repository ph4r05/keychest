<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_history', function(Blueprint $table)
		{
			$table->foreign('watch_id', 'shist_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_history', function(Blueprint $table)
		{
			$table->dropForeign('shist_watch_target_id');
		});
	}

}
