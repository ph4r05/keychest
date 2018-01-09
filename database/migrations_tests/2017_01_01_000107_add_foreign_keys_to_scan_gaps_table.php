<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanGapsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_gaps', function(Blueprint $table)
		{
			$table->foreign('watch_id', 'sgap_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_gaps', function(Blueprint $table)
		{
			$table->dropForeign('sgap_watch_target_id');
		});
	}

}
