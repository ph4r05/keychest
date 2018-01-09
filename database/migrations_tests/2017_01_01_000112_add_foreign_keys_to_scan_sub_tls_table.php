<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanSubTlsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_sub_tls', function(Blueprint $table)
		{
			$table->foreign('parent_scan_id', 'scan_sub_tls_scan_handshakes_id')->references('id')->on('scan_handshakes')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('watch_id', 'scan_sub_tls_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_sub_tls', function(Blueprint $table)
		{
			$table->dropForeign('scan_sub_tls_scan_handshakes_id');
			$table->dropForeign('scan_sub_tls_watch_target_id');
		});
	}

}
