<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanDnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_dns', function(Blueprint $table)
		{
			$table->foreign('watch_id', 'dns_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_dns', function(Blueprint $table)
		{
			$table->dropForeign('dns_watch_target_id');
		});
	}

}
