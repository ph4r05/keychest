<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanDnsEntryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_dns_entry', function(Blueprint $table)
		{
			$table->foreign('scan_id', 'scan_dns_entry_scan_id')->references('id')->on('scan_dns')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_dns_entry', function(Blueprint $table)
		{
			$table->dropForeign('scan_dns_entry_scan_id');
		});
	}

}
