<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIpScanResultTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ip_scan_result', function(Blueprint $table)
		{
			$table->foreign('ip_scan_record_id', 'fk_ip_scan_result_ip_scan_record_id')->references('id')->on('ip_scan_record')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ip_scan_result', function(Blueprint $table)
		{
			$table->dropForeign('fk_ip_scan_result_ip_scan_record_id');
		});
	}

}
