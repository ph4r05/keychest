<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanHandshakeResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_handshake_results', function(Blueprint $table)
		{
			$table->foreign('scan_id', 'fk_scan_handshake_results_scan_handshakes_id')->references('id')->on('scan_handshakes')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_handshake_results', function(Blueprint $table)
		{
			$table->dropForeign('fk_scan_handshake_results_scan_handshakes_id');
		});
	}

}
