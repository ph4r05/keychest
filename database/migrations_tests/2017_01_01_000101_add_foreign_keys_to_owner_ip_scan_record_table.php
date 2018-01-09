<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOwnerIpScanRecordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('owner_ip_scan_record', function(Blueprint $table)
		{
			$table->foreign('ip_scan_record_id', 'fk_owner_ip_scan_record_ip_scan_record_id')->references('id')->on('ip_scan_record')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('owner_id', 'fk_owner_ip_scan_record_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('owner_ip_scan_record', function(Blueprint $table)
		{
			$table->dropForeign('fk_owner_ip_scan_record_ip_scan_record_id');
			$table->dropForeign('fk_owner_ip_scan_record_owner_id');
		});
	}

}
