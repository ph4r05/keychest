<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanHandshakesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_handshakes', function(Blueprint $table)
		{
			$table->foreign('test_id', 'tls_watch_managed_tests_id')->references('id')->on('managed_tests')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('sub_ecc', 'scan_handshakes_scan_sub_tls_id_ecc')->references('id')->on('scan_sub_tls')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('sub_rsa', 'scan_handshakes_scan_sub_tls_id_rsa')->references('id')->on('scan_sub_tls')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('watch_id', 'tls_watch_target_id')->references('id')->on('watch_target')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_handshakes', function(Blueprint $table)
		{
			$table->dropForeign('tls_watch_managed_tests_id');
			$table->dropForeign('scan_handshakes_scan_sub_tls_id_ecc');
			$table->dropForeign('scan_handshakes_scan_sub_tls_id_rsa');
			$table->dropForeign('tls_watch_target_id');
		});
	}

}
