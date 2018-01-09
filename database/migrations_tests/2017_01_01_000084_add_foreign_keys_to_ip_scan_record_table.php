<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIpScanRecordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ip_scan_record', function(Blueprint $table)
		{
			$table->foreign('last_result_id', 'fk_ip_scan_record_ip_scan_result_id')->references('id')->on('ip_scan_result')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('service_id', 'fk_ip_scan_record_watch_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ip_scan_record', function(Blueprint $table)
		{
			$table->dropForeign('fk_ip_scan_record_ip_scan_result_id');
			$table->dropForeign('fk_ip_scan_record_watch_service_id');
		});
	}

}
